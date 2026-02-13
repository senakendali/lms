<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;

class AssignmentController extends Controller
{
    public function index()
    {
        return view('student.assignments.index', [
            'assignments' => collect(),
        ]);
    }

    public function show(Assignment $assignment)
    {
        return view('student.assignments.show', [
            'assignment' => $assignment,
        ]);
    }
}
