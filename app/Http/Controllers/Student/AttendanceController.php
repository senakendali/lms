<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('student.attendance.index');
    }
}
