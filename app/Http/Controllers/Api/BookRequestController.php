<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Book;
use App\Models\RetrieveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            $validator = Validator::make($request->all(), [
                'book_id' => 'required|exists:books,book_id',
                'type' => 'required|in:reading,borrowing',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if book exists and is available
            $book = Book::find($request->book_id);
            if (!$book) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب غير موجود'
                ], 404);
            }

            if ($book->status !== 'available') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب غير متاح حالياً'
                ], 400);
            }

            // Check if student already has a pending request for this book
            $existingRequest = BookRequest::where('student_id', $student->student_id)
                ->where('book_id', $request->book_id)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لديك طلب معلق بالفعل لهذا الكتاب'
                ], 400);
            }

            // Create the request
            $bookRequest = BookRequest::create([
                'student_id' => $student->student_id,
                'book_id' => $request->book_id,
                'type' => $request->type,
                'date_of_request' => now(),
                'status' => 'pending',
                'notes' => $request->notes
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال الطلب بنجاح',
                'data' => [
                    'request_id' => $bookRequest->request_id,
                    'book_id' => $bookRequest->book_id,
                    'type' => $bookRequest->type,
                    'status' => $bookRequest->status,
                    'date_of_request' => $bookRequest->date_of_request,
                    'notes' => $bookRequest->notes
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في إرسال الطلب'
            ], 500);
        }
    }

    /**
     * Get student's book requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            $query = BookRequest::where('student_id', $student->student_id)
                ->with(['book:book_id,book_name,author,image']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $requests = $query->orderBy('date_of_request', 'desc')->paginate($perPage);

            $requestsData = $requests->getCollection()->map(function($bookRequest) {
                return [
                    'request_id' => $bookRequest->request_id,
                    'book' => [
                        'book_id' => $bookRequest->book->book_id,
                        'book_name' => $bookRequest->book->book_name,
                        'author' => $bookRequest->book->author,
                        'image' => $bookRequest->book->image ? asset($bookRequest->book->image) : null
                    ],
                    'type' => $bookRequest->type,
                    'status' => $bookRequest->status,
                    'date_of_request' => $bookRequest->date_of_request,
                    'notes' => $bookRequest->notes,
                    'created_at' => $bookRequest->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'requests' => $requestsData,
                    'pagination' => [
                        'current_page' => $requests->currentPage(),
                        'total_pages' => $requests->lastPage(),
                        'total_items' => $requests->total(),
                        'per_page' => $requests->perPage(),
                        'has_next' => $requests->hasMorePages(),
                        'has_previous' => $requests->currentPage() > 1
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب الطلبات'
            ], 500);
        }
    }

    /**
     * Get specific book request details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            $bookRequest = BookRequest::where('request_id', $id)
                ->where('student_id', $student->student_id)
                ->with(['book', 'retrieveRequest'])
                ->first();

            if (!$bookRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الطلب غير موجود'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'request_id' => $bookRequest->request_id,
                    'book' => [
                        'book_id' => $bookRequest->book->book_id,
                        'book_name' => $bookRequest->book->book_name,
                        'author' => $bookRequest->book->author,
                        'image' => $bookRequest->book->image ? asset($bookRequest->book->image) : null,
                        'status' => $bookRequest->book->status
                    ],
                    'type' => $bookRequest->type,
                    'status' => $bookRequest->status,
                    'date_of_request' => $bookRequest->date_of_request,
                    'notes' => $bookRequest->notes,
                    'retrieve_request' => $bookRequest->retrieveRequest ? [
                        'retrieve_id' => $bookRequest->retrieveRequest->retrieve_id,
                        'request_date' => $bookRequest->retrieveRequest->request_date,
                        'status' => $bookRequest->retrieveRequest->status
                    ] : null,
                    'created_at' => $bookRequest->created_at,
                    'updated_at' => $bookRequest->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب تفاصيل الطلب'
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

