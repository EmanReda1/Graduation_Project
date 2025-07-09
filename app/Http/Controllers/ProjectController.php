<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BookRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(HttpRequest $request)
    {
        $query = Project::query();

        // Apply filters
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('place', 'like', "%{$search}%");
            });
        }

        // Sort projects
        $sortField = $request->get('sort', 'project_name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $projects = $query->paginate(15);

        // Get unique departments for filter dropdown
        $departments = Project::select('department')->distinct()->pluck('department');

        // Get statuses for filter dropdown
        $statuses = ['available', 'borrowed', 'archived'];

        return view('projects.index', compact('projects', 'departments', 'statuses'));
    }

    /**
     * Show the form for creating a new project.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get unique departments for dropdown
        $departments = Project::select('department')->distinct()->pluck('department');

        // Get statuses for dropdown
        $statuses = ['available', 'borrowed', 'archived'];

        return view('projects.create', compact('departments', 'statuses'));
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HttpRequest $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'supervisor' => 'required|string|max:255',
            'project_date' => 'required|date|before_or_equal:today',
            'department' => 'required|string|max:100',
            'status' => 'required|string|in:available,borrowed,archived',
            'place' => 'nullable|string|max:255',
            'shelf_no' => 'nullable|string|max:50',
            'sum' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pdf' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            // Store in storage/app/public/images/projects
            $imagePath = $image->storeAs('images/projects', $imageName, 'public');
        }

        // Handle PDF upload
        $pdfPath = null;
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . '_' . $pdf->getClientOriginalName();

            // Store in storage/app/public/pdfs/projects
            $pdfPath = $pdf->storeAs('pdfs/projects', $pdfName, 'public');
        }

        // Create project
        $project = Project::create([
            'project_name' => $validated['project_name'],
            'department' => $validated['department'],
            'status' => $validated['status'],
            'supervisor' => $validated['supervisor'],
            'project_date' => $validated['project_date'],
            'place' => $validated['place'] ?? null,
            'shelf_no' => $validated['shelf_no'] ?? null,
            'sum' => $validated['sum'] ?? null,
            'image' => $imagePath,
            'pdf' => $pdfPath,
        ]);

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'تم إضافة المشروع بنجاح.');
    }

    /**
     * Display the specified project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::findOrFail($id);

        // Get related requests
        $requests = BookRequest::where('project_id', $id)
            ->with('student')
            ->orderBy('date_of_request', 'desc')
            ->paginate(10);

        return view('projects.show', compact('project', 'requests'));
    }

    /**
     * Show the form for editing the specified project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);

        // Get unique departments for dropdown
        $departments = Project::select('department')->distinct()->pluck('department');

        // Get statuses for dropdown
        $statuses = ['available', 'borrowed', 'archived'];

        return view('projects.edit', compact('project', 'departments', 'statuses'));
    }

    /**
     * Update the specified project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(HttpRequest $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'department' => 'required|string|max:100',
            'status' => 'required|string|in:available,borrowed,archived',
            'place' => 'nullable|string|max:255',
            'shelf_no' => 'nullable|string|max:50',
            'sum' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pdf' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($project->image && Storage::disk('public')->exists($project->image)) {
                Storage::disk('public')->delete($project->image);
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $validated['image'] = $image->storeAs('images/projects', $imageName, 'public');
        }

        // Handle PDF upload
        if ($request->hasFile('pdf')) {
            // Delete old PDF if exists
            if ($project->pdf && Storage::disk('public')->exists($project->pdf)) {
                Storage::disk('public')->delete($project->pdf);
            }

            $pdf = $request->file('pdf');
            $pdfName = time() . '_' . $pdf->getClientOriginalName();
            $validated['pdf'] = $pdf->storeAs('pdfs/projects', $pdfName, 'public');
        }
        // Update project
        $project->update($validated);

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'تم تحديث المشروع بنجاح.');
    }

    /**
     * Remove the specified project from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        // Delete image if exists
        if ($project->image && Storage::disk('public')->exists($project->image)) {
            Storage::disk('public')->delete($project->image);
        }

        // Delete PDF if exists
        if ($project->pdf && Storage::disk('public')->exists($project->pdf)) {
            Storage::disk('public')->delete($project->pdf);
        }

        // Delete project
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'تم حذف المشروع بنجاح.');
    }

    /**
     * Download project PDF.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**public function downloadPdf($id)
    {
        $project = Project::findOrFail($id);

        if (!$project->pdf || !File::exists(public_path($project->pdf))) {
            abort(404, 'PDF file not found');
        }

        return response()->download(public_path($project->pdf));
    }**/

    /**
     * Search for projects.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(HttpRequest $request)
    {
        $search = $request->get('search');

        $projects = Project::where('project_name', 'like', "%{$search}%")
            ->orWhere('department', 'like', "%{$search}%")
            ->orWhere('place', 'like', "%{$search}%")
            ->paginate(15);

        // Get unique departments for filter dropdown
        $departments = Project::select('department')->distinct()->pluck('department');

        // Get statuses for filter dropdown
        $statuses = ['available', 'borrowed', 'archived'];

        return view('projects.index', compact('projects', 'departments', 'statuses', 'search'));
    }

    /**
     * Get projects by department.
     *
     * @param  string  $department
     * @return \Illuminate\Http\Response
     */
    public function getByDepartment($department)
    {
        $projects = Project::where('department', $department)
            ->orderBy('project_name')
            ->paginate(15);

        // Get unique departments for filter dropdown
        $departments = Project::select('department')->distinct()->pluck('department');

        // Get statuses for filter dropdown
        $statuses = ['available', 'borrowed', 'archived'];

        return view('projects.index', compact('projects', 'departments', 'statuses', 'department'));
    }

    /**
     * Get projects by status.
     *
     * @param  string  $status
     * @return \Illuminate\Http\Response
     */
    public function getByStatus($status)
    {
        $projects = Project::where('status', $status)
            ->orderBy('project_name')
            ->paginate(15);

        // Get unique departments for filter dropdown
        $departments = Project::select('department')->distinct()->pluck('department');

        // Get statuses for filter dropdown
        $statuses = ['available', 'borrowed', 'archived'];

        return view('projects.index', compact('projects', 'departments', 'statuses', 'status'));
    }
}
