<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Chat;

class ChatController extends Controller
{
    /**
     * Show the main chat interface for the librarian.
     * This will typically load a Blade view that uses JavaScript to fetch chat data from the API.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $librarian = Auth::guard("web")->user();

        return view('chat.index', compact('librarian'));
    }

    /**
     * Show the chat history for a specific student.
     * This will load a Blade view for a specific chat conversation.
     *
     * @param  int  $student_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($student_id)
    {
        $librarian = Auth::user();
        $student = Student::where('student_id', $student_id)->first();

        if (!$student) {
            return redirect()->route('chat.index')->with('error', 'Student not found.');
        }


        return view('chat.show', compact('librarian', 'student'));
    }

}
