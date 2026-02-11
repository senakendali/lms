<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('instructor_id', auth()->id())
            ->orderByDesc('id')
            ->get();

        return view('instructor.courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        abort_if($course->instructor_id !== auth()->id(), 403);

        return view('instructor.courses.show', compact('course'));
    }
}
