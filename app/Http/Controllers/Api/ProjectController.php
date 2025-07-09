<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ProjectController extends Controller
{
    /**
     * Get list of projects with search and filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Project::query();

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%")
                      ->orWhere('supervisor', 'like', "%{$search}%");
                });
            }

            // Apply department filter
            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }

            // Apply status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply year filter
            if ($request->filled('year')) {
                $query->whereYear('project_date', $request->year);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $projects = $query->orderBy('project_date', 'desc')->paginate($perPage);

            $projectsData = $projects->getCollection()->map(function($project) {
                return [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'department' => $project->department,
                    'status' => $project->status,
                    'supervisor' => $project->supervisor,
                    'project_date' => $project->project_date,
                    'image' => $project->image ? asset('storage/' . $project->image) : null,
                    'pdf_available' => !empty($project->pdf),
                    'pdf_url' => $project->pdf ? route('api.projects.pdf', $project->project_id) : null,
                    'place' => $project->place,
                    'shelf_no' => $project->shelf_no
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'projects' => $projectsData,
                    'pagination' => [
                        'current_page' => $projects->currentPage(),
                        'total_pages' => $projects->lastPage(),
                        'total_items' => $projects->total(),
                        'per_page' => $projects->perPage(),
                        'has_next' => $projects->hasMorePages(),
                        'has_previous' => $projects->currentPage() > 1
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب المشاريع'
            ], 500);
        }
    }

    /**
     * Get project details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Attempt to get authenticated student for favorited status, but don't require it
            $student = null;
            try {
                $student = JWTAuth::parseToken()->authenticate();
            } catch (\Exception $e) {
                // Token not provided or invalid, continue without student context
            }

            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المشروع غير موجود'
                ], 404);
            }

            // Check if 'image' attribute exists and is not null before using asset()
            $imageUrl = null;
            if (isset($project->image) && !empty($project->image)) {
                try {
                    $imageUrl = asset('storage/' . $project->image);
                } catch (\Exception $e) {
                    $imageUrl = null; // Fallback if asset() fails
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'department' => $project->department,
                    'status' => $project->status,
                    'place' => $project->place,
                    'shelf_no' => $project->shelf_no,
                    'supervisor' => $project->supervisor,
                    'project_date' => $project->project_date,
                    'summary' => $project->summary,
                    'image' => $imageUrl,
                    'pdf_available' => !empty($project->pdf),
                    'pdf_url' => $project->pdf ? route('api.projects.pdf', $project->project_id) : null,
                    'created_at' => $project->created_at,
                    'updated_at' => $project->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            // Log the actual exception message for debugging
            Log::error('Error fetching project details: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب تفاصيل المشروع'
            ], 500);
        }
    }

    /**
     * View/Download project PDF
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function viewPdf($id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المشروع غير موجود'
                ], 404);
            }

            if (!$project->pdf || !Storage::disk('public')->exists($project->pdf)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ملف PDF غير موجود'
                ], 404);
            }

            $filePath = Storage::disk('public')->path($project->pdf);
            $fileName = $project->project_name . '.pdf';

            return Response::file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Error viewing PDF: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في عرض ملف PDF'
            ], 500);
        }
    }

    /**
     * Download project PDF
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

   /*public function downloadPdf($id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المشروع غير موجود'
                ], 404);
            }

            if (!$project->pdf || !Storage::disk('public')->exists($project->pdf)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ملف PDF غير موجود'
                ], 404);
            }

            $filePath = Storage::disk('public')->path($project->pdf);
            $fileName = $project->project_name . '.pdf';

            return Response::download($filePath, $fileName);

        } catch (\Exception $e) {
            Log::error('Error downloading PDF: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في تحميل ملف PDF'
            ], 500);
        }
    }*/

    /**
     * Get projects by department
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
     * Get available projects only
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function available(Request $request)
    {
        $request->merge(['status' => 'available']);
        return $this->index($request);
    }

    /**
     * Search projects
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
     * Get recent projects
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $projects = Project::orderBy('created_at', 'desc')->paginate($perPage);

            $projectsData = $projects->getCollection()->map(function($project) {
                return [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'department' => $project->department,
                    'status' => $project->status,
                    'supervisor' => $project->supervisor,
                    'project_date' => $project->project_date,
                    'image' => $project->image ? asset('storage/' . $project->image) : null,
                    'pdf_available' => !empty($project->pdf),
                    'pdf_url' => $project->pdf ? route('api.projects.pdf', $project->project_id) : null,
                    'created_at' => $project->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'projects' => $projectsData,
                    'pagination' => [
                        'current_page' => $projects->currentPage(),
                        'total_pages' => $projects->lastPage(),
                        'total_items' => $projects->total(),
                        'per_page' => $projects->perPage()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب المشاريع الحديثة'
            ], 500);
        }
    }

    /**
     * Get projects by year
     *
     * @param Request $request
     * @param int $year
     * @return \Illuminate\Http\JsonResponse
     */
    public function byYear(Request $request, $year)
    {
        $request->merge(['year' => $year]);
        return $this->index($request);
    }

    /**
     * Get project statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $totalProjects = Project::count();
            $availableProjects = Project::where('status', 'available')->count();
            $unavailableProjects = Project::where('status', 'unavailable')->count();
            $projectsWithPdf = Project::whereNotNull('pdf')->where('pdf', '!=', '')->count();

            // Projects by department
            $projectsByDepartment = Project::selectRaw('department, COUNT(*) as count')
                ->groupBy('department')
                ->get()
                ->map(function($item) {
                    return [
                        'department' => $item->department,
                        'count' => $item->count
                    ];
                });

            // Projects by year
            $projectsByYear = Project::selectRaw('YEAR(project_date) as year, COUNT(*) as count')
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
                    'total_projects' => $totalProjects,
                    'available_projects' => $availableProjects,
                    'unavailable_projects' => $unavailableProjects,
                    'projects_with_pdf' => $projectsWithPdf,
                    'projects_by_department' => $projectsByDepartment,
                    'projects_by_year' => $projectsByYear
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب إحصائيات المشاريع'
            ], 500);
        }
    }
}
