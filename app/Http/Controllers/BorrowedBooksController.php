<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use App\Models\Book;
use App\Models\Student;
use App\Models\RetrieveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BorrowedBooksController extends Controller
{
    /**
     * Display a listing of the currently borrowed books.
     * This is an admin-only view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Only show books that are currently borrowed (status = 'borrowed')
        $query = Book::where('status', 'borrowed');

        // Apply filters
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Sort books
        $sortField = $request->get('sort', 'book_name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $borrowedBooks = $query->paginate(15);

        foreach ($borrowedBooks as $book) {
            // Get the most recent approved borrowing request for this book
            $latestBorrowRequest = BookRequest::where('book_id', $book->id)
                                            ->where('type', 'borrowing')
                                            ->where('status', 'approved')
                                            ->latest('date_of_request')
                                            ->first();

            // Initialize as null first
            $book->return_request_pending = null;

            // Only check for return request if we have a valid borrow request
            if ($latestBorrowRequest) {
                $book->return_request_pending = RetrieveRequest::where('request_id', $latestBorrowRequest->request_id)
                                                                    ->where('status', 'pending')
                                                                    ->first();
            }

            $book->extension_request_pending = BookRequest::where('book_id', $book->id)
                                                    ->where('type', 'extension')
                                                    ->where('status', 'pending')
                                                    ->first();
        }

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        return view('borrowed_books.index', compact('borrowedBooks', 'departments'));
    }

    /**
     * Display the specified borrowed book details.
     * This is an admin-only view.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
         $book = Book::findOrFail($id);
        // Check if the book is actually borrowed
        if ($book->status !== 'borrowed') {
            return redirect()->route('borrowed-books.index')
                ->with('error', 'هذا الكتاب غير مستعار حالياً.');
        }

        // Get the current borrower (student) through the most recent approved borrowing request
        $borrower = null;
        $borrowRequest = $book->requests()
            ->where('type', 'borrowing')
            ->where('status', 'approved')
            ->orderBy('date_of_request', 'desc')
            ->first();

        if ($borrowRequest) {
            $borrower = $borrowRequest->student;
        }

        $book->return_request_pending = null;
        if ($borrowRequest) {
            $book->return_request_pending = RetrieveRequest::where("request_id", $borrowRequest->request_id)
                                                            ->where("status", "pending")
                                                            ->first();
        }

        $book->extension_request_pending = BookRequest::where('book_id', $book->id)
                                                ->where('type', 'extension')
                                                ->where('status', 'pending')
                                                ->first();

        return view('borrowed_books.show', compact('book', 'borrower', 'borrowRequest'));
    }

    /**
     * Admin approves a return request.
     * This is an admin-only action.
     *
     * @param  int  $bookId
     * @return \Illuminate\Http\Response
     */
    public function approveReturn($bookId)
    {
        DB::beginTransaction();
        try {
            $book = Book::findOrFail($bookId);

            $borrowRequest = $book->requests()
                ->where('type', 'borrowing')
                ->where('status', 'approved')
                ->orderBy('date_of_request', 'desc')
                ->first();

            if (!$borrowRequest) {
                throw new \Exception('لا يوجد طلب استعارة نشط لهذا الكتاب.');
            }

            $retrieveRequest = RetrieveRequest::where('request_id', $borrowRequest->request_id)
                                            ->where('status', 'pending')
                                            ->firstOrFail();

            // Update the retrieve request status
            $retrieveRequest->status = 'approved';
            $retrieveRequest->save();

            // Update the original borrowing request status to completed
            $borrowRequest->status = 'completed';
            $borrowRequest->save();

            // Update the book status
            $book->status = 'available';
            $book->save();

            DB::commit();

            // TODO: Send notification to student that return request is approved

            return redirect()->route('borrowed-books.index')
                ->with('success', 'تم الموافقة على طلب إرجاع الكتاب وتحديث حالة الكتاب بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الموافقة على طلب الإرجاع: ' . $e->getMessage());
        }
    }

    /**
     * Admin rejects a return request.
     * This is an admin-only action.
     *
     * @param  int  $bookId
     * @return \Illuminate\Http\Response
     */
    public function rejectReturn($bookId)
    {
        DB::beginTransaction();
        try {
            $book = Book::findOrFail($bookId);

            $borrowRequest = $book->requests()
                ->where('type', 'borrowing')
                ->where('status', 'approved')
                ->orderBy('date_of_request', 'desc')
                ->first();

            if (!$borrowRequest) {
                throw new \Exception('لا يوجد طلب استعارة نشط لهذا الكتاب.');
            }

            $retrieveRequest = RetrieveRequest::where('request_id', $borrowRequest->request_id)
                                            ->where('status', 'pending')
                                            ->firstOrFail();

            // Update the retrieve request status
            $retrieveRequest->status = 'rejected';
            $retrieveRequest->save();

            DB::commit();

            // TODO: Send notification to student that return request is rejected

            return redirect()->route('borrowed-books.index')
                ->with('success', 'تم رفض طلب إرجاع الكتاب.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفض طلب الإرجاع: ' . $e->getMessage());
        }
    }

    /**
     * Admin approves an extension request.
     * This is an admin-only action.
     *
     * @param  int  $bookId
     * @return \Illuminate\Http\Response
     */
    public function approveExtension($bookId)
    {
        DB::beginTransaction();
        try {
            $book = Book::findOrFail($bookId);

            $extensionRequest = BookRequest::where('book_id', $book->id)
                                            ->where('type', 'extension')
                                            ->where('status', 'pending')
                                            ->firstOrFail();

            // Update the request status
            $extensionRequest->status = 'approved';
            $extensionRequest->save();

            // You might want to update the due date of the original borrow request here
            // This depends on your business logic for extensions

            DB::commit();

            // TODO: Send notification to student that extension request is approved

            return redirect()->route('borrowed-books.index')
                ->with('success', 'تم الموافقة على طلب تمديد فترة الاستعارة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الموافقة على طلب التمديد: ' . $e->getMessage());
        }
    }

    /**
     * Admin rejects an extension request.
     * This is an admin-only action.
     *
     * @param  int  $bookId
     * @return \Illuminate\Http\Response
     */
    public function rejectExtension($bookId)
    {
        DB::beginTransaction();
        try {
            $book = Book::findOrFail($bookId);

            $extensionRequest = BookRequest::where('book_id', $book->id)
                                            ->where('type', 'extension')
                                            ->where('status', 'pending')
                                            ->firstOrFail();

            // Update the request status
            $extensionRequest->status = 'rejected';
            $extensionRequest->save();

            DB::commit();

            // TODO: Send notification to student that extension request is rejected

            return redirect()->route('borrowed-books.index')
                ->with('success', 'تم رفض طلب تمديد فترة الاستعارة.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفض طلب التمديد: ' . $e->getMessage());
        }
    }

    /**
     * Search for borrowed books.
     * This is an admin-only action.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $search = $request->get('search');

        $borrowedBooks = Book::where('status', 'borrowed')
            ->where(function($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            })
            ->paginate(15);

        foreach ($borrowedBooks as $book) {
            $latestBorrowRequest = BookRequest::where('book_id', $book->id)
                                            ->where('type', 'borrowing')
                                            ->where('status', 'approved')
                                            ->latest('date_of_request')
                                            ->first();

            $book->return_request_pending = null;
            if ($latestBorrowRequest) {
                $book->return_request_pending = RetrieveRequest::where('request_id', $latestBorrowRequest->request_id)
                                                                ->where('status', 'pending')
                                                                ->first();
            }

            $book->extension_request_pending = BookRequest::where('book_id', $book->id)
                                                    ->where('type', 'extension')
                                                    ->where('status', 'pending')
                                                    ->first();
        }

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        return view('borrowed_books.index', compact('borrowedBooks', 'departments', 'search'));
    }

    /**
     * Get borrowed books by department.
     * This is an admin-only action.
     *
     * @param  string  $department
     * @return \Illuminate\Http\Response
     */
    public function getByDepartment($department)
    {
        $borrowedBooks = Book::where('status', 'borrowed')
            ->where('department', $department)
            ->paginate(15);

        foreach ($borrowedBooks as $book) {
            $latestBorrowRequest = BookRequest::where('book_id', $book->id)
                                            ->where('type', 'borrowing')
                                            ->where('status', 'approved')
                                            ->latest('date_of_request')
                                            ->first();

            $book->return_request_pending = null;
            if ($latestBorrowRequest) {
                $book->return_request_pending = RetrieveRequest::where('request_id', $latestBorrowRequest->request_id)
                                                                ->where('status', 'pending')
                                                                ->first();
            }

            $book->extension_request_pending = BookRequest::where('book_id', $book->id)
                                                    ->where('type', 'extension')
                                                    ->where('status', 'pending')
                                                    ->first();
        }

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        return view('borrowed_books.index', compact('borrowedBooks', 'departments', 'department'));
    }

    /**
     * Get borrowed books by student.
     * This is an admin-only action.
     *
     * @param  int  $studentId
     * @return \Illuminate\Http\Response
     */
    public function getByStudent($studentId)
    {
        $student = Student::findOrFail($studentId);

        // Get book IDs borrowed by this student
        $borrowedBookIds = $student->requests()
            ->where('type', 'borrowing')
            ->where('status', 'approved')
            ->pluck('book_id');

        $borrowedBooks = Book::whereIn('book_id', $borrowedBookIds)
            ->where('status', 'borrowed')
            ->paginate(15);

        foreach ($borrowedBooks as $book) {
            $latestBorrowRequest = BookRequest::where('book_id', $book->id)
                                            ->where('type', 'borrowing')
                                            ->where('status', 'approved')
                                            ->latest('date_of_request')
                                            ->first();

            $book->return_request_pending = null;
            if ($latestBorrowRequest) {
                $book->return_request_pending = RetrieveRequest::where('request_id', $latestBorrowRequest->request_id)
                                                                ->where('status', 'pending')
                                                                ->first();
            }

            $book->extension_request_pending = BookRequest::where('book_id', $book->id)
                                                    ->where('type', 'extension')
                                                    ->where('status', 'pending')
                                                    ->first();
        }

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        return view('borrowed_books.index', compact('borrowedBooks', 'departments', 'student'));
    }
}
