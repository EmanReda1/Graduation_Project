<?php

namespace App\Http\Controllers;

use App\Models\BookRequest as Request;
use App\Models\RetrieveRequest;
use App\Models\Student;
use App\Models\Book;
use App\Models\Notification;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class BorrowingRequestController extends Controller
{
    /**
     * Display a listing of the borrowing requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(HttpRequest $request)
    {
        $query = Request::with(["student", "book"])
            ->where("type", "borrowing");

        // Apply filters
        if ($request->filled("student_id")) {
            $query->where("student_id", $request->student_id);
        }

        if ($request->filled("book_id")) {
            $query->where("book_id", $request->book_id);
        }

        if ($request->filled("status")) {
            $query->where("status", $request->status);
        }

        if ($request->filled("start_date") && $request->filled("end_date")) {
            $query->whereDate("date_of_request", ">=", $request->start_date)
                  ->whereDate("date_of_request", "<=", $request->end_date);
        }

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas("student", function($sq) use ($search) {
                    $sq->where("username", "like", "%{$search}%");
                })->orWhereHas("book", function($sq) use ($search) {
                    $sq->where("book_name", "like", "%{$search}%")
                      ->orWhere("author", "like", "%{$search}%");
                });
            });
        }

        // Sort requests
        $sortField = $request->get("sort", "date_of_request");
        $sortDirection = $request->get("direction", "desc");
        $query->orderBy($sortField, $sortDirection);

        $borrowingRequests = $query->paginate(15);

        // Get students for filter dropdown
        $students = Student::orderBy("username")->get();

        // Get books for filter dropdown
        $books = Book::orderBy("book_name")->get();

        // Get statuses for filter dropdown
        $statuses = ["pending", "approved", "rejected"];

        return view("borrowing_requests.index", compact("borrowingRequests", "students", "books", "statuses"));
    }

    /**
     * Show the form for creating a new borrowing request.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $students = Student::orderBy("username")->get();
        $books = Book::where("status", "available")->orderBy("book_name")->get();

        return view("borrowing_requests.create", compact("students", "books"));
    }

    /**
     * Store a newly created borrowing request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HttpRequest $request)
    {
        $validated = $request->validate([
            "student_id" => "required|exists:students,student_id",
            "book_id" => "required|exists:books,book_id",
            "notes" => "nullable|string|max:500",
        ]);

        // Check if book is available
        $book = Book::findOrFail($validated["book_id"]);
        if ($book->status !== "available") {
            return redirect()->back()
                ->with("error", "الكتاب غير متاح للاستعارة حالياً.")
                ->withInput();
        }

        // Create a request of type "borrowing"
        $borrowingRequest = Request::create([
            "student_id" => $validated["student_id"],
            "book_id" => $validated["book_id"],
            "date_of_request" => now(),
            "type" => "borrowing",
            "status" => "pending", // Default to pending
            "notes" => $validated["notes"] ?? null,
        ]);

        return redirect()->route("borrowing-requests.show", $borrowingRequest->request_id)
            ->with("success", "تم تسجيل طلب الاستعارة بنجاح.");
    }

    /**
     * Display the specified borrowing request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $borrowingRequest = Request::with(["student", "book"])
            ->where("type", "borrowing")
            ->findOrFail($id);

        return view("borrowing_requests.show", compact("borrowingRequest"));
    }

    /**
     * Show the form for editing the specified borrowing request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $borrowingRequest = Request::with(["student", "book"])
            ->where("type", "borrowing")
            ->findOrFail($id);

        $students = Student::orderBy("username")->get();
        $books = Book::orderBy("book_name")->get();
        $statuses = ["pending", "approved", "rejected"];

        return view("borrowing_requests.edit", compact("borrowingRequest", "students", "books", "statuses"));
    }

    /**
     * Update the specified borrowing request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(HttpRequest $request, $id)
    {
        $borrowingRequest = Request::where("type", "borrowing")->findOrFail($id);

        $validated = $request->validate([
            "student_id" => "required|exists:students,student_id",
            "book_id" => "required|exists:books,book_id",
            "status" => "required|in:pending,approved,rejected",
            "notes" => "nullable|string|max:500",
        ]);

        // Check if book is available if changing book or approving
        if (($borrowingRequest->book_id != $validated["book_id"] ||
            ($borrowingRequest->status != "approved" && $validated["status"] == "approved"))) {

            $book = Book::findOrFail($validated["book_id"]);
            if ($book->status !== "available") {
                return redirect()->back()
                    ->with("error", "الكتاب غير متاح للاستعارة حالياً.")
                    ->withInput();
            }
        }

        // Update the borrowing request
        $borrowingRequest->update([
            "student_id" => $validated["student_id"],
            "book_id" => $validated["book_id"],
            "status" => $validated["status"],
            "notes" => $validated["notes"] ?? null,
        ]);

        // If approved, create a retrieve request if it doesn\\'t exist and update book status
        if ($validated["status"] == "approved") {
            if (!$borrowingRequest->retrieveRequest) {
                $borrowingRequest->retrieveRequest()->create([
                    "request_id" => $borrowingRequest->request_id,
                    "request_date" => now(),
                    "status" => "approved",
                ]);
            }

            // Update book status to borrowed
            $borrowingRequest->book->update(["status" => "borrowed"]);

            // Add notification to student
            Notification::create([
                "student_id" => $borrowingRequest->student_id,
                "message" => "تمت الموافقة على طلب استعارة الكتاب " . $borrowingRequest->book->book_name . ".",
                "type" => "borrowing_approved",
                "is_read" => false,
                "date_time" => now(),
            ]);

        }

        // If rejected and book was marked as borrowed for this request, make it available again
        if ($validated["status"] == "rejected") {
            // Check if this was the request that caused the book to be borrowed
            $activeRequests = Request::where("book_id", $borrowingRequest->book_id)
                ->where("type", "borrowing")
                ->where("status", "approved")
                ->where("request_id", "!=", $borrowingRequest->request_id)
                ->count();

            if ($activeRequests == 0) {
                $borrowingRequest->book->update(["status" => "available"]);
            }

            // Add notification to student
            Notification::create([
                "student_id" => $borrowingRequest->student_id,
                "message" => "تم رفض طلب استعارة الكتاب " . $borrowingRequest->book->book_name . ". يرجى التواصل مع إدارة المكتبة.",
                "type" => "borrowing_rejected",
                "is_read" => false,
                "date_time" => now(),
            ]);
        }

        return redirect()->route("borrowing-requests.show", $borrowingRequest->request_id)
            ->with("success", "تم تحديث طلب الاستعارة بنجاح.");
    }

    /**
     * Approve the specified borrowing request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        $borrowingRequest = Request::where("type", "borrowing")->findOrFail($id);

        // Update the status to approved
        $borrowingRequest->update(["status" => "approved"]);

        // If approved, create a retrieve request if it doesn\\'t exist and update book status
        if (!$borrowingRequest->retrieveRequest) {
            $borrowingRequest->retrieveRequest()->create([
                "request_id" => $borrowingRequest->request_id,
                "request_date" => now(),
                "status" => "approved",
            ]);
        }

        // Update book status to borrowed
        $borrowingRequest->book->update(["status" => "borrowed"]);

        // Add notification to student
        Notification::create([
            "student_id" => $borrowingRequest->student_id,
            "message" => "تمت الموافقة على طلب استعارة الكتاب " . $borrowingRequest->book->book_name . ".",
            "type" => "borrowing_approved",
            "is_read" => false,
            "date_time" => now(),
        ]);

        return redirect()->route("borrowing-requests.show", $borrowingRequest->request_id)
            ->with("success", "تمت الموافقة على طلب الاستعارة بنجاح.");
    }

    /**
     * Reject the specified borrowing request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject($id)
    {
        $borrowingRequest = Request::where("type", "borrowing")->findOrFail($id);

        // Update the status to rejected
        $borrowingRequest->update(["status" => "rejected"]);

        // If rejected and book was marked as borrowed for this request, make it available again
        $activeRequests = Request::where("book_id", $borrowingRequest->book_id)
            ->where("type", "borrowing")
            ->where("status", "approved")
            ->where("request_id", "!=", $borrowingRequest->request_id)
            ->count();

        if ($activeRequests == 0) {
            $borrowingRequest->book->update(["status" => "available"]);
        }

        // Add notification to student
        Notification::create([
            "student_id" => $borrowingRequest->student_id,
            "message" => "تم رفض طلب استعارة الكتاب " . $borrowingRequest->book->book_name . ". يرجى التواصل مع إدارة المكتبة.",
            "type" => "borrowing_rejected",
            "is_read" => false,
            "date_time" => now(),
        ]);

        return redirect()->route("borrowing-requests.show", $borrowingRequest->request_id)
            ->with("success", "تم رفض طلب الاستعارة بنجاح.");
    }

    /**
     * Remove the specified borrowing request from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $borrowingRequest = Request::where("type", "borrowing")->findOrFail($id);

        // If the request was approved and the book is borrowed, check if we need to update book status
        if ($borrowingRequest->status == "approved" && $borrowingRequest->book->status == "borrowed") {
            // Check if this was the request that caused the book to be borrowed
            $activeRequests = Request::where("book_id", $borrowingRequest->book_id)
                ->where("type", "borrowing")
                ->where("status", "approved")
                ->where("request_id", "!=", $borrowingRequest->request_id)
                ->count();

            if ($activeRequests == 0) {
                $borrowingRequest->book->update(["status" => "available"]);
            }
        }

        // Delete the borrowing request (will cascade delete the retrieve request if exists)
        $borrowingRequest->delete();

        return redirect()->route("borrowing-requests.index")
            ->with("success", "تم حذف طلب الاستعارة بنجاح.");
    }

    /**
     * Search for borrowing requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(HttpRequest $request)
    {
        $search = $request->search;

        $query = Request::with(["student", "book"])
            ->where("type", "borrowing");

        $query->where(function($q) use ($search) {
            $q->whereHas("student", function($sq) use ($search) {
                $sq->where("username", "like", "%{$search}%");
            })->orWhereHas("book", function($sq) use ($search) {
                $sq->where("book_name", "like", "%{$search}%")
                      ->orWhere("author", "like", "%{$search}%");
            });
        });

        $borrowingRequests = $query->paginate(15);

        // Get students for filter dropdown
        $students = Student::orderBy("username")->get();

        // Get books for filter dropdown
        $books = Book::orderBy("book_name")->get();

        // Get statuses for filter dropdown
        $statuses = ["pending", "approved", "rejected"];

        return view("borrowing_requests.index", compact("borrowingRequests", "students", "books", "statuses", "search"));
    }

    /**
     * Get borrowing requests by student.
     *
     * @param  int  $studentId
     * @return \Illuminate\Http\Response
     */
    public function getByStudent($studentId)
    {
        $student = Student::findOrFail($studentId);

        $borrowingRequests = Request::with(["student", "book"])
            ->where("type", "borrowing")
            ->where("student_id", $studentId)
            ->orderBy("date_of_request", "desc")
            ->paginate(15);

        // Get students for filter dropdown
        $students = Student::orderBy("username")->get();

        // Get books for filter dropdown
        $books = Book::orderBy("book_name")->get();

        // Get statuses for filter dropdown
        $statuses = ["pending", "approved", "rejected"];

        return view("borrowing_requests.index", compact("borrowingRequests", "students", "books", "statuses", "student"));
    }

    /**
     * Get borrowing requests by book.
     *
     * @param  int  $bookId
     * @return \Illuminate\Http\Response
     */
    public function getByBook($bookId)
    {
        $book = Book::findOrFail($bookId);

        $borrowingRequests = Request::with(["student", "book"])
            ->where("type", "borrowing")
            ->where("book_id", $bookId)
            ->orderBy("date_of_request", "desc")
            ->paginate(15);

        // Get students for filter dropdown
        $students = Student::orderBy("username")->get();

        // Get books for filter dropdown
        $books = Book::orderBy("book_name")->get();

        // Get statuses for filter dropdown
        $statuses = ["pending", "approved", "rejected"];

        return view("borrowing_requests.index", compact("borrowingRequests", "students", "books", "statuses", "book"));
    }

    /**
     * Get borrowing requests by status.
     *
     * @param  string  $status
     * @return \Illuminate\Http\Response
     */

    public function getByStatus($status)
    {
        $borrowingRequests = Request::with(["student", "book"])
            ->where("type", "borrowing")
            ->where("status", $status)
            ->orderBy("date_of_request", "desc")
            ->paginate(15);

        $students = Student::orderBy("username")->get();
        $books = Book::orderBy("book_name")->get();
        $statuses = ["pending", "approved", "rejected"];

        return view("borrowing_requests.index", compact("borrowingRequests", "students", "books", "statuses", "status"));
    }
}


