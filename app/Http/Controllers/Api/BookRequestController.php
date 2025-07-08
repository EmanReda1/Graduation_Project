<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Book;
use App\Models\Notification;
use App\Models\RetrieveRequest;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
     * Get student notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request)
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بجلب الإشعارات. يرجى تسجيل الدخول."
                ], 401);
            }

            $query = Notification::where("student_id", $student->student_id);

            // Filter by read status if provided
            if ($request->filled("is_read")) {
                $query->where("is_read", $request->boolean("is_read"));
            }

            // Filter by type if provided
            if ($request->filled("type")) {
                $query->where("type", $request->type);
            }

            $notifications = $query->orderBy("date_time", "desc")->paginate(15);

            return response()->json([
                "status" => "success",
                "data" => $notifications
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching student notifications: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ أثناء جلب الإشعارات"
            ], 500);
        }
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markNotificationAsRead($notificationId)
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بتحديث الإشعارات. يرجى تسجيل الدخول."
                ], 401);
            }

            $notification = Notification::where("notification_id", $notificationId)
                ->where("student_id", $student->student_id)
                ->first();

            if (!$notification) {
                return response()->json([
                    "status" => "error",
                    "message" => "الإشعار غير موجود"
                ], 404);
            }

            $notification->is_read = true;
            $notification->save();

            return response()->json([
                "status" => "success",
                "message" => "تم تحديث حالة الإشعار"
            ]);
        } catch (\Exception $e) {
            Log::error("Error marking notification as read: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ أثناء تحديث الإشعار"
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllNotificationsAsRead()
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بتحديث الإشعارات. يرجى تسجيل الدخول."
                ], 401);
            }

            Notification::where("student_id", $student->student_id)
                ->where("is_read", false)
                ->update(["is_read" => true]);

            return response()->json([
                "status" => "success",
                "message" => "تم تحديث جميع الإشعارات"
            ]);
        } catch (\Exception $e) {
            Log::error("Error marking all notifications as read: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ أثناء تحديث الإشعارات"
            ], 500);
        }
    }

    /**
     * Get unread notifications count
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadNotificationsCount()
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بجلب الإشعارات. يرجى تسجيل الدخول."
                ], 401);
            }

            $count = Notification::where("student_id", $student->student_id)
                ->where("is_read", false)
                ->count();

            return response()->json([
                "status" => "success",
                "data" => [
                    "unread_count" => $count
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching unread notifications count: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ أثناء جلب عدد الإشعارات"
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

            // قواعد التحقق الأساسية
            $validationRules = [
                "book_id" => "required|exists:books,book_id",
                "type" => "required|in:reading,borrowing",
                "notes" => "nullable|string",
                "phone_number" => "nullable|string|max:20", // رقم التليفون اختياري
            ];

            // إضافة قواعد التحقق للأوراق فقط إذا كان النوع "borrowing"
            if ($request->type === 'borrowing') {
                $validationRules["id_card_image"] = "required|file|mimes:jpg,jpeg,png|max:2048"; // صورة البطاقة مطلوبة
                $validationRules["eagle_seal_document"] = "required|file|mimes:pdf,jpg,jpeg,png|max:2048"; // ورقة ختم النسر مطلوبة
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => "خطأ في التحقق من البيانات",
                    "errors" => $validator->errors()
                ], 422);
            }

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

            // معالجة رفع الأوراق إذا كان النوع "borrowing"
            $documentsUploaded = 0;
            if ($request->type === 'borrowing') {
                // الحصول على الأوراق الموجودة مسبقاً
                $existingDocs = $student->borrow_docs ? json_decode($student->borrow_docs, true) : [];

                // التأكد من أن existingDocs هو مصفوفة
                if (!is_array($existingDocs)) {
                    $existingDocs = [];
                }

                $newDocs = $existingDocs;
                $uploadedFiles = [];

                // معالجة صورة البطاقة
                if ($request->hasFile('id_card_image')) {
                    $idCardFile = $request->file('id_card_image');
                    $idCardFileName = 'id_card_' . $student->student_id . '_' . time() . '.' . $idCardFile->getClientOriginalExtension();
                    $idCardPath = $idCardFile->storeAs('student_documents', $idCardFileName, 'public');

                    if ($idCardPath) {
                        // حذف صورة البطاقة القديمة إذا كانت موجودة
                        if (isset($newDocs['id_card']) && Storage::disk('public')->exists($newDocs['id_card'])) {
                            Storage::disk('public')->delete($newDocs['id_card']);
                        }
                        $newDocs['id_card'] = $idCardPath;
                        $uploadedFiles[] = 'صورة البطاقة';
                        $documentsUploaded++;
                    }
                }

                // معالجة ورقة ختم النسر
                if ($request->hasFile('eagle_seal_document')) {
                    $eagleSealFile = $request->file('eagle_seal_document');
                    $eagleSealFileName = 'eagle_seal_' . $student->student_id . '_' . time() . '.' . $eagleSealFile->getClientOriginalExtension();
                    $eagleSealPath = $eagleSealFile->storeAs('student_documents', $eagleSealFileName, 'public');

                    if ($eagleSealPath) {
                        // حذف ورقة ختم النسر القديمة إذا كانت موجودة
                        if (isset($newDocs['eagle_seal']) && Storage::disk('public')->exists($newDocs['eagle_seal'])) {
                            Storage::disk('public')->delete($newDocs['eagle_seal']);
                        }
                        $newDocs['eagle_seal'] = $eagleSealPath;
                        $uploadedFiles[] = 'ورقة ختم النسر';
                        $documentsUploaded++;
                    }
                }

                // تحديث حقل borrow_docs في جدول الطالب
                /** @var \App\Models\Student $student */
                $student->borrow_docs = json_encode($newDocs);
                $student->save();

                Log::info("Documents uploaded for student: " . $student->student_id . " - Files: " . implode(', ', $uploadedFiles));
            }

            // إنشاء طلب الكتاب
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

            // إرسال إشعار للطالب بتأكيد استلام الطلب
            $this->sendNotificationToStudent($student->student_id, [
                'type' => 'request_submitted',
                'message' => 'تم استلام طلب ' . ($bookRequest->type === 'reading' ? 'قراءة' : 'استعارة') . ' للكتاب "' . $bookRequest->book->book_name . '" وسيتم مراجعته قريباً.'
            ]);

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

            $responseMessage = "تم إنشاء طلب الكتاب بنجاح.";
            if ($documentsUploaded > 0) {
                $responseMessage .= " تم رفع " . $documentsUploaded . " ملف(ات) بنجاح.";
            }

            return response()->json([
                "status" => "success",
                "message" => $responseMessage,
                "data" => [
                    "request" => $bookRequest,
                    "documents_uploaded" => $documentsUploaded,
                    "has_documents" => $request->type === 'borrowing' ? $this->checkStudentDocuments($student) : false
                ]
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
     * Update book request status by librarian (should be called from librarian side)
     *
     * @param Request $request
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRequestStatus(Request $request, $requestId)
    {
        try {
            $validator = Validator::make($request->all(), [
                "status" => "required|in:approved,rejected",
                "librarian_notes" => "nullable|string|max:500"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => "بيانات غير صحيحة",
                    "errors" => $validator->errors()
                ], 422);
            }

            $bookRequest = BookRequest::with('book', 'student')->find($requestId);

            if (!$bookRequest) {
                return response()->json([
                    "status" => "error",
                    "message" => "الطلب غير موجود"
                ], 404);
            }

            $oldStatus = $bookRequest->status;
            $bookRequest->status = $request->status;
            $bookRequest->librarian_notes = $request->librarian_notes;
            $bookRequest->save();

            // إرسال إشعار للطالب حسب حالة الطلب
            $this->sendRequestStatusNotification($bookRequest, $request->status, $request->librarian_notes);

            return response()->json([
                "status" => "success",
                "message" => "تم تحديث حالة الطلب بنجاح",
                "data" => [
                    "request_id" => $bookRequest->request_id,
                    "old_status" => $oldStatus,
                    "new_status" => $bookRequest->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating request status: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ أثناء تحديث حالة الطلب"
            ], 500);
        }
    }

    /**
     * Send notification to student based on request status
     *
     * @param BookRequest $bookRequest
     * @param string $status
     * @param string|null $librarianNotes
     */
    private function sendRequestStatusNotification($bookRequest, $status, $librarianNotes = null)
    {
        $bookName = $bookRequest->book->book_name ?? 'غير معروف';
        $requestType = $bookRequest->type === 'reading' ? 'قراءة' : 'استعارة';

        switch ($status) {
            case 'approved':
                if ($bookRequest->type === 'borrowing') {
                    $message = "تم قبول طلب استعارة الكتاب \"$bookName\". الكتاب متاح للاستعارة وسيتم توفيره خلال 3 أيام من تاريخ الموافقة.";
                    $type = 'borrowing_approved';
                } else {
                    $message = "تم قبول طلب قراءة الكتاب \"$bookName\". الكتاب متاح للقراءة وسيتم توفيره خلال يوم واحد من تاريخ الموافقة.";
                    $type = 'reading_approved';
                }
                break;

            case 'rejected':
                $message = "تم رفض طلب $requestType الكتاب \"$bookName\".";
                if ($librarianNotes) {
                    $message .= " السبب: $librarianNotes";
                }
                $type = $bookRequest->type . '_rejected';
                break;

            default:
                return; // لا نرسل إشعار للحالات الأخرى
        }

        $this->sendNotificationToStudent($bookRequest->student_id, [
            'type' => $type,
            'message' => $message
        ]);
    }

    /**
     * Send notification to student
     *
     * @param int $studentId
     * @param array $notificationData
     */
    private function sendNotificationToStudent($studentId, $notificationData)
    {
        try {
            Notification::create([
                "student_id" => $studentId,
                "message" => $notificationData['message'],
                "type" => $notificationData['type'],
                "is_read" => false,
                "date_time" => now(),
            ]);

            Log::info("Notification sent to student: $studentId, Type: " . $notificationData['type']);
        } catch (\Exception $e) {
            Log::error("Error sending notification to student: " . $e->getMessage());
        }
    }

    /**
     * Check if student has all required documents
     *
     * @param Student $student
     * @return array
     */
    private function checkStudentDocuments($student)
    {
        $docs = $student->borrow_docs ? json_decode($student->borrow_docs, true) : [];

        return [
            'has_id_card' => isset($docs['id_card']) && !empty($docs['id_card']),
            'has_eagle_seal' => isset($docs['eagle_seal']) && !empty($docs['eagle_seal']),
            'all_documents_available' => isset($docs['id_card']) && isset($docs['eagle_seal']) && !empty($docs['id_card']) && !empty($docs['eagle_seal'])
        ];
    }

    /**
     * Get student documents status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocumentsStatus(Request $request)
    {
        try {
            $student = Auth::guard("api")->user();

            if (!$student) {
                return response()->json([
                    "status" => "error",
                    "message" => "غير مصرح لك بالوصول. يرجى تسجيل الدخول."
                ], 401);
            }

            $documentsStatus = $this->checkStudentDocuments($student);

            return response()->json([
                "status" => "success",
                "data" => $documentsStatus
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching documents status: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "حدث خطأ أثناء جلب حالة الأوراق"
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

            // إرسال إشعار للطالب بتأكيد استلام طلب الإرجاع
            $this->sendNotificationToStudent($student->student_id, [
                'type' => 'return_request_submitted',
                'message' => 'تم استلام طلب إرجاع الكتاب "' . $originalRequest->book->book_name . '" وسيتم مراجعته من قبل أمين المكتبة.'
            ]);

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

            // إرسال إشعار للطالب بتأكيد استلام طلب التمديد
            $this->sendNotificationToStudent($student->student_id, [
                'type' => 'extension_request_submitted',
                'message' => 'تم استلام طلب تمديد فترة استعارة الكتاب "' . $originalRequest->book->book_name . '" وسيتم مراجعته من قبل أمين المكتبة.'
            ]);

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

            // إرسال إشعار للطالب بتأكيد إلغاء الطلب
            $requestType = $bookRequest->type === 'reading' ? 'قراءة' :
                          ($bookRequest->type === 'borrowing' ? 'استعارة' : 'تمديد');

            $this->sendNotificationToStudent($student->student_id, [
                'type' => 'request_cancelled',
                'message' => 'تم إلغاء طلب ' . $requestType . ' الكتاب "' . ($bookRequest->book->book_name ?? 'غير معروف') . '" بنجاح.'
            ]);

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
