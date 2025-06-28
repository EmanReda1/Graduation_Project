<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use App\Models\Book;
use App\Models\Student;
use Illuminate\Http\Request;

class ReadingRequestController extends Controller
{
    /**
     * Display a listing of reading requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = BookRequest::where('type', 'reading')
                           ->where('status', 'pending');
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%");
            })->orWhereHas('book', function($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%");
            });
        }
        
        // Apply student filter if provided
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        $readingRequests = $query->orderBy('date_of_request', 'desc')->paginate(15);
        
        return view('reading_requests.index', compact('readingRequests'));
    }
    
    /**
     * Display the specified reading request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $request = BookRequest::findOrFail($id);
        
        // Verify this is a reading request
        if ($request->type != 'reading') {
            return redirect()->route('reading-requests.index')
                ->with('error', 'هذا ليس طلب قراءة.');
        }
        
        return view('reading_requests.show', compact('request'));
    }
    
    /**
     * Approve a reading request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        $request = BookRequest::findOrFail($id);
        
        // Verify this is a reading request and it's pending
        if ($request->type != 'reading' || $request->status != 'pending') {
            return redirect()->route('reading-requests.index')
                ->with('error', 'لا يمكن الموافقة على هذا الطلب.');
        }
        
        // Update request status
        $request->status = 'approved';
        $request->save();
        
        // Update book status
        $book = $request->book;
        $book->status = 'in_reading';
        $book->save();
        
        // Create a retrieve request to track the reading
        $retrieveRequest = new \App\Models\RetrieveRequest();
        $retrieveRequest->request_id = $request->request_id;
        $retrieveRequest->request_date = now();
        $retrieveRequest->save();
        
        return redirect()->route('reading-requests.index')
            ->with('success', 'تمت الموافقة على طلب القراءة بنجاح.');
    }
    
    /**
     * Reject a reading request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject($id)
    {
        $request = BookRequest::findOrFail($id);
        
        // Verify this is a reading request and it's pending
        if ($request->type != 'reading' || $request->status != 'pending') {
            return redirect()->route('reading-requests.index')
                ->with('error', 'لا يمكن رفض هذا الطلب.');
        }
        
        // Update request status
        $request->status = 'rejected';
        $request->save();
        
        return redirect()->route('reading-requests.index')
            ->with('success', 'تم رفض طلب القراءة بنجاح.');
    }
    
    /**
     * Search for reading requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        return $this->index($request);
    }
    
    /**
     * Get reading requests by student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getByStudent($id)
    {
        $request = new Request();
        $request->merge(['student_id' => $id]);
        
        return $this->index($request);
    }
}
