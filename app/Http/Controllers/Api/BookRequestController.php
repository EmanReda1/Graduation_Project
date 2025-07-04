<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Book;
use App\Models\Notification;
use App\Models\RetrieveRequest; // Added this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class BookRequestController extends Controller
{
    /**
     * Get all book requests for the authenticated student.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بجلب الطلبات. يرجى تسجيل الدخول."
                ], 401);
            }

            $query = BookRequest::where("student_id", $student->student_id)
                ->with(["book"]);

            if ($request->filled("type")) {
                $query->where("type", $request->type);
            }

            if ($request->filled("status")) {
                $query->where("status", $request->status);
            }

            if ($request->filled("book_id")) {
                $query->where("book_id", $request->book_id);
            }

            if ($request->filled("start_date") && $request->filled("end_date")) {
                $query->whereBetween("date_of_request", [$request->start_date, $request->end_date]);
            }

            if ($request->filled("search")) {
                $search = $request->search;
                $query->whereHas("book", function ($sq) use ($search) {
                    $sq->where("book_name", "like", "%{$search}%")
                        ->orWhere("author", "like", "%{$search}%");
                });
            }

            $requests = $query->orderBy("date_of_request", "desc")->paginate(15);

            return response()->json([
                "status" => "success",
                "data" => $requests
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching student book requests: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ أثناء جلب الطلبات"
            ], 500);
        }
    }

    /**
     * Create a new book request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Log::info("Book request POST received.");
        Log::info("Request data: " . json_encode($request->all()));

        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بإنشاء طلبات. يرجى تسجيل الدخول."
                ], 401);
            }

            $request->validate([
                "book_id" => "required|exists:books,book_id",
                "type" => "required|in:reading,borrowing",
                "notes" => "nullable|string",
            ]);

            $existingPendingRequest = BookRequest::where("student_id", $student->student_id)
                ->where("book_id", $request->book_id)
                ->where("type", $request->type)
                ->where("status", "pending")
                ->first();

            if ($existingPendingRequest) {
                return response()->json([
                    "status" => "error",
                    "message" => "لديك بالفعل طلب معلق لهذا الكتاب من نفس النوع."
                ], 400);
            }

            $bookRequest = new BookRequest();
            $bookRequest->student_id = $student->student_id;
            $bookRequest->book_id = $request->book_id;
            $bookRequest->type = $request->type;
            $bookRequest->date_of_request = now();
            $bookRequest->status = "pending";
            $bookRequest->notes = $request->notes;
            $bookRequest->save();

            // Reload the book relationship to ensure book_name is available
            $bookRequest->load("book");

            Log::info("Book request saved successfully.");

            // Notify librarian about new request
            $librarian = Auth::guard("web")->user(); // Assuming Auth::guard("web")->user() gets the librarian in web context

            if ($librarian) {
                Notification::create([
                    "librarian_id" => $librarian->id,
                    "message" => "تم إرسال طلب " . ($bookRequest->type === "reading" ? "قراءة" : "استعارة") . " جديد من الطالب " . ($student->username ?? $student->name) . " للكتاب " . ($bookRequest->book->book_name ?? "غير معروف") . ".",
                    "type" => $bookRequest->type . "_request_pending",
                    "is_read" => false,
                    "date_time" => now(),
                ]);
            } else {
                Log::warning("Librarian not authenticated in web context for notification.");
            }

            return response()->json([
                "status" => "success",
                "message" => "تم إنشاء طلب الكتاب بنجاح.",
                "data" => $bookRequest
            ], 201);
        } catch (ValidationException $e) {
            Log::error("Validation Error for Book Request: " . $e->getMessage());
            Log::error("Validation Errors: " . json_encode($e->errors()));
            return response()->json([
                "status" => "error",
                "message" => "خطأ في التحقق من البيانات",
                "errors" => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error saving book request: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ غير متوقع في إرسال الطلب"
            ], 500);
        }
    }

    /**
     * Request to return a borrowed book
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestReturn(Request $request, $id)
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بإنشاء طلبات. يرجى تسجيل الدخول."
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                "notes" => "nullable|string|max:500"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => "بيانات غير صحيحة",
                    "errors" => $validator->errors()
                ], 422);
            }

            $originalRequest = BookRequest::where("request_id", $id)
                ->where("student_id", $student->student_id)
                ->whereIn("type", ["borrowing", "reading"])
                ->where("status", "approved")
                ->first();

            if (!$originalRequest) {
                return response()->json([
                    "status" => "error",
                    "message" => "الطلب غير موجود أو غير مؤهل للإرجاع"
                ], 404);
            }

            // Check for existing pending retrieve request in the new retrieve_requests table
            $existingReturnRequest = RetrieveRequest::where("request_id", $originalRequest->request_id)
                ->where("status", "pending")
                ->first();

            if ($existingReturnRequest) {
                return response()->json([
                    "status" => "error",
                    "message" => "لديك طلب إرجاع معلق بالفعل لهذا الكتاب"
                ], 400);
            }

            // Create retrieve request in the retrieve_requests table
            $returnRequest = RetrieveRequest::create([
                "request_id" => $originalRequest->request_id,
                "request_date" => now(),
                "status" => "pending",
                "notes" => $request->notes ?? "طلب إرجاع كتاب"
            ]);

            $originalRequest->load("book");

            $librarian = Auth::guard("web")->user(); // Assuming Auth::guard("web")->user() gets the librarian in web context
            if ($librarian) {
                Notification::create([
                    "librarian_id" => $librarian->id,
                    "message" => "تم إرسال طلب إرجاع جديد من الطالب " . ($student->username ?? $student->name) . " للكتاب " . ($originalRequest->book->book_name ?? "غير معروف") . ".",
                    "type" => "return_request_pending",
                    "is_read" => false,
                    "date_time" => now(),
                ]);
            } else {
                Log::warning("Librarian not authenticated in web context for return request notification.");
            }

            return response()->json([
                "status" => "success",
                "message" => "تم إرسال طلب الإرجاع بنجاح. سيتم مراجعته من قبل أمين المكتبة.",
                "data" => [
                    "retrieve_id" => $returnRequest->retrieve_id,
                    "request_id" => $returnRequest->request_id,
                    "status" => $returnRequest->status,
                    "request_date" => $returnRequest->request_date
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error in requestReturn: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ في إرسال طلب الإرجاع"
            ], 500);
        }
    }

    /**
     * Request to extend borrowing period
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestExtension(Request $request, $id)
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بإنشاء طلبات. يرجى تسجيل الدخول."
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                "notes" => "nullable|string|max:500"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => "بيانات غير صحيحة",
                    "errors" => $validator->errors()
                ], 422);
            }

            $originalRequest = BookRequest::where("request_id", $id)
                ->where("student_id", $student->student_id)
                ->where("type", "borrowing")
                ->where("status", "approved")
                ->first();

            if (!$originalRequest) {
                return response()->json([
                    "status" => "error",
                    "message" => "الطلب غير موجود أو غير مؤهل للتمديد"
                ], 404);
            }

            $existingExtensionRequest = BookRequest::where("student_id", $student->student_id)
                ->where("book_id", $originalRequest->book_id)
                ->where("type", "extension")
                ->where("status", "pending")
                ->first();

            if ($existingExtensionRequest) {
                return response()->json([
                    "status" => "error",
                    "message" => "لديك طلب تمديد معلق بالفعل لهذا الكتاب"
                ], 400);
            }

            $extensionRequest = BookRequest::create([
                "student_id" => $student->student_id,
                "book_id" => $originalRequest->book_id,
                "type" => "extension",
                "date_of_request" => now(),
                "status" => "pending",
                "notes" => $request->notes ?? "طلب تمديد فترة استعارة"
            ]);

            $originalRequest->load("book");

            $librarian = Auth::guard("web")->user(); // Assuming Auth::guard("web")->user() gets the librarian in web context
            if ($librarian) {
                Notification::create([
                    "librarian_id" => $librarian->id,
                    "message" => "تم إرسال طلب تمديد جديد من الطالب " . ($student->username ?? $student->name) . " للكتاب " . ($originalRequest->book->book_name ?? "غير معروف") . ".",
                    "type" => "extension_request_pending",
                    "is_read" => false,
                    "date_time" => now(),
                ]);
            } else {
                Log::warning("Librarian not authenticated in web context for extension request notification.");
            }

            return response()->json([
                "status" => "success",
                "message" => "تم إرسال طلب التمديد بنجاح. سيتم مراجعته من قبل أمين المكتبة.",
                "data" => [
                    "request_id" => $extensionRequest->request_id,
                    "type" => $extensionRequest->type,
                    "status" => $extensionRequest->status,
                    "date_of_request" => $extensionRequest->date_of_request
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error in requestExtension: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ في إرسال طلب التمديد"
            ], 500);
        }
    }

    /**
     * Cancel a pending request
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بإنشاء طلبات. يرجى تسجيل الدخول."
                ], 401);
            }

            $bookRequest = BookRequest::where("request_id", $id)
                ->where("student_id", $student->student_id)
                ->where("status", "pending")
                ->first();

            if (!$bookRequest) {
                return response()->json([
                    "status" => "error",
                    "message" => "الطلب غير موجود أو لا يمكن إلغاؤه"
                ], 404);
            }

            $bookRequest->delete();

            $bookRequest->load("book");

            $librarian = Auth::guard("web")->user(); // Assuming Auth::guard("web")->user() gets the librarian in web context
            if ($librarian) {
                Notification::create([
                    "librarian_id" => $librarian->id,
                    "message" => "تم إلغاء طلب " . $bookRequest->type . " من الطالب " . ($student->username ?? $student->name) . " للكتاب " . ($bookRequest->book->book_name ?? "غير معروف") . ".",
                    "type" => "request_cancelled",
                    "is_read" => false,
                    "date_time" => now(),
                ]);
            } else {
                Log::warning("Librarian not authenticated in web context for cancellation notification.");
            }

            return response()->json([
                "status" => "success",
                "message" => "تم إلغاء الطلب بنجاح"
            ]);
        } catch (\Exception $e) {
            Log::error("Error in cancel: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ في إلغاء الطلب"
            ], 500);
        }
    }

    /**
     * Get borrowed books for current student
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function borrowedBooks(Request $request)
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بجلب الكتب المستعارة. يرجى تسجيل الدخول."
                ], 401);
            }

            $query = BookRequest::where("student_id", $student->student_id)
                ->where("type", "borrowing")
                ->where("status", "approved")
                ->with(["book"]);

            if ($request->filled("search")) {
                $search = $request->search;
                $query->whereHas("book", function ($sq) use ($search) {
                    $sq->where("book_name", "like", "%{$search}%")
                        ->orWhere("author", "like", "%{$search}%");
                });
            }

            $borrowedBooks = $query->orderBy("date_of_request", "desc")->paginate(15);

            return response()->json([
                "status" => "success",
                "data" => $borrowedBooks
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching borrowed books: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ أثناء جلب الكتب المستعارة"
            ], 500);
        }
    }
}


