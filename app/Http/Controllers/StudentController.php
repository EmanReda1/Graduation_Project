<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the students (View Only for Librarian).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get query parameters for filtering
        $level = $request->query('level');
        $department = $request->query('department');
        $search = $request->query('search');

        // Start with a base query - only select public fields
        $query = Student::select([
            'student_id',
            'fullname',
            'level',
            'department',
            'university_code',
            'created_at'
        ]);

        // Apply filters if provided
        if ($level) {
            $query->where('level', $level);
        }

        if ($department) {
            $query->where('department', $department);
        }

        // Apply search if provided (only on public fields)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('university_code', 'like', "%{$search}%");
            });
        }

        // Get paginated results
        $students = $query->paginate(15);

        // Get unique levels and departments for filter dropdowns
        $levels = Student::select('level')->distinct()->pluck('level');
        $departments = Student::select('department')->distinct()->pluck('department');

        return view('students.index', compact('students', 'levels', 'departments', 'search', 'level', 'department'));
    }

    /**
     * Display the specified resource (View Only for Librarian).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find student with only basic information - NO RELATIONSHIPS
        $student = Student::select([
            'student_id',
            'fullname',
            'level',
            'department',
            'university_code',
            'created_at'
        ])
        ->findOrFail($id);

        return view('students.show', compact('student'));
    }

    /**
     * Search for students (public information only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $search = $request->input('search');

        $students = Student::select([
            'student_id',
            'fullname',
            'level',
            'department',
            'university_code'
        ])
        ->where(function($query) use ($search) {
            $query->where('fullname', 'like', "%{$search}%")
                  ->orWhere('university_code', 'like', "%{$search}%");
        })
        ->paginate(15);

        // Get unique levels and departments for filter dropdowns
        $levels = Student::select('level')->distinct()->pluck('level');
        $departments = Student::select('department')->distinct()->pluck('department');

        return view('students.index', compact('students', 'levels', 'departments', 'search'));
    }

    /**
     * Get students by level (public information only).
     *
     * @param  int  $level
     * @return \Illuminate\Http\Response
     */
    public function getByLevel($level)
    {
        $students = Student::select([
            'student_id',
            'fullname',
            'level',
            'department',
            'university_code'
        ])
        ->where('level', $level)
        ->paginate(15);

        // Get unique levels and departments for filter dropdowns
        $levels = Student::select('level')->distinct()->pluck('level');
        $departments = Student::select('department')->distinct()->pluck('department');

        return view('students.index', compact('students', 'levels', 'departments', 'level'));
    }

    /**
     * Get students by department (public information only).
     *
     * @param  string  $department
     * @return \Illuminate\Http\Response
     */
    public function getByDepartment($department)
    {
        $students = Student::select([
            'student_id',
            'fullname',
            'level',
            'department',
            'university_code'
        ])
        ->where('department', $department)
        ->paginate(15);

        // Get unique levels and departments for filter dropdowns
        $levels = Student::select('level')->distinct()->pluck('level');
        $departments = Student::select('department')->distinct()->pluck('department');

        return view('students.index', compact('students', 'levels', 'departments', 'department'));
    }
}

