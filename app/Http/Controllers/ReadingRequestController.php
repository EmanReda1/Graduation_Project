<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use App\Models\Book;
use App\Models\Student;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $query = BookRequest::where("type", "reading")
                           ->where("status", "pending");

        // Apply search filter if provided
        if ($request->filled("search")) {
            $search = $request->search;
            $query->whereHas("student", function($q) use ($search) {
                $q->where("username", "like", "%{$search}%"); // Use username for student
            })->orWhereHas("book", function($q) use ($search) {
                $q->where("book_name", "like", "%{$search}%");
            });
        }

        // Apply student filter if provided
        if ($request->filled("student_id")) {
            $query->where("student_id", $request->student_id);
        }

        $readingRequests = $query->orderBy("date_of_request", "desc")->paginate(15);

        return view("reading_requests.index", compact("readingRequests"));
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
        if ($request->type != "reading") {
            return redirect()->route("reading-requests.index")
                ->with("error", "هذا ليس طلب قراءة.");
        }

        return view("reading_requests.show", compact("request"));
    }

    /**
     * Approve a reading request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            $request = BookRequest::findOrFail($id);

            // Verify this is a reading request and it\"s pending
            if ($request->type != "reading" || $request->status != "pending") {
                DB::rollBack();
                return redirect()->route("reading-requests.index")
                    ->with("error", "لا يمكن الموافقة على هذا الطلب.");
            }

            // Re-fetch the book with a lock to ensure exclusive access during this transaction
            $book = Book::where('book_id', $request->book_id)->lockForUpdate()->first();

            // Check if the book is available
            if ($book->status !== "available") {
                DB::rollBack();
                return redirect()->back()->with("error", "الكتاب غير متاح حالياً للموافقة على طلب القراءة.");
            }

            // Update request status
            $request->status = "approved";
            $request->save();

            // Update book status
            $book->status = "in_reading";
            $book->save();

            // Commit the transaction
            DB::commit();

            // Add notification to student
            Notification::create([
                "student_id" => $request->student_id,
                "message" => "تمت الموافقة على طلب قراءة الكتاب " . ($request->book->book_name ?? 'غير معروف') . ".",
                "type" => "reading_approved",
                "is_read" => false,
                "date_time" => now(),
            ]);

            // Add notification to librarian
            $librarian = Auth::user(); // Assuming Auth::user() gets the authenticated librarian
            if ($librarian) {
                Notification::create([
                    "librarian_id" => $librarian->librarian_id, // Use librarian_id from the librarian object
                    "student_id" => $request->student_id,
                    "message" => "تمت الموافقة على طلب قراءة الكتاب " . ($request->book->book_name ?? 'غير معروف') . " للطالب " . ($request->student->fullname ?? $request->student->username ?? 'غير معروف') . ".",
                    "type" => "reading_approved_librarian",
                    "is_read" => false,
                    "date_time" => now(),
                ]);
            }

            return redirect()->route("reading-requests.index")
                ->with("success", "تمت الموافقة على طلب القراءة بنجاح.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with("error", "حدث خطأ أثناء الموافقة على طلب القراءة: " . $e->getMessage());
        }
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

        // Verify this is a reading request and it\"s pending
        if ($request->type != "reading" || $request->status != "pending") {
            return redirect()->route("reading-requests.index")
                ->with("error", "لا يمكن رفض هذا الطلب.");
        }

        // Update request status
        $request->status = "rejected";
        $request->save();

        // Add notification to student
        Notification::create([
            "student_id" => $request->student_id,
            "message" => "تم رفض طلب قراءة الكتاب " . ($request->book->book_name ?? 'غير معروف') . ". يرجى التواصل مع إدارة المكتبة.",
            "type" => "reading_rejected",
            "is_read" => false,
            "date_time" => now(),
        ]);

        // Add notification to librarian
        $librarian = Auth::user(); // Assuming Auth::user() gets the authenticated librarian
        if ($librarian) {
            Notification::create([
                "librarian_id" => $librarian->librarian_id, // Use librarian_id from the librarian object
                "student_id" => $request->student_id,
                "message" => "تم رفض طلب قراءة الكتاب " . ($request->book->book_name ?? 'غير معروف') . " للطالب " . ($request->student->fullname ?? $request->student->username ?? 'غير معروف') . ".",
                "type" => "reading_rejected_librarian",
                "is_read" => false,
                "date_time" => now(),
            ]);
        }

        return redirect()->route("reading-requests.index")
            ->with("success", "تم رفض طلب القراءة بنجاح.");
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
        $request->merge(["student_id" => $id]);

        return $this->index($request);
    }
}


