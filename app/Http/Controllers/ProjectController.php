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
            $query->where(function($q) use ($search) {
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
            'department' => 'required|string|max:100',
            'status' => 'required|string|in:available,borrowed,archived',
            'place' => 'nullable|string|max:255',
            'shelf_no' => 'nullable|string|max:50',
            'sum' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/projects'), $imageName);
            $imagePath = 'images/projects/' . $imageName;
        }

        // Create project
        $project = Project::create([
            'project_name' => $validated['project_name'],
            'department' => $validated['department'],
            'status' => $validated['status'],
            'place' => $validated['place'] ?? null,
            'shelf_no' => $validated['shelf_no'] ?? null,
            'sum' => $validated['sum'] ?? null,
            'image' => $imagePath,
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
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($project->image && File::exists(public_path($project->image))) {
                File::delete(public_path($project->image));
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/projects'), $imageName);
            $validated['image'] = 'images/projects/' . $imageName;
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
        if ($project->image && File::exists(public_path($project->image))) {
            File::delete(public_path($project->image));
        }

        // Delete project
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'تم حذف المشروع بنجاح.');
    }

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
