<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Topic;
use App\Models\TopicProgress;
use App\Models\VideoProgress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
  // buat LIVE / manual completion
  public function markTopic(Request $request, Topic $topic)
  {
    $userId = $request->user()->id;

    // pastiin topic punya course_id via relasi module->course
    $courseId = $topic->module?->course_id;

    abort_unless($courseId, 404);

    $action = $request->input('action', 'done'); // done | reset | start

    $row = TopicProgress::firstOrCreate(
      ['user_id' => $userId, 'topic_id' => $topic->id],
      ['course_id' => $courseId, 'status' => 'not_started']
    );

    if ($action === 'reset') {
      $row->update([
        'status' => 'not_started',
        'started_at' => null,
        'completed_at' => null,
      ]);
    } elseif ($action === 'start') {
      $row->update([
        'status' => 'in_progress',
        'started_at' => $row->started_at ?? now(),
        'completed_at' => null,
      ]);
    } else {
      $row->update([
        'status' => 'done',
        'started_at' => $row->started_at ?? now(),
        'completed_at' => now(),
      ]);
    }

    return back()->with('status', 'Progress topic updated.');
  }

  // save video progress (nanti lu panggil dari UI / JS kalau udah siap)
  public function saveVideoProgress(Request $request, Material $material)
  {
    $userId = $request->user()->id;

    abort_unless(($material->type ?? null) === 'video', 404);

    $topicId = $material->topic_id;
    $courseId = $material->topic?->module?->course_id;

    abort_unless($topicId && $courseId, 404);

    $data = $request->validate([
      'watched_seconds' => ['nullable','integer','min:0'],
      'duration_seconds' => ['nullable','integer','min:0'],
      'progress_pct' => ['nullable','integer','min:0','max:100'],
    ]);

    $vp = VideoProgress::updateOrCreate(
      ['user_id' => $userId, 'material_id' => $material->id],
      [
        'course_id' => $courseId,
        'topic_id' => $topicId,
        'watched_seconds' => (int)($data['watched_seconds'] ?? 0),
        'duration_seconds' => (int)($data['duration_seconds'] ?? 0),
        'progress_pct' => (int)($data['progress_pct'] ?? 0),
        'last_watched_at' => now(),
      ]
    );

    // ✅ auto update topic_progress berdasar video %
    // threshold: >= 90% dianggap done
    $pct = (int)($vp->progress_pct ?? 0);

    $tp = TopicProgress::firstOrCreate(
      ['user_id' => $userId, 'topic_id' => $topicId],
      ['course_id' => $courseId, 'status' => 'not_started']
    );

    if ($pct > 0 && $tp->status === 'not_started') {
      $tp->update(['status' => 'in_progress', 'started_at' => $tp->started_at ?? now()]);
    }

    if ($pct >= 90) {
      $tp->update(['status' => 'done', 'completed_at' => now()]);
    }

    return response()->json(['ok' => true]);
  }
}
