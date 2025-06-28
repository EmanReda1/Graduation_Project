<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use App\Models\Book;
use App\Models\Student;
use App\Models\RetrieveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        return view('borrowed_books.index', compact('borrowedBooks', 'departments'));
    }

    /**
     * Display the specified borrowed book details.
     * This is an admin-only view.
     *
     * @param  int  $id
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
        
        return view('borrowed_books.show', compact('book', 'borrower', 'borrowRequest'));
    }

    /**
     * Process a return request from a student.
     * This creates a return request that needs admin approval.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function requestReturn(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $student = $request->user()->student;
        
        // Check if the book is actually borrowed by this student
        $borrowRequest = $book->requests()
            ->where('student_id', $student->id)
            ->where('type', 'borrowing')
            ->where('status', 'approved')
            ->first();
            
        if (!$borrowRequest) {
            return redirect()->back()
                ->with('error', 'لا يمكنك طلب إرجاع هذا الكتاب لأنه غير مستعار بواسطتك.');
        }
        
        // Create a return request
        $returnRequest = new BookRequest();
        $returnRequest->student_id = $student->id;
        $returnRequest->book_id = $book->id;
        $returnRequest->date_of_request = now();
        $returnRequest->status = 'pending';
        $returnRequest->type = 'return';
        $returnRequest->notes = $request->notes ?? 'طلب إرجاع كتاب';
        $returnRequest->save();
        
        return redirect()->back()
            ->with('success', 'تم إرسال طلب إرجاع الكتاب بنجاح. سيتم مراجعته من قبل أمين المكتبة.');
    }

    /**
     * Process a request to extend borrowing period.
     * This creates an extension request that needs admin approval.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function requestExtension(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $student = $request->user()->student;
        
        // Check if the book is actually borrowed by this student
        $borrowRequest = $book->requests()
            ->where('student_id', $student->id)
            ->where('type', 'borrowing')
            ->where('status', 'approved')
            ->first();
            
        if (!$borrowRequest) {
            return redirect()->back()
                ->with('error', 'لا يمكنك طلب تمديد استعارة هذا الكتاب لأنه غير مستعار بواسطتك.');
        }
        
        // Create an extension request
        $extensionRequest = new BookRequest();
        $extensionRequest->student_id = $student->id;
        $extensionRequest->book_id = $book->id;
        $extensionRequest->date_of_request = now();
        $extensionRequest->status = 'pending';
        $extensionRequest->type = 'extension';
        $extensionRequest->notes = $request->notes ?? 'طلب تمديد فترة استعارة';
        $extensionRequest->save();
        
        return redirect()->back()
            ->with('success', 'تم إرسال طلب تمديد فترة الاستعارة بنجاح. سيتم مراجعته من قبل أمين المكتبة.');
    }

    /**
     * Admin approves a return request.
     * This is an admin-only action.
     *
     * @param  int  $requestId
     * @return \Illuminate\Http\Response
     */
    public function approveReturn($requestId)
    {
        $returnRequest = BookRequest::findOrFail($requestId);
        
        // Verify this is a return request
        if ($returnRequest->type !== 'return') {
            return redirect()->back()
                ->with('error', 'هذا ليس طلب إرجاع.');
        }
        
        // Update the request status
        $returnRequest->status = 'approved';
        $returnRequest->save();
        
        // Update the book status
        $book = $returnRequest->book;
        $book->status = 'available';
        $book->save();
        
        // Find and update the original borrow request
        $borrowRequest = BookRequest::where('book_id', $book->id)
            ->where('student_id', $returnRequest->student_id)
            ->where('type', 'borrowing')
            ->where('status', 'approved')
            ->first();
            
        if ($borrowRequest) {
            $borrowRequest->status = 'completed';
            $borrowRequest->save();
            
            // Update retrieve request if exists
            if ($borrowRequest->retrieveRequest) {
                $borrowRequest->retrieveRequest->status = 'completed';
                $borrowRequest->retrieveRequest->save();
            }
        }
        
        return redirect()->route('book-requests.index')
            ->with('success', 'تم الموافقة على طلب إرجاع الكتاب وتحديث حالة الكتاب بنجاح.');
    }

    /**
     * Admin rejects a return request.
     * This is an admin-only action.
     *
     * @param  int  $requestId
     * @return \Illuminate\Http\Response
     */
    public function rejectReturn($requestId)
    {
        $returnRequest = BookRequest::findOrFail($requestId);
        
        // Verify this is a return request
        if ($returnRequest->type !== 'return') {
            return redirect()->back()
                ->with('error', 'هذا ليس طلب إرجاع.');
        }
        
        // Update the request status
        $returnRequest->status = 'rejected';
        $returnRequest->save();
        
        return redirect()->route('book-requests.index')
            ->with('success', 'تم رفض طلب إرجاع الكتاب.');
    }

    /**
     * Admin approves an extension request.
     * This is an admin-only action.
     *
     * @param  int  $requestId
     * @return \Illuminate\Http\Response
     */
    public function approveExtension($requestId)
    {
        $extensionRequest = BookRequest::findOrFail($requestId);
        
        // Verify this is an extension request
        if ($extensionRequest->type !== 'extension') {
            return redirect()->back()
                ->with('error', 'هذا ليس طلب تمديد استعارة.');
        }
        
        // Update the request status
        $extensionRequest->status = 'approved';
        $extensionRequest->save();
        
        // You might want to update the due date of the original borrow request here
        // This depends on your business logic for extensions
        
        return redirect()->route('book-requests.index')
            ->with('success', 'تم الموافقة على طلب تمديد فترة الاستعارة بنجاح.');
    }

    /**
     * Admin rejects an extension request.
     * This is an admin-only action.
     *
     * @param  int  $requestId
     * @return \Illuminate\Http\Response
     */
    public function rejectExtension($requestId)
    {
        $extensionRequest = BookRequest::findOrFail($requestId);
        
        // Verify this is an extension request
        if ($extensionRequest->type !== 'extension') {
            return redirect()->back()
                ->with('error', 'هذا ليس طلب تمديد استعارة.');
        }
        
        // Update the request status
        $extensionRequest->status = 'rejected';
        $extensionRequest->save();
        
        return redirect()->route('book-requests.index')
            ->with('success', 'تم رفض طلب تمديد فترة الاستعارة.');
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
            
        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        return view('borrowed_books.index', compact('borrowedBooks', 'departments', 'student'));
    }
}
