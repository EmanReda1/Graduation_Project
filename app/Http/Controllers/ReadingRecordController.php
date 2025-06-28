<?php

namespace App\Http\Controllers;

use App\Models\RetrieveRequest;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookRequest;
use Illuminate\Http\Request;

class ReadingRecordController extends Controller
{
    /**
     * Display a listing of the reading records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get reading requests that are currently active
        $query = RetrieveRequest::with(['request.student', 'request.book'])
            ->reading()
            ->whereHas('request', function($q) {
                $q->where('status', 'approved');
            });

        // Apply filters
        if ($request->filled('student_id')) {
            $query->byStudent($request->student_id);
        }

        if ($request->filled('book_id')) {
            $query->byBook($request->book_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('request.student', function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%");
            })->orWhereHas('request.book', function($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%");
            });
        }

        // Sort by date
        $query->orderBy('request_date', 'desc');

        $readingRecords = $query->paginate(10);

        return view('reading_records.index', compact('readingRecords'));
    }

    /**
     * Display the specified reading record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $readingRecord = RetrieveRequest::with(['request.student', 'request.book'])
            ->reading()
            ->findOrFail($id);

        return view('reading_records.show', compact('readingRecord'));
    }

    /**
     * Process a request to borrow a book that is currently being read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function requestBorrow($id)
    {
        $readingRecord = RetrieveRequest::with(['request.student', 'request.book'])
            ->reading()
            ->findOrFail($id);

        // Create a new borrowing request
        $borrowRequest = new BookRequest();
        $borrowRequest->student_id = $readingRecord->request->student_id;
        $borrowRequest->book_id = $readingRecord->request->book_id;
        $borrowRequest->date_of_request = now();
        $borrowRequest->status = 'pending';
        $borrowRequest->type = 'borrowing';
        $borrowRequest->save();

        return redirect()->route('reading-records.index')
            ->with('success', 'تم إرسال طلب الاستعارة بنجاح.');
    }

    /**
     * Process a request to return a book that is currently being read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function returnBook($id)
    {
        $readingRecord = RetrieveRequest::with(['request.student', 'request.book'])
            ->reading()
            ->findOrFail($id);

        // Update the request status
        $readingRecord->request->status = 'completed';
        $readingRecord->request->save();

        // Update the book status
        if ($readingRecord->request->book) {
            $book = $readingRecord->request->book;
            $book->status = 'available';
            $book->save();
        }

        return redirect()->route('reading-records.index')
            ->with('success', 'تم إعادة الكتاب بنجاح.');
    }
}
