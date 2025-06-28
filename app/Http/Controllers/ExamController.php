<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    /**
     * Display a listing of the exams.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get query parameters for filtering
        $department = $request->query('dept');
        $type = $request->query('type');
        $semester = $request->query('semester');
        $level = $request->query('level');
        $year = $request->query('year');
        $search = $request->query('search');

        // Start with a base query
        $query = Exam::query();

        // Apply filters if provided
        if ($department) {
            $query->byDepartment($department);
        }

        if ($type) {
            $query->byType($type);
        }

        if ($semester) {
            $query->bySemester($semester);
        }

        if ($level) {
            $query->byLevel($level);
        }

        if ($year) {
            $query->byYear($year);
        }

        // Apply search if provided
        if ($search) {
            $query->search($search);
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        // Get paginated results
        $exams = $query->paginate(15);

        // Get unique values for filter dropdowns
        $departments = Exam::select('dept')->distinct()->whereNotNull('dept')->pluck('dept');
        $types = Exam::select('type')->distinct()->whereNotNull('type')->pluck('type');
        $semesters = Exam::select('semester')->distinct()->whereNotNull('semester')->pluck('semester');
        $levels = Exam::select('level')->distinct()->whereNotNull('level')->pluck('level');
        $years = Exam::select('year')->distinct()->whereNotNull('year')->orderBy('year', 'desc')->pluck('year');

        return view('exams.index', compact(
            'exams',
            'departments',
            'types',
            'semesters',
            'levels',
            'years',
            'search',
            'department',
            'type',
            'semester',
            'level',
            'year'
        ));
    }

    /**
     * Show the form for creating a new exam.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get unique values for dropdowns
        $departments = Exam::select('dept')->distinct()->whereNotNull('dept')->pluck('dept');
        $types = ['midterm', 'final', 'quiz', 'assignment'];
        $semesters = ['first', 'second', 'summer'];
        $levels = ['1', '2', '3', '4'];
        $currentYear = date('Y');
        $years = range($currentYear - 5, $currentYear + 1);

        return view('exams.create', compact('departments', 'types', 'semesters', 'levels', 'years'));
    }

    /**
     * Store a newly created exam in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'course_name' => 'required|string|max:255',
            'dept' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'doctor' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'pdf' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Handle PDF upload
        if ($request->hasFile('pdf')) {
            $pdfFile = $request->file('pdf');
            $pdfName = time() . '_' . $pdfFile->getClientOriginalName();
            $pdfPath = $pdfFile->storeAs('exams', $pdfName, 'public');
            $validated['pdf'] = $pdfName;
        }

        // Create new exam
        $exam = Exam::create($validated);

        return redirect()->route('exams.show', $exam->exam_id)
            ->with('success', 'تم إضافة الامتحان بنجاح');
    }

    /**
     * Display the specified exam.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $exam = Exam::findOrFail($id);

        return view('exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified exam.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $exam = Exam::findOrFail($id);

        // Get unique values for dropdowns
        $departments = Exam::select('dept')->distinct()->whereNotNull('dept')->pluck('dept');
        $types = ['midterm', 'final', 'quiz', 'assignment'];
        $semesters = ['first', 'second', 'summer'];
        $levels = ['1', '2', '3', '4'];
        $currentYear = date('Y');
        $years = range($currentYear - 5, $currentYear + 1);

        return view('exams.edit', compact('exam', 'departments', 'types', 'semesters', 'levels', 'years'));
    }

    /**
     * Update the specified exam in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        // Validate request
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'course_name' => 'required|string|max:255',
            'dept' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'doctor' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'pdf' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Handle PDF upload
        if ($request->hasFile('pdf')) {
            // Delete old PDF if exists
            if ($exam->pdf && Storage::disk('public')->exists('exams/' . $exam->pdf)) {
                Storage::disk('public')->delete('exams/' . $exam->pdf);
            }

            $pdfFile = $request->file('pdf');
            $pdfName = time() . '_' . $pdfFile->getClientOriginalName();
            $pdfPath = $pdfFile->storeAs('exams', $pdfName, 'public');
            $validated['pdf'] = $pdfName;
        }

        // Update exam
        $exam->update($validated);

        return redirect()->route('exams.show', $exam->exam_id)
            ->with('success', 'تم تحديث الامتحان بنجاح');
    }

    /**
     * Remove the specified exam from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);

        // Delete PDF file if exists
        if ($exam->pdf && Storage::disk('public')->exists('exams/' . $exam->pdf)) {
            Storage::disk('public')->delete('exams/' . $exam->pdf);
        }

        // Delete exam
        $exam->delete();

        return redirect()->route('exams.index')
            ->with('success', 'تم حذف الامتحان بنجاح');
    }

    /**
     * Search for exams.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $search = $request->input('search');

        $exams = Exam::search($search)->orderBy('created_at', 'desc')->paginate(15);

        // Get unique values for filter dropdowns
        $departments = Exam::select('dept')->distinct()->whereNotNull('dept')->pluck('dept');
        $types = Exam::select('type')->distinct()->whereNotNull('type')->pluck('type');
        $semesters = Exam::select('semester')->distinct()->whereNotNull('semester')->pluck('semester');
        $levels = Exam::select('level')->distinct()->whereNotNull('level')->pluck('level');
        $years = Exam::select('year')->distinct()->whereNotNull('year')->orderBy('year', 'desc')->pluck('year');

        return view('exams.index', compact(
            'exams',
            'departments',
            'types',
            'semesters',
            'levels',
            'years',
            'search'
        ));
    }

    /**
     * Get exams by department.
     *
     * @param  string  $department
     * @return \Illuminate\Http\Response
     */
    public function getByDepartment($department)
    {
        $exams = Exam::byDepartment($department)->orderBy('created_at', 'desc')->paginate(15);

        // Get unique values for filter dropdowns
        $departments = Exam::select('dept')->distinct()->whereNotNull('dept')->pluck('dept');
        $types = Exam::select('type')->distinct()->whereNotNull('type')->pluck('type');
        $semesters = Exam::select('semester')->distinct()->whereNotNull('semester')->pluck('semester');
        $levels = Exam::select('level')->distinct()->whereNotNull('level')->pluck('level');
        $years = Exam::select('year')->distinct()->whereNotNull('year')->orderBy('year', 'desc')->pluck('year');

        return view('exams.index', compact(
            'exams',
            'departments',
            'types',
            'semesters',
            'levels',
            'years',
            'department'
        ));
    }

    /**
     * Get exams by type.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    public function getByType($type)
    {
        $exams = Exam::byType($type)->orderBy('created_at', 'desc')->paginate(15);

        // Get unique values for filter dropdowns
        $departments = Exam::select('dept')->distinct()->whereNotNull('dept')->pluck('dept');
        $types = Exam::select('type')->distinct()->whereNotNull('type')->pluck('type');
        $semesters = Exam::select('semester')->distinct()->whereNotNull('semester')->pluck('semester');
        $levels = Exam::select('level')->distinct()->whereNotNull('level')->pluck('level');
        $years = Exam::select('year')->distinct()->whereNotNull('year')->orderBy('year', 'desc')->pluck('year');

        return view('exams.index', compact(
            'exams',
            'departments',
            'types',
            'semesters',
            'levels',
            'years',
            'type'
        ));
    }

    /**
     * Download exam PDF.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPdf($id)
    {
        $exam = Exam::findOrFail($id);

        if (!$exam->pdf || !Storage::disk('public')->exists('exams/' . $exam->pdf)) {
            abort(404, 'ملف PDF غير موجود');
        }

        $filePath = Storage::disk('public')->path('exams/' . $exam->pdf);
        $fileName = $exam->course_name . '_' . $exam->type . '_' . $exam->year . '.pdf';

        return response()->download($filePath, $fileName);
    }
}

