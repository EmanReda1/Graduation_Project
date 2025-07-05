<?php

namespace App\Http\Controllers;

use App\Models\RetrieveRequest;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Get all retrieve requests related to borrowing requests
        $query = RetrieveRequest::with(["request.student", "request.book"])->where('status', 'approved');

        // Apply filters
        if ($request->filled("student_id")) {
            $query->whereHas("request.student", function($q) use ($request) {
                $q->where("student_id", $request->student_id);
            });
        }

        if ($request->filled("book_id")) {
            $query->whereHas("request.book", function($q) use ($request) {
                $q->where("book_id", $request->book_id);
            });
        }

        if ($request->filled("status")) {
            $query->where("status", $request->status);
        }

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas("request.student", function($sq) use ($search) {
                    $sq->where("fullname", "like", "%{$search}%");
                })->orWhereHas("request.book", function($sq) use ($search) {
                    $sq->where("book_name", "like", "%{$search}%")
                      ->orWhere("author", "like", "%{$search}%");
                });
            });
        }

        // Sort by date
        $query->orderBy("request_date", "desc");

        $borrowingRecords = $query->paginate(10);

        // Get students for filter dropdown
        $students = Student::orderBy("fullname")->get();

        // Get books for filter dropdown
        $books = Book::orderBy("book_name")->get();

        // Get statuses for filter dropdown
        $statuses = ["pending", "approved", "rejected"];

        return view("borrowing_records.index", compact("borrowingRecords", "students", "books", "statuses"));
    }

    /**
     * Display the specified borrowing record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $borrowingRecord = RetrieveRequest::with(["request.student", "request.book"])
            ->findOrFail($id);

        return view("borrowing_records.show", compact("borrowingRecord"));
    }

    /**
     * Admin approves a return request from a borrowed book.
     *
     * @param  int  $retrieveRequestId
     * @return \Illuminate\Http\Response
     */
    public function approveReturn($retrieveRequestId)
    {
        DB::beginTransaction();
        try {
            $retrieveRequest = RetrieveRequest::findOrFail($retrieveRequestId);

            // Update the retrieve request status
            $retrieveRequest->status = "approved";
            $retrieveRequest->save();

            // Get the original borrowing request
            $borrowRequest = BookRequest::findOrFail($retrieveRequest->book_request_id);

            // Update the original borrowing request status to completed
            $borrowRequest->status = "completed";
            $borrowRequest->save();

            // Update the book status
            $book = $borrowRequest->book;
            $book->status = "available";
            $book->save();

            DB::commit();

            // Send notification to student that return request is approved
            $book_name = $retrieveRequest->request && $retrieveRequest->request->book ? $retrieveRequest->request->book->book_name : 'الكتاب غير معروف';
            Notification::create([
                "student_id" => $borrowRequest->student_id,
                "message" => "تمت الموافقة على طلب إرجاع الكتاب " . $book->$book_name . ".",
                "type" => "return_approved",
                "is_read" => false,
                "date_time" => now(),
            ]);

            return redirect()->route("borrowing-records.index")
                ->with("success", "تم الموافقة على طلب إرجاع الكتاب وتحديث حالة الكتاب بنجاح.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with("error", "حدث خطأ أثناء الموافقة على طلب الإرجاع: " . $e->getMessage());
        }
    }

    /**
     * Admin rejects a return request from a borrowed book.
     *
     * @param  int  $retrieveRequestId
     * @return \Illuminate\Http\Response
     */
    public function rejectReturn($retrieveRequestId)
    {
        DB::beginTransaction();
        try {
            $retrieveRequest = RetrieveRequest::findOrFail($retrieveRequestId);

            // Update the retrieve request status
            $retrieveRequest->status = "rejected";
            $retrieveRequest->save();

            DB::commit();

            // Send notification to student that return request is rejected
            $book_name = $retrieveRequest->request && $retrieveRequest->request->book ? $retrieveRequest->request->book->book_name : 'الكتاب غير معروف';
            Notification::create([
                "student_id" => $retrieveRequest->request->student_id,
                "message" => "تم رفض طلب إرجاع الكتاب " . $retrieveRequest->request->book->$book_name . ". يرجى التواصل مع إدارة المكتبة.",
                "type" => "return_rejected",
                "is_read" => false,
                "date_time" => now(),
            ]);

            return redirect()->route("borrowing-records.index")
                ->with("success", "تم رفض طلب إرجاع الكتاب.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with("error", "حدث خطأ أثناء رفض طلب الإرجاع: " . $e->getMessage());
        }
    }
}


