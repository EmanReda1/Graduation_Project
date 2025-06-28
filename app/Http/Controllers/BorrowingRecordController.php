<?php

namespace App\Http\Controllers;

use App\Models\RetrieveRequest;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookRequest;
use Illuminate\Http\Request;

class BorrowingRecordController extends Controller
{
    /**
     * Display a listing of the borrowing records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get borrowing requests that are completed (returned)
        $query = RetrieveRequest::with(['request.student', 'request.book'])
            ->borrowing()
            ->whereHas('request', function($q) {
                $q->where('status', 'completed');
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

        $borrowingRecords = $query->paginate(10);

        return view('borrowing_records.index', compact('borrowingRecords'));
    }

    /**
     * Display the specified borrowing record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $borrowingRecord = RetrieveRequest::with(['request.student', 'request.book'])
            ->borrowing()
            ->findOrFail($id);

        return view('borrowing_records.show', compact('borrowingRecord'));
    }
}
