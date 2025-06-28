<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use App\Models\Book;
use App\Models\Student;
use App\Models\RetrieveRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class BookRequestController extends Controller
{
    /**
     * Display a listing of all book requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = BookRequest::query();

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%");
            })->orWhereHas('book', function($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%");
            });
        }

        // Sort requests
        $sortField = $request->get('sort', 'date_of_request');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $bookRequests = $query->paginate(15);

        return view('book_requests.index', compact('bookRequests'));
    }

    /**
     * Show the form for creating a new book request.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $students = Student::all();
        $books = Book::where('status', 'available')->get();
        $types = ['reading', 'borrowing'];

        return view('book_requests.create', compact('students', 'books', 'types'));
    }

    /**
     * Store a newly created book request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'book_id' => 'required|exists:books,book_id',
            'type' => 'required|in:reading,borrowing',
            'notes' => 'nullable|string',
        ]);

        $bookRequest = new BookRequest();
        $bookRequest->student_id = $request->student_id;
        $bookRequest->book_id = $request->book_id;
        $bookRequest->type = $request->type;
        $bookRequest->date_of_request = now();
        $bookRequest->status = 'pending';
        $bookRequest->notes = $request->notes;
        $bookRequest->save();

        return redirect()->route('book-requests.index')
            ->with('success', 'تم إنشاء طلب الكتاب بنجاح.');
    }

    /**
     * Display the specified book request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $bookRequest = BookRequest::findOrFail($id);

        return view('book_requests.show', compact('bookRequest'));
    }

    /**
     * Show the form for editing the specified book request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $bookRequest = BookRequest::findOrFail($id);
        $students = Student::all();
        $books = Book::all();
        $types = ['reading', 'borrowing', 'return', 'extension'];
        $statuses = ['pending', 'approved', 'rejected', 'completed'];

        return view('book_requests.edit', compact('bookRequest', 'students', 'books', 'types', 'statuses'));
    }

    /**
     * Update the specified book request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'book_id' => 'required|exists:books,book_id',
            'type' => 'required|in:reading,borrowing,return,extension',
            'status' => 'required|in:pending,approved,rejected,completed',
            'notes' => 'nullable|string',
        ]);

        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->student_id = $request->student_id;
        $bookRequest->book_id = $request->book_id;
        $bookRequest->type = $request->type;
        $bookRequest->status = $request->status;
        $bookRequest->notes = $request->notes;
        $bookRequest->save();

        return redirect()->route('book-requests.index')
            ->with('success', 'تم تحديث طلب الكتاب بنجاح.');
    }

    /**
     * Remove the specified book request from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->delete();

        return redirect()->route('book-requests.index')
            ->with('success', 'تم حذف طلب الكتاب بنجاح.');
    }

    /**
     * Display a listing of reading requests.
     *
     * @return \Illuminate\Http\Response
     */
    public function readingRequests()
    {
        $bookRequests = BookRequest::where('type', 'reading')->paginate(15);

        return view('book_requests.reading', compact('bookRequests'));
    }

    /**
     * Display a listing of borrowing requests.
     *
     * @return \Illuminate\Http\Response
     */
    public function borrowingRequests()
    {
        $bookRequests = BookRequest::where('type', 'borrowing')->paginate(15);

        return view('book_requests.borrowing', compact('bookRequests'));
    }

    /**
     * Display a listing of borrowed books.
     *
     * @return \Illuminate\Http\Response
     */
    public function borrowedBooks()
    {
        $books = Book::where('status', 'borrowed')->paginate(15);

        return view('book_requests.borrowed_books', compact('books'));
    }

    /**
     * Display a listing of borrowed projects.
     *
     * @return \Illuminate\Http\Response
     */
    public function borrowedProjects()
    {
        $projects = Project::where('status', 'borrowed')->paginate(15);

        return view('book_requests.borrowed_projects', compact('projects'));
    }

    /**
     * Approve a book request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approveRequest($id)
    {
        $bookRequest = BookRequest::findOrFail($id);

        // Update request status
        $bookRequest->status = 'approved';
        $bookRequest->save();

        // Update book status based on request type
        $book = $bookRequest->book;

        if ($bookRequest->type == 'reading') {
            // For reading requests, mark the book as 'in_reading'
            $book->status = 'in_reading';
            $book->save();

            // Create a retrieve request to track the reading
            $retrieveRequest = new RetrieveRequest();
            $retrieveRequest->request_id = $bookRequest->request_id;
            $retrieveRequest->request_date = now();
            $retrieveRequest->save();
        }
        elseif ($bookRequest->type == 'borrowing') {
            // For borrowing requests, mark the book as 'borrowed'
            $book->status = 'borrowed';
            $book->save();
        }
        elseif ($bookRequest->type == 'return') {
            // For return requests, mark the book as 'available'
            $book->status = 'available';
            $book->save();

            // Find and update the original borrow request
            $borrowRequest = BookRequest::where('book_id', $book->book_id)
                ->where('student_id', $bookRequest->student_id)
                ->where('type', 'borrowing')
                ->where('status', 'approved')
                ->first();

            if ($borrowRequest) {
                $borrowRequest->status = 'completed';
                $borrowRequest->save();
            }
        }
        elseif ($bookRequest->type == 'extension') {
            // For extension requests, we might want to update due dates or other fields
            // This depends on your business logic for extensions
        }

        return redirect()->back()
            ->with('success', 'تم الموافقة على الطلب بنجاح.');
    }

    /**
     * Reject a book request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function rejectRequest($id)
    {
        $bookRequest = BookRequest::findOrFail($id);

        // Update request status
        $bookRequest->status = 'rejected';
        $bookRequest->save();

        return redirect()->back()
            ->with('success', 'تم رفض الطلب بنجاح.');
    }

    /**
     * Process a return request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function returnItem($id)
    {
        $bookRequest = BookRequest::findOrFail($id);

        // Verify this is a borrowing request that was approved
        if ($bookRequest->type != 'borrowing' || $bookRequest->status != 'approved') {
            return redirect()->back()
                ->with('error', 'لا يمكن إرجاع هذا العنصر.');
        }

        // Update request status
        $bookRequest->status = 'completed';
        $bookRequest->save();

        // Update book status
        $book = $bookRequest->book;
        $book->status = 'available';
        $book->save();

        return redirect()->back()
            ->with('success', 'تم إرجاع العنصر بنجاح.');
    }
}
