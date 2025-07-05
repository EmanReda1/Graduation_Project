<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use App\Models\RetrieveRequest;
use Illuminate\Http\Request;

class ReadingRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = BookRequest::with(["student", "book"])
            ->where("type", "reading")
            ->where("status", "approved");

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas("student", function($sq) use ($search) {
                    $sq->where("fullname", "like", "%{$search}%");
                })->orWhereHas("book", function($bq) use ($search) {
                    $bq->where("book_name", "like", "%{$search}%");
                });
            });
        }

        $readingRecords = $query->orderBy("request_date", "desc")->paginate(15);

        // Eager load retrieve requests and add a flag to each reading record
        $readingRecords->each(function ($record) {
            // The request_id in retrieve_requests refers to the request_id in book_requests (requests table)
            $record->retrieve_request = RetrieveRequest::where("request_id", $record->request_id)->first();
        });

        return view("reading_records.index", compact("readingRecords"));
    }

    public function show($id)
    {
        $readingRecord = BookRequest::with(["student", "book"])
            ->where("type", "reading")
            ->where("status", "approved")
            ->findOrFail($id);

        // Eager load retrieve request and add it to the reading record
        // The request_id in retrieve_requests refers to the request_id in book_requests (requests table)
        $readingRecord->retrieve_request = RetrieveRequest::where("request_id", $readingRecord->request_id)->first();

        return view("reading_records.show", compact("readingRecord"));
    }

    public function returnBook($id)
    {
        $bookRequest = BookRequest::findOrFail($id);

        // Check if there\\\'s a pending retrieve request for this book request
        // The request_id in retrieve_requests refers to the request_id in book_requests (requests table)
        $retrieveRequest = RetrieveRequest::where("request_id", $bookRequest->request_id)
                                        ->where("status", "pending")
                                        ->first();

        if (!$retrieveRequest) {
            return redirect()->back()->with("error", "لا يوجد طلب إرجاع معلق لهذا الكتاب.");
        }

        // Update request status
        $bookRequest->status = 'returned';
        $bookRequest->save();

        // Update book status
        $book = $bookRequest->book;
        $book->status = 'available';
        $book->save();

        // Update retrieve request status
        $retrieveRequest->status = 'completed';
        $retrieveRequest->save();

        return redirect()->route("reading-records.index")
            ->with("success", "تمت إعادة الكتاب بنجاح.");
    }

    public function rejectReturn($id)
    {
        $bookRequest = BookRequest::findOrFail($id);

        // Check if there\\\'s a pending retrieve request for this book request
        // The request_id in retrieve_requests refers to the request_id in book_requests (requests table)
        $retrieveRequest = RetrieveRequest::where("request_id", $bookRequest->request_id)
                                        ->where("status", "pending")
                                        ->first();

        if (!$retrieveRequest) {
            return redirect()->back()->with("error", "لا يوجد طلب إرجاع معلق لهذا الكتاب لرفضه.");
        }

        // Update retrieve request status to rejected
        $retrieveRequest->status = 'rejected';
        $retrieveRequest->save();

        return redirect()->route("reading-records.index")
            ->with("success", "تم رفض طلب الإرجاع بنجاح.");
    }
}


