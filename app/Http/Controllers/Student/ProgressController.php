<?php
// app/Http/Controllers/Student/ProgressController.php  (FULL PATCH)

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Topic;
use App\Models\TopicProgress;
use App\Models\VideoProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProgressController extends Controller
{
  public function markTopic(Request $request, Topic $topic)
  {
    $userId = $request->user()->id;

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

      return response()->json(['ok' => true, 'message' => 'Progress topic direset.', 'status' => 'not_started']);
    }

    if ($action === 'start') {
      $row->update([
        'status' => 'in_progress',
        'started_at' => $row->started_at ?? now(),
        'completed_at' => null,
      ]);

      return response()->json(['ok' => true, 'message' => 'Topic dimulai.', 'status' => 'in_progress']);
    }

    $row->update([
      'status' => 'done',
      'started_at' => $row->started_at ?? now(),
      'completed_at' => now(),
    ]);

    return response()->json(['ok' => true, 'message' => 'Topic ditandai selesai.', 'status' => 'done']);
  }

  // ✅ GET: buat resume posisi terakhir
  public function getVideoProgress(Request $request, Material $material)
  {
    $userId = $request->user()->id;

    abort_unless(($material->type ?? null) === 'video', 404);

    $topicId  = $material->topic_id;
    $courseId = $material->topic?->module?->course_id;
    abort_unless($topicId && $courseId, 404);

    $vp = VideoProgress::where('user_id', $userId)
      ->where('material_id', $material->id)
      ->first();

    return response()->json([
      'ok' => true,
      'material_id' => $material->id,
      'watched_seconds' => (int)($vp?->watched_seconds ?? 0),
      'duration_seconds' => (int)($vp?->duration_seconds ?? 0),
      'progress_pct' => (int)($vp?->progress_pct ?? 0),
      'last_watched_at' => (string)($vp?->last_watched_at ?? ''),
    ]);
  }

  public function saveVideoProgress(Request $request, Material $material)
  {
    $userId = $request->user()->id;

    abort_unless(($material->type ?? null) === 'video', 404);

    $topicId  = $material->topic_id;
    $courseId = $material->topic?->module?->course_id;
    abort_unless($topicId && $courseId, 404);

    $data = $request->validate([
      'watched_seconds' => ['nullable','integer','min:0'],
      'duration_seconds' => ['nullable','integer','min:0'],
      'progress_pct' => ['nullable','integer','min:0','max:100'],
    ]);

    $watched  = max(0, (int)($data['watched_seconds'] ?? 0));
    $duration = max(0, (int)($data['duration_seconds'] ?? 0));

    // ✅ clamp watched ke duration kalau duration valid
    if ($duration > 0 && $watched > $duration) $watched = $duration;

    $pct = (int)($data['progress_pct'] ?? 0);

    $vp = VideoProgress::updateOrCreate(
      ['user_id' => $userId, 'material_id' => $material->id],
      [
        'course_id' => $courseId,
        'topic_id' => $topicId,
        'watched_seconds' => $watched,
        'duration_seconds' => $duration,
        'progress_pct' => $pct,
        'last_watched_at' => now(),
      ]
    );

    // auto update topic_progress berdasar video %
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

  /**
   * ✅ STREAM VIDEO (RANGE SUPPORT)
   * Ini yang bikin resume/seek ga balik ke 0 untuk video file_path lokal.
   */
  public function streamVideo(Request $request, Material $material)
  {
    abort_unless(($material->type ?? null) === 'video', 404);

    $filePath = (string)($material->file_path ?? '');
    abort_unless($filePath !== '', 404);

    // asumsi file_path disimpan di disk "public"
    $disk = Storage::disk('public');
    abort_unless($disk->exists($filePath), 404);

    $absolutePath = $disk->path($filePath);
    $size = filesize($absolutePath);
    $mime = mime_content_type($absolutePath) ?: 'video/mp4';

    $range = $request->header('Range');

    if (!$range) {
      return response()->file($absolutePath, [
        'Content-Type' => $mime,
        'Accept-Ranges' => 'bytes',
        'Content-Length' => $size,
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
      ]);
    }

    // parse range: bytes=start-end
    if (!preg_match('/bytes=(\d+)-(\d*)/i', $range, $matches)) {
      return response()->file($absolutePath, [
        'Content-Type' => $mime,
        'Accept-Ranges' => 'bytes',
      ]);
    }

    $start = (int)$matches[1];
    $end = $matches[2] !== '' ? (int)$matches[2] : ($size - 1);

    if ($start > $end || $start >= $size) {
      return response('', 416, [
        'Content-Range' => "bytes */{$size}",
      ]);
    }

    $end = min($end, $size - 1);
    $length = ($end - $start) + 1;

    $fh = fopen($absolutePath, 'rb');
    fseek($fh, $start);

    return response()->stream(function () use ($fh, $length) {
      $buffer = 1024 * 1024; // 1MB
      $remaining = $length;

      while ($remaining > 0 && !feof($fh)) {
        $read = ($remaining > $buffer) ? $buffer : $remaining;
        $data = fread($fh, $read);
        if ($data === false) break;

        echo $data;
        $remaining -= strlen($data);

        if (ob_get_level()) ob_flush();
        flush();
      }

      fclose($fh);
    }, 206, [
      'Content-Type' => $mime,
      'Accept-Ranges' => 'bytes',
      'Content-Length' => $length,
      'Content-Range' => "bytes {$start}-{$end}/{$size}",
      'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
      'Pragma' => 'no-cache',
    ]);
  }
}