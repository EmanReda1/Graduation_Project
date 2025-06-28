<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExamController extends Controller
{
    /**
     * Get list of exams with search and filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Exam::query();

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('course_name', 'like', "%{$search}%")
                      ->orWhere('doctor', 'like', "%{$search}%");
                });
            }

            // Apply department filter
            if ($request->filled('department')) {
                $query->where('dept', $request->department);
            }

            // Apply level filter
            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            // Apply semester filter
            if ($request->filled('semester')) {
                $query->where('semester', $request->semester);
            }

            // Apply type filter
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Apply year filter
            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $exams = $query->orderBy('year', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage);

            $examsData = $exams->getCollection()->map(function($exam) {
                return [
                    'exam_id' => $exam->exam_id,
                    'type' => $exam->type,
                    'course_name' => $exam->course_name,
                    'department' => $exam->dept,
                    'semester' => $exam->semester,
                    'level' => $exam->level,
                    'doctor' => $exam->doctor,
                    'year' => $exam->year,
                    'has_pdf' => !empty($exam->pdf),
                    'created_at' => $exam->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'exams' => $examsData,
                    'pagination' => [
                        'current_page' => $exams->currentPage(),
                        'total_pages' => $exams->lastPage(),
                        'total_items' => $exams->total(),
                        'per_page' => $exams->perPage(),
                        'has_next' => $exams->hasMorePages(),
                        'has_previous' => $exams->currentPage() > 1
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب الامتحانات'
            ], 500);
        }
    }

    /**
     * Get exam details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $exam = Exam::find($id);
            
            if (!$exam) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الامتحان غير موجود'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'exam_id' => $exam->exam_id,
                    'type' => $exam->type,
                    'course_name' => $exam->course_name,
                    'department' => $exam->dept,
                    'semester' => $exam->semester,
                    'level' => $exam->level,
                    'doctor' => $exam->doctor,
                    'year' => $exam->year,
                    'pdf_url' => $exam->pdf ? asset($exam->pdf) : null,
                    'has_pdf' => !empty($exam->pdf),
                    'created_at' => $exam->created_at,
                    'updated_at' => $exam->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب تفاصيل الامتحان'
            ], 500);
        }
    }

    /**
     * Download exam PDF
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            $exam = Exam::find($id);
            
            if (!$exam) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الامتحان غير موجود'
                ], 404);
            }

            if (!$exam->pdf || !file_exists(public_path($exam->pdf))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ملف الامتحان غير متوفر'
                ], 404);
            }

            $filePath = public_path($exam->pdf);
            $fileName = $exam->course_name . '_' . $exam->type . '_' . $exam->year . '.pdf';

            // Log download activity (optional)
            // You can add logging here if needed

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في تحميل الملف'
            ], 500);
        }
    }

    /**
     * Get exams by department
     *
     * @param Request $request
     * @param string $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function byDepartment(Request $request, $department)
    {
        $request->merge(['department' => $department]);
        return $this->index($request);
    }

    /**
     * Get exams by level
     *
     * @param Request $request
     * @param string $level
     * @return \Illuminate\Http\JsonResponse
     */
    public function byLevel(Request $request, $level)
    {
        $request->merge(['level' => $level]);
        return $this->index($request);
    }

    /**
     * Get exams by semester
     *
     * @param Request $request
     * @param string $semester
     * @return \Illuminate\Http\JsonResponse
     */
    public function bySemester(Request $request, $semester)
    {
        $request->merge(['semester' => $semester]);
        return $this->index($request);
    }

    /**
     * Get exams by type
     *
     * @param Request $request
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function byType(Request $request, $type)
    {
        $request->merge(['type' => $type]);
        return $this->index($request);
    }

    /**
     * Search exams
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'يجب أن يكون النص المراد البحث عنه أكثر من حرفين',
                'errors' => $validator->errors()
            ], 422);
        }

        $request->merge(['search' => $request->query]);
        return $this->index($request);
    }

    /**
     * Get recent exams
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            
            $exams = Exam::orderBy('created_at', 'desc')->paginate($perPage);

            $examsData = $exams->getCollection()->map(function($exam) {
                return [
                    'exam_id' => $exam->exam_id,
                    'type' => $exam->type,
                    'course_name' => $exam->course_name,
                    'department' => $exam->dept,
                    'semester' => $exam->semester,
                    'level' => $exam->level,
                    'doctor' => $exam->doctor,
                    'year' => $exam->year,
                    'has_pdf' => !empty($exam->pdf),
                    'created_at' => $exam->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'exams' => $examsData,
                    'pagination' => [
                        'current_page' => $exams->currentPage(),
                        'total_pages' => $exams->lastPage(),
                        'total_items' => $exams->total(),
                        'per_page' => $exams->perPage()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب الامتحانات الحديثة'
            ], 500);
        }
    }

    /**
     * Get exam statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $totalExams = Exam::count();

            // Exams by type
            $examsByType = Exam::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->map(function($item) {
                    return [
                        'type' => $item->type,
                        'count' => $item->count
                    ];
                });

            // Exams by department
            $examsByDepartment = Exam::selectRaw('dept as department, COUNT(*) as count')
                ->groupBy('dept')
                ->get()
                ->map(function($item) {
                    return [
                        'department' => $item->department,
                        'count' => $item->count
                    ];
                });

            // Exams by level
            $examsByLevel = Exam::selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->orderBy('level')
                ->get()
                ->map(function($item) {
                    return [
                        'level' => $item->level,
                        'count' => $item->count
                    ];
                });

            // Exams by year
            $examsByYear = Exam::selectRaw('year, COUNT(*) as count')
                ->groupBy('year')
                ->orderBy('year', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'year' => $item->year,
                        'count' => $item->count
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_exams' => $totalExams,
                    'exams_by_type' => $examsByType,
                    'exams_by_department' => $examsByDepartment,
                    'exams_by_level' => $examsByLevel,
                    'exams_by_year' => $examsByYear
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب إحصائيات الامتحانات'
            ], 500);
        }
    }

    /**
     * Get available filters for exams
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filters()
    {
        try {
            $departments = Exam::distinct()->pluck('dept')->filter()->values();
            $levels = Exam::distinct()->pluck('level')->filter()->sort()->values();
            $semesters = Exam::distinct()->pluck('semester')->filter()->values();
            $types = Exam::distinct()->pluck('type')->filter()->values();
            $years = Exam::distinct()->pluck('year')->filter()->sort()->reverse()->values();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'departments' => $departments,
                    'levels' => $levels,
                    'semesters' => $semesters,
                    'types' => $types,
                    'years' => $years
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب خيارات التصفية'
            ], 500);
        }
    }
}

