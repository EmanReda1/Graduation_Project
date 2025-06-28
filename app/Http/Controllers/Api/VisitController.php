<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class VisitController extends Controller
{
    /**
     * Get student's visit history
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get student from token (if available)
            $student = null;
            try {
                $student = JWTAuth::parseToken()->authenticate();
                if (!$student) {
                    $studentIdFromToken = JWTAuth::parseToken()->getPayload()->get("sub");
                    if ($studentIdFromToken) {
                        $student = Student::find($studentIdFromToken);
                    }
                }
            } catch (\Exception $e) {
                // If no token or invalid token, return error
                return response()->json([
                    'status' => 'error',
                    'message' => 'يجب تسجيل الدخول أولاً'
                ], 401);
            }

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            $perPage = $request->get('per_page', 10);

            $visits = Visit::where('student_id', $student->student_id)
                ->orderBy('visit_time', 'desc')
                ->paginate($perPage);

            $visitsData = $visits->getCollection()->map(function($visit) {
                return [
                    'visit_id' => $visit->visit_id,
                    'visit_time' => $visit->visit_time,
                    'visit_date' => Carbon::parse($visit->visit_time)->format('Y-m-d'),
                    'visit_time_formatted' => Carbon::parse($visit->visit_time)->format('h:i A'),
                    'created_at' => $visit->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'visits' => $visitsData,
                    'pagination' => [
                        'current_page' => $visits->currentPage(),
                        'total_pages' => $visits->lastPage(),
                        'total_items' => $visits->total(),
                        'per_page' => $visits->perPage()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in visits index: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب سجل الزيارات'
            ], 500);
        }
    }

    /**
     * Record a new visit by scanning QR code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scan(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'qr_code' => 'required|string',
                'location' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get student from token
            $student = null;
            try {
                $student = JWTAuth::parseToken()->authenticate();
                if (!$student) {
                    $studentIdFromToken = JWTAuth::parseToken()->getPayload()->get("sub");
                    if ($studentIdFromToken) {
                        $student = Student::find($studentIdFromToken);
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'يجب تسجيل الدخول أولاً'
                ], 401);
            }

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // Validate QR code (should be the library's QR code)
            $validQrCodes = [
                'LIBRARY_001',
                'FCI_ZU_LIBRARY',
                'MAIN_LIBRARY_ENTRANCE'
            ];

            if (!in_array($request->qr_code, $validQrCodes)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'رمز QR غير صحيح أو غير مصرح به'
                ], 400);
            }

            // Check if student already visited today
            $today = Carbon::today();
            $existingVisit = Visit::where('student_id', $student->student_id)
                ->whereDate('visit_time', $today)
                ->first();

            if ($existingVisit) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'لقد قمت بتسجيل زيارة اليوم بالفعل',
                    'data' => [
                        'existing_visit' => [
                            'visit_id' => $existingVisit->visit_id,
                            'visit_time' => $existingVisit->visit_time,
                            'visit_time_formatted' => Carbon::parse($existingVisit->visit_time)->format('h:i A')
                        ]
                    ]
                ], 200);
            }

            // Create new visit
            $visit = Visit::create([
                'student_id' => $student->student_id,
                'visit_time' => Carbon::now()
            ]);

            Log::info("New visit recorded for student ID: {$student->student_id}, Visit ID: {$visit->visit_id}");

            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل الزيارة بنجاح',
                'data' => [
                    'visit' => [
                        'visit_id' => $visit->visit_id,
                        'student_name' => $student->fullname,
                        'visit_time' => $visit->visit_time,
                        'visit_time_formatted' => Carbon::parse($visit->visit_time)->format('h:i A'),
                        'visit_date' => Carbon::parse($visit->visit_time)->format('Y-m-d')
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error in visit scan: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في تسجيل الزيارة'
            ], 500);
        }
    }

    /**
     * Check if student can visit today (hasn't visited yet)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function canVisitToday(Request $request)
    {
        try {
            // Get student from token
            $student = null;
            try {
                $student = JWTAuth::parseToken()->authenticate();
                if (!$student) {
                    $studentIdFromToken = JWTAuth::parseToken()->getPayload()->get("sub");
                    if ($studentIdFromToken) {
                        $student = Student::find($studentIdFromToken);
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'يجب تسجيل الدخول أولاً'
                ], 401);
            }

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // Check if student already visited today
            $today = Carbon::today();
            $existingVisit = Visit::where('student_id', $student->student_id)
                ->whereDate('visit_time', $today)
                ->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'can_visit' => !$existingVisit,
                    'existing_visit' => $existingVisit ? [
                        'visit_id' => $existingVisit->visit_id,
                        'visit_time' => $existingVisit->visit_time,
                        'visit_time_formatted' => Carbon::parse($existingVisit->visit_time)->format('h:i A')
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in canVisitToday: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في التحقق من إمكانية الزيارة'
            ], 500);
        }
    }

    /**
     * Get visit statistics for student
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        try {
            // Get student from token
            $student = null;
            try {
                $student = JWTAuth::parseToken()->authenticate();
                if (!$student) {
                    $studentIdFromToken = JWTAuth::parseToken()->getPayload()->get("sub");
                    if ($studentIdFromToken) {
                        $student = Student::find($studentIdFromToken);
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'يجب تسجيل الدخول أولاً'
                ], 401);
            }

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // Get statistics
            $totalVisits = Visit::where('student_id', $student->student_id)->count();

            $thisMonthVisits = Visit::where('student_id', $student->student_id)
                ->whereMonth('visit_time', Carbon::now()->month)
                ->whereYear('visit_time', Carbon::now()->year)
                ->count();

            $lastVisit = Visit::where('student_id', $student->student_id)
                ->orderBy('visit_time', 'desc')
                ->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_visits' => $totalVisits,
                    'this_month_visits' => $thisMonthVisits,
                    'last_visit' => $lastVisit ? [
                        'visit_time' => $lastVisit->visit_time,
                        'visit_time_formatted' => Carbon::parse($lastVisit->visit_time)->format('h:i A'),
                        'visit_date' => Carbon::parse($lastVisit->visit_time)->format('Y-m-d')
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in visit statistics: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب إحصائيات الزيارات'
            ], 500);
        }
    }

    /**
     * Get recent visits for student
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(Request $request)
    {
        try {
            // Get student from token
            $student = null;
            try {
                $student = JWTAuth::parseToken()->authenticate();
                if (!$student) {
                    $studentIdFromToken = JWTAuth::parseToken()->getPayload()->get("sub");
                    if ($studentIdFromToken) {
                        $student = Student::find($studentIdFromToken);
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'يجب تسجيل الدخول أولاً'
                ], 401);
            }

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            $limit = $request->get('limit', 5);

            $visits = Visit::where('student_id', $student->student_id)
                ->orderBy('visit_time', 'desc')
                ->limit($limit)
                ->get();

            $visitsData = $visits->map(function($visit) {
                return [
                    'visit_id' => $visit->visit_id,
                    'visit_time' => $visit->visit_time,
                    'visit_date' => Carbon::parse($visit->visit_time)->format('Y-m-d'),
                    'visit_time_formatted' => Carbon::parse($visit->visit_time)->format('h:i A'),
                    'days_ago' => Carbon::parse($visit->visit_time)->diffForHumans()
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'recent_visits' => $visitsData
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in recent visits: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب الزيارات الحديثة'
            ], 500);
        }
    }

    /**
     * Get monthly visit data for charts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthly(Request $request)
    {
        try {
            // Get student from token
            $student = null;
            try {
                $student = JWTAuth::parseToken()->authenticate();
                if (!$student) {
                    $studentIdFromToken = JWTAuth::parseToken()->getPayload()->get("sub");
                    if ($studentIdFromToken) {
                        $student = Student::find($studentIdFromToken);
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'يجب تسجيل الدخول أولاً'
                ], 401);
            }

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // Get visits for the last 6 months
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $count = Visit::where('student_id', $student->student_id)
                    ->whereMonth('visit_time', $date->month)
                    ->whereYear('visit_time', $date->year)
                    ->count();

                $monthlyData[] = [
                    'month' => $date->format('M Y'),
                    'visits' => $count
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'monthly_visits' => $monthlyData
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in monthly visits: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب البيانات الشهرية'
            ], 500);
        }
    }
}

