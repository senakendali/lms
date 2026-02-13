<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\TopicProgress;
use App\Models\VideoProgress;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        // ✅ course yang student enroll (pivot course_user)
        $courses = $student->courses() // butuh relasi shortcut di User model (lihat bawah)
            ->with('instructor:id,name')
            ->orderByDesc('course_user.created_at')
            ->get();

        // ✅ summary (progress nanti nyambung ke progress table)
        $summary = (object) [
            'enrolled_count' => $courses->count(),
            'avg_progress' => 0,
            'pending_assignments' => 0,
        ];

        return view('student.courses.index', compact('courses', 'summary'));
    }

    public function show(Request $request, Course $course)
    {
        

        $userId = $request->user()->id;

        // ambil semua topic_id di course ini
        $topicIds = $course->modules
        ->flatMap(fn($m) => $m->topics)
        ->pluck('id')
        ->filter()
        ->values();

        // topic progress map
        $topicProgressMap = TopicProgress::where('user_id', $userId)
        ->whereIn('topic_id', $topicIds)
        ->get()
        ->keyBy('topic_id');

        // video progress map (by material_id)
        $materialIds = $course->modules
        ->flatMap(fn($m) => $m->topics)
        ->flatMap(fn($t) => $t->materials)
        ->where('type','video')
        ->pluck('id')
        ->filter()
        ->values();

        $videoProgressMap = VideoProgress::where('user_id', $userId)
        ->whereIn('material_id', $materialIds)
        ->get()
        ->keyBy('material_id');

        // hitung course progress sederhana:
        // done topics / total topics
        $totalTopics = max(1, $topicIds->count());
        $doneTopics = $topicProgressMap->filter(fn($p) => ($p->status ?? '') === 'done')->count();
        $progressPct = (int) round(($doneTopics / $totalTopics) * 100);

        return view('student.courses.show', compact(
        'course',
        'progressPct',
        'topicProgressMap',
        'videoProgressMap'
        ));

    }
}
