<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Book;
use App\Models\RetrieveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookRequestController extends Controller
{
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
            $request->validate([
                "student_id" => "required|exists:students,student_id",
                "book_id" => "required|exists:books,book_id",
                "type" => "required|in:reading,borrowing",
                "notes" => "nullable|string",
            ]);

            $bookRequest = new BookRequest();
            $bookRequest->student_id = $request->student_id;
            $bookRequest->book_id = $request->book_id;
            $bookRequest->type = $request->type;
            $bookRequest->date_of_request = now();
            $bookRequest->status = "pending";
            $bookRequest->notes = $request->notes;
            $bookRequest->save();

            Log::info("Book request saved successfully.");

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
            $student = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the original borrowing request
            $originalRequest = BookRequest::where('request_id', $id)
                ->where('student_id', $student->student_id)
                ->where('type', 'borrowing')
                ->where('status', 'approved')
                ->first();

            if (!$originalRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الطلب غير موجود أو غير مؤهل للإرجاع'
                ], 404);
            }

            // Check if there's already a return request
            $existingReturnRequest = BookRequest::where('student_id', $student->student_id)
                ->where('book_id', $originalRequest->book_id)
                ->where('type', 'return')
                ->where('status', 'pending')
                ->first();

            if ($existingReturnRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لديك طلب إرجاع معلق بالفعل لهذا الكتاب'
                ], 400);
            }

            // Create return request
            $returnRequest = BookRequest::create([
                'student_id' => $student->student_id,
                'book_id' => $originalRequest->book_id,
                'type' => 'return',
                'date_of_request' => now(),
                'status' => 'pending',
                'notes' => $request->notes ?? 'طلب إرجاع كتاب'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال طلب الإرجاع بنجاح. سيتم مراجعته من قبل أمين المكتبة.',
                'data' => [
                    'request_id' => $returnRequest->request_id,
                    'type' => $returnRequest->type,
                    'status' => $returnRequest->status,
                    'date_of_request' => $returnRequest->date_of_request
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في إرسال طلب الإرجاع'
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
            $student = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the original borrowing request
            $originalRequest = BookRequest::where('request_id', $id)
                ->where('student_id', $student->student_id)
                ->where('type', 'borrowing')
                ->where('status', 'approved')
                ->first();

            if (!$originalRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الطلب غير موجود أو غير مؤهل للتمديد'
                ], 404);
            }

            // Check if there's already an extension request
            $existingExtensionRequest = BookRequest::where('student_id', $student->student_id)
                ->where('book_id', $originalRequest->book_id)
                ->where('type', 'extension')
                ->where('status', 'pending')
                ->first();

            if ($existingExtensionRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لديك طلب تمديد معلق بالفعل لهذا الكتاب'
                ], 400);
            }

            // Create extension request
            $extensionRequest = BookRequest::create([
                'student_id' => $student->student_id,
                'book_id' => $originalRequest->book_id,
                'type' => 'extension',
                'date_of_request' => now(),
                'status' => 'pending',
                'notes' => $request->notes ?? 'طلب تمديد فترة استعارة'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال طلب التمديد بنجاح. سيتم مراجعته من قبل أمين المكتبة.',
                'data' => [
                    'request_id' => $extensionRequest->request_id,
                    'type' => $extensionRequest->type,
                    'status' => $extensionRequest->status,
                    'date_of_request' => $extensionRequest->date_of_request
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في إرسال طلب التمديد'
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
            $student = JWTAuth::parseToken()->authenticate();

            $bookRequest = BookRequest::where('request_id', $id)
                ->where('student_id', $student->student_id)
                ->where('status', 'pending')
                ->first();

            if (!$bookRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الطلب غير موجود أو لا يمكن إلغاؤه'
                ], 404);
            }

            $bookRequest->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إلغاء الطلب بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في إلغاء الطلب'
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
            $student = JWTAuth::parseToken()->authenticate();

            $borrowedBooks = BookRequest::where('student_id', $student->student_id)
                ->where('type', 'borrowing')
                ->where('status', 'approved')
                ->with(['book:book_id,book_name,author,image,status'])
                ->whereHas('book', function($query) {
                    $query->where('status', 'borrowed');
                })
                ->orderBy('date_of_request', 'desc')
                ->get();

            $booksData = $borrowedBooks->map(function($request) {
                $borrowDate = $request->date_of_request;
                $dueDate = $borrowDate->addDays(14); // Assuming 14 days borrowing period
                $daysRemaining = now()->diffInDays($dueDate, false);

                return [
                    'request_id' => $request->request_id,
                    'book_id' => $request->book->book_id,
                    'book_name' => $request->book->book_name,
                    'author' => $request->book->author,
                    'image' => $request->book->image ? asset($request->book->image) : null,
                    'borrowed_date' => $request->date_of_request,
                    'due_date' => $dueDate,
                    'days_remaining' => $daysRemaining,
                    'is_overdue' => $daysRemaining < 0
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'borrowed_books' => $booksData,
                    'total_borrowed' => $booksData->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب الكتب المستعارة'
            ], 500);
        }
    }
}

