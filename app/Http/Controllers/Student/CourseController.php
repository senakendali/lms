<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TopicProgress;
use App\Models\VideoProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        // ✅ course yang student enroll (pivot course_user)
        $courses = $student->courses()
            ->with('instructor:id,name')
            ->orderByDesc('course_user.created_at')
            ->get();

        // ✅ summary: enrolled_count + avg_progress (berdasarkan TopicProgress done)
        // NOTE: biar ga berat, hitung sekali via query.
        $courseIds = $courses->pluck('id')->filter()->values();
        $avgProgress = 0;

        if ($courseIds->count() > 0) {
            // total topics per course
            $topicCounts = DB::table('topics')
                ->join('modules', 'modules.id', '=', 'topics.module_id')
                ->whereIn('modules.course_id', $courseIds)
                ->select('modules.course_id as course_id', DB::raw('COUNT(topics.id) as total_topics'))
                ->groupBy('modules.course_id')
                ->pluck('total_topics', 'course_id');

            // done topics per course untuk user
            $doneCounts = DB::table('topic_progress')
                ->join('topics', 'topics.id', '=', 'topic_progress.topic_id')
                ->join('modules', 'modules.id', '=', 'topics.module_id')
                ->where('topic_progress.user_id', $student->id)
                ->where('topic_progress.status', 'done')
                ->whereIn('modules.course_id', $courseIds)
                ->select('modules.course_id as course_id', DB::raw('COUNT(topic_progress.id) as done_topics'))
                ->groupBy('modules.course_id')
                ->pluck('done_topics', 'course_id');

            $progressList = [];
            foreach ($courseIds as $cid) {
                $total = (int)($topicCounts[$cid] ?? 0);
                if ($total <= 0) {
                    $progressList[] = 0;
                    continue;
                }
                $done = (int)($doneCounts[$cid] ?? 0);
                $progressList[] = (int) round(($done / $total) * 100);
            }

            $avgProgress = (int) round(collect($progressList)->avg() ?? 0);
        }

        $summary = (object) [
            'enrolled_count'      => $courses->count(),
            'avg_progress'        => $avgProgress,
            'pending_assignments' => 0, // kalau lu mau, nanti kita sambung ke submission table
        ];

        return view('student.courses.index', compact('courses', 'summary'));
    }

    public function show(Request $request, Course $course)
    {
        $user = $request->user();
        $userId = $user->id;

        // ✅ Guard: pastikan student memang enroll course ini
        $isEnrolled = $user->courses()->where('courses.id', $course->id)->exists();
        abort_unless($isEnrolled, 403);

        // ✅ Eager load biar ga N+1
        $course->load([
            'instructor:id,name',
            'modules' => function ($q) {
                $q->orderByRaw("COALESCE(`order`, id) ASC");
            },
            'modules.topics' => function ($q) {
                $q->orderByRaw("COALESCE(`order`, id) ASC");
            },
            'modules.topics.materials' => function ($q) {
                $q->orderByRaw("COALESCE(`order`, id) ASC");
            },
            'modules.topics.assignments',
        ]);

        // ambil semua topic_id di course ini
        $topicIds = $course->modules
            ->flatMap(fn ($m) => $m->topics ?? [])
            ->pluck('id')
            ->filter()
            ->values();

        // topic progress map (keyBy topic_id)
        $topicProgressMap = TopicProgress::where('user_id', $userId)
            ->whereIn('topic_id', $topicIds)
            ->get()
            ->keyBy('topic_id');

        // ambil semua material video id (ini material.id)
        $materialIds = $course->modules
            ->flatMap(fn ($m) => $m->topics ?? [])
            ->flatMap(fn ($t) => $t->materials ?? [])
            ->where('type', 'video')
            ->pluck('id')
            ->filter()
            ->values();

        // ✅ VideoProgress map: keyBy material_id (wajib)
        $videoProgressMap = VideoProgress::where('user_id', $userId)
            ->whereIn('material_id', $materialIds)
            ->get()
            ->keyBy('material_id');

        // hitung course progress sederhana: done topics / total topics
        $totalTopics = (int) $topicIds->count();
        $doneTopics = (int) $topicProgressMap
            ->filter(fn ($p) => ($p->status ?? '') === 'done')
            ->count();

        $progressPct = $totalTopics > 0
            ? (int) round(($doneTopics / $totalTopics) * 100)
            : 0;

        return view('student.courses.show', compact(
            'course',
            'progressPct',
            'topicProgressMap',
            'videoProgressMap'
        ));
    }
}
