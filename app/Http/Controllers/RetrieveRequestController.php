<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use App\Models\RetrieveRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetrieveRequestController extends Controller
{
    /**
     * Display a listing of retrieve requests.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $retrieveRequests = RetrieveRequest::with(["bookRequest.student", "bookRequest.book"])
                                         ->whereHas("bookRequest", function($query) {
                                             $query->where("status", "pending");
                                         })
                                         ->orderBy("request_date", "desc")
                                         ->paginate(15);

        return view("retrieve_requests.index", compact("retrieveRequests"));
    }

    /**
     * Approve a retrieve request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        $retrieveRequest = RetrieveRequest::findOrFail($id);
        $bookRequest = $retrieveRequest->bookRequest;

        if (!$bookRequest || $bookRequest->status !== "pending") {
            return redirect()->back()->with("error", "لا يمكن الموافقة على طلب الإرجاع هذا.");
        }

        // Update retrieve request status
        $retrieveRequest->status = "approved";
        $retrieveRequest->save();

        // Update book request status to completed
        $bookRequest->status = "completed";
        $bookRequest->save();

        // Update book status to available
        $book = $bookRequest->book;
        if ($book) {
            $book->status = "available";
            $book->save();
        }

        // Add notification to student
        Notification::create([
            "student_id" => $bookRequest->student_id,
            "message" => "تمت الموافقة على طلب إرجاع الكتاب " . ($book->book_name ?? 'غير معروف') . ".",
            "type" => "retrieve_approved",
            "is_read" => false,
            "date_time" => now(),
        ]);

        // Add notification to librarian
        $librarian = Auth::user();
        if ($librarian) {
            Notification::create([
                "librarian_id" => $librarian->librarian_id,
                "student_id" => $bookRequest->student_id,
                "message" => "تمت الموافقة على طلب إرجاع الكتاب " . ($book->book_name ?? 'غير معروف') . " للطالب " . ($bookRequest->student->fullname ?? $bookRequest->student->username ?? 'غير معروف') . ".",
                "type" => "retrieve_approved_librarian",
                "is_read" => false,
                "date_time" => now(),
            ]);
        }

        return redirect()->back()->with("success", "تمت الموافقة على طلب الإرجاع بنجاح.");
    }

    /**
     * Reject a retrieve request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject($id)
    {
        $retrieveRequest = RetrieveRequest::findOrFail($id);
        $bookRequest = $retrieveRequest->bookRequest;

        if (!$bookRequest || $bookRequest->status !== "pending") {
            return redirect()->back()->with("error", "لا يمكن رفض طلب الإرجاع هذا.");
        }

        // Update retrieve request status
        $retrieveRequest->status = "rejected";
        $retrieveRequest->save();

        // Add notification to student
        Notification::create([
            "student_id" => $bookRequest->student_id,
            "message" => "تم رفض طلب إرجاع الكتاب " . ($bookRequest->book->book_name ?? 'غير معروف') . ". يرجى التواصل مع إدارة المكتبة.",
            "type" => "retrieve_rejected",
            "is_read" => false,
            "date_time" => now(),
        ]);

        // Add notification to librarian
        $librarian = Auth::user();
        if ($librarian) {
            Notification::create([
                "librarian_id" => $librarian->librarian_id,
                "student_id" => $bookRequest->student_id,
                "message" => "تم رفض طلب إرجاع الكتاب " . ($bookRequest->book->book_name ?? 'غير معروف') . " للطالب " . ($bookRequest->student->fullname ?? $bookRequest->student->username ?? 'غير معروف') . ".",
                "type" => "retrieve_rejected_librarian",
                "is_read" => false,
                "date_time" => now(),
            ]);
        }

        return redirect()->back()->with("success", "تم رفض طلب الإرجاع بنجاح.");
    }
}


