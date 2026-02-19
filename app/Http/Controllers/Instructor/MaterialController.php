<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Topic;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        abort_if($course->instructor_id !== auth()->id(), 403);

        $course->load([
            'modules' => function ($q) {
                if (Schema::hasColumn('modules', 'order')) {
                    $q->orderBy('order');
                } else {
                    $q->orderBy('id');
                }

                $q->with([
                    'topics' => function ($t) {
                        if (Schema::hasColumn('topics', 'order')) {
                            $t->orderBy('order');
                        } else {
                            $t->orderBy('id');
                        }

                        $t->with([
                            'materials' => function ($m) {
                                if (Schema::hasColumn('materials', 'order')) {
                                    $m->orderBy('order');
                                } else {
                                    $m->orderBy('id');
                                }
                            },
                            'assignments' => function ($a) {
                                $a->orderByDesc('id');
                            },
                        ]);
                    },
                ]);
            },
        ]);

        // ✅ match view lu: resources/views/instructor/courses/materials.blade.php
        return view('instructor.materials.index', compact('course'));
    }

    // ================= MODULE =================
    public function storeModule(Request $request)
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'title'     => ['required', 'string', 'max:160'],
            'learning_objectives' => ['nullable', 'string', 'max:2000'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        abort_if($course->instructor_id !== auth()->id(), 403);

        $payload = [
            'course_id' => $course->id,
            'title'     => $data['title'],
            'learning_objectives' => $data['learning_objectives'] ?? null,
        ];

        if (Schema::hasColumn('modules', 'order')) {
            $nextOrder = (int) (Module::where('course_id', $course->id)->max('order') ?? 0) + 1;
            $payload['order'] = $nextOrder;
        }

        Module::create($payload);

        return back()->with('status', 'Module ditambahkan.');
    }

    public function updateModule(Request $request, Module $module)
    {
        $module->load('course');
        abort_if($module->course->instructor_id !== auth()->id(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'learning_objectives' => ['nullable', 'string', 'max:2000'],
        ]);

        $module->update([
            'title' => $data['title'],
            'learning_objectives' => $data['learning_objectives'] ?? null,
        ]);

        return back()->with('status', 'Module diupdate.');
    }

    public function destroyModule(Module $module)
    {
        $module->load('course');
        abort_if($module->course->instructor_id !== auth()->id(), 403);

        $module->delete();

        return back()->with('status', 'Module dihapus.');
    }

    // ================= TOPIC =================
    public function storeTopic(Request $request)
    {
        $data = $request->validate([
            'module_id' => ['required', 'integer', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'delivery_type' => ['nullable', Rule::in(['video', 'live', 'hybrid'])],
        ]);

        $module = Module::with('course')->findOrFail($data['module_id']);
        abort_if($module->course->instructor_id !== auth()->id(), 403);

        $delivery = $data['delivery_type'] ?? 'video';

        $payload = [
            'module_id' => $data['module_id'],
            'title' => $data['title'],
            'delivery_type' => $delivery,
        ];

        if (Schema::hasColumn('topics', 'order')) {
            $max = Topic::where('module_id', $data['module_id'])->max('order');
            $payload['order'] = is_null($max) ? 1 : ((int) $max + 1);
        }

        Topic::create($payload);

        return back()->with('status', 'Topic berhasil ditambahkan.');
    }

    public function updateTopic(Request $request, Topic $topic)
    {
        $topic->load('module.course');
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subtopics' => ['nullable', 'string'],
            'delivery_type' => ['nullable', Rule::in(['video', 'live', 'hybrid'])],
        ]);

        if (!array_key_exists('delivery_type', $data) || empty($data['delivery_type'])) {
            unset($data['delivery_type']);
        }

        // ✅ OUTLINE FIELD MAPPING: ikut kolom yang ada di DB
        if (array_key_exists('subtopics', $data)) {
            if (Schema::hasColumn('topics', 'subtopics')) {
                // ok
            } elseif (Schema::hasColumn('topics', 'focus_points')) {
                $data['focus_points'] = $data['subtopics'];
                unset($data['subtopics']);
            } elseif (Schema::hasColumn('topics', 'subtopic_points')) {
                $data['subtopic_points'] = $data['subtopics'];
                unset($data['subtopics']);
            } else {
                unset($data['subtopics']);
            }
        }

        $topic->update($data);

        return back()->with('status', 'Topic berhasil diupdate.');
    }

    public function destroyTopic(Topic $topic)
    {
        $topic->load('module.course');
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        $topic->delete();

        return back()->with('status', 'Topic dihapus.');
    }

    // ================= MATERIAL =================

    /**
     * Ambil Google Drive file ID dari:
     * - ID langsung: 1AbC...
     * - URL: https://drive.google.com/file/d/{id}/view
     * - URL: https://drive.google.com/open?id={id}
     * - URL: https://drive.google.com/uc?id={id}&...
     */
    private function extractDriveId(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') return null;

        // kalau cuma ID (tanpa http / tanpa slash)
        if (!str_contains($input, 'http') && !str_contains($input, '/')) {
            return preg_match('/^[a-zA-Z0-9_-]{10,}$/', $input) ? $input : null;
        }

        if (preg_match('#/file/d/([^/]+)#', $input, $m)) {
            return $m[1] ?? null;
        }

        if (preg_match('#[?&]id=([^&]+)#', $input, $m)) {
            return $m[1] ?? null;
        }

        if (preg_match('#/uc\?id=([^&]+)#', $input, $m)) {
            return $m[1] ?? null;
        }

        return null;
    }

    private function drivePreviewUrl(string $driveId): string
    {
        return "https://drive.google.com/file/d/{$driveId}/preview";
    }

    private function normalizeTitle(?string $title, string $fallback): string
    {
        $t = trim((string) $title);
        return $t !== '' ? $t : $fallback;
    }

    /**
     * ✅ Fix: user input "videos/xxx.mp4" (public/storage/videos)
     * jadi URL publik "/storage/videos/xxx.mp4"
     */
    private function normalizePublicVideoUrl(string $raw): string
    {
        $v = trim($raw);

        if ($v === '') return $v;

        // external url
        if (str_starts_with($v, 'http://') || str_starts_with($v, 'https://')) return $v;

        // already correct
        if (str_starts_with($v, '/storage/')) return $v;

        // user types: storage/videos/xxx.mp4
        if (str_starts_with($v, 'storage/')) return '/' . $v;

        // user types: videos/xxx.mp4 -> /storage/videos/xxx.mp4
        if (str_starts_with($v, 'videos/')) return '/storage/' . $v;

        // user types: /videos/xxx.mp4 -> /storage/videos/xxx.mp4
        if (str_starts_with($v, '/videos/')) return '/storage' . $v;

        return $v;
    }

    private function buildMaterialBasePayload(int $topicId, array $data): array
    {
        $payload = [
            'topic_id'  => $topicId,
            'title'     => $data['title'],
            'type'      => $data['type'],
            'drive_id'  => null,
            'url'       => null,
            'file_path' => null,
        ];

        // ✅ only set order if column exists
        if (Schema::hasColumn('materials', 'order')) {
            $payload['order'] = (int) (Material::where('topic_id', $topicId)->max('order') ?? 0) + 1;
        }

        return $payload;
    }

    public function storeMaterial(Request $request)
    {
        // ✅ MATCH UI:
        // video: title + (video_url OR drive_id)
        // file: title + files[]
        // link: title + url
        $data = $request->validate([
            'topic_id'  => ['required', 'exists:topics,id'],
            'title'     => ['nullable', 'string', 'max:160'],
            'type'      => ['required', Rule::in(['video', 'file', 'link'])],

            // video
            'video_url' => ['nullable', 'string', 'max:2000'],
            'drive_id'  => ['nullable', 'string', 'max:2000'],

            // file upload
            'files'     => ['nullable', 'array'],
            'files.*'   => ['file', 'max:51200'], // 50MB

            // link
            'url'       => ['nullable', 'string', 'max:2000'],
        ]);

        $topic = Topic::with('module.course')->findOrFail($data['topic_id']);
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        $type = $data['type'];

        // ===== VIDEO =====
        if ($type === 'video') {
            $videoUrl = trim((string) ($data['video_url'] ?? ''));
            $driveRaw = trim((string) ($data['drive_id'] ?? ''));

            // Drive mode kalau drive_id diisi
            if ($driveRaw !== '') {
                $driveId = $this->extractDriveId($driveRaw);

                if (!$driveId) {
                    return back()->withErrors([
                        'drive_id' => 'Drive link / file id tidak valid.',
                    ])->withInput();
                }

                $payload = $this->buildMaterialBasePayload($topic->id, [
                    'title' => $this->normalizeTitle($data['title'] ?? null, 'Video'),
                    'type'  => 'video',
                ]);

                $payload['drive_id'] = $driveId;
                $payload['url']      = $this->drivePreviewUrl($driveId);

                Material::create($payload);

                return back()->with('status', 'Video (Google Drive) ditambahkan.');
            }

            // Local/URL mode
            if ($videoUrl === '') {
                return back()->withErrors([
                    'video_url' => 'Video URL / path lokal wajib diisi (atau isi Drive).',
                ])->withInput();
            }

            $videoUrl = $this->normalizePublicVideoUrl($videoUrl);

            $payload = $this->buildMaterialBasePayload($topic->id, [
                'title' => $this->normalizeTitle($data['title'] ?? null, 'Video'),
                'type'  => 'video',
            ]);

            $payload['url'] = $videoUrl;

            Material::create($payload);

            return back()->with('status', 'Video (local/url) ditambahkan.');
        }

        // ===== FILE =====
        if ($type === 'file') {
            if (!$request->hasFile('files')) {
                return back()->withErrors(['files' => 'Upload file wajib diisi.'])->withInput();
            }

            $created = 0;

            foreach ((array) $request->file('files') as $uploaded) {
                if (!$uploaded) continue;

                $path = $uploaded->store('materials', 'public');

                $payload = $this->buildMaterialBasePayload($topic->id, [
                    'title' => $this->normalizeTitle($data['title'] ?? null, $uploaded->getClientOriginalName()),
                    'type'  => 'file',
                ]);

                $payload['file_path'] = $path;

                Material::create($payload);

                $created++;
            }

            return back()->with('status', $created > 0
                ? "{$created} file berhasil diupload."
                : "Tidak ada file yang diupload."
            );
        }

        // ===== LINK =====
        if ($type === 'link') {
            $url = trim((string) ($data['url'] ?? ''));

            if ($url === '') {
                return back()->withErrors(['url' => 'URL wajib diisi untuk link.'])->withInput();
            }

            if (!(str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
                return back()->withErrors(['url' => 'URL harus diawali http:// atau https://'])->withInput();
            }

            $payload = $this->buildMaterialBasePayload($topic->id, [
                'title' => $this->normalizeTitle($data['title'] ?? null, 'Link'),
                'type'  => 'link',
            ]);

            $payload['url'] = $url;

            Material::create($payload);

            return back()->with('status', 'Link ditambahkan.');
        }

        return back()->withErrors(['type' => 'Tipe material tidak valid.'])->withInput();
    }

    public function updateMaterial(Request $request, Material $material)
    {
        $material->load('topic.module.course');
        abort_if($material->topic->module->course->instructor_id !== auth()->id(), 403);

        $data = $request->validate([
            'title'     => ['required', 'string', 'max:160'],
            'type'      => ['required', Rule::in(['video', 'file', 'link'])],

            // video
            'video_source' => ['nullable', Rule::in(['local', 'drive'])],
            'video_url'    => ['nullable', 'string', 'max:2000'],
            'drive_id'     => ['nullable', 'string', 'max:2000'],

            // file replace
            'file'      => ['nullable', 'file', 'max:51200'],

            // link
            'url'       => ['nullable', 'string', 'max:2000'],

            // keep file_path (from modal hidden)
            'file_path' => ['nullable', 'string', 'max:2000'],
        ]);

        $payload = [
            'title'     => $data['title'],
            'type'      => $data['type'],
            'drive_id'  => null,
            'url'       => null,
            'file_path' => null,
        ];

        // ===== VIDEO =====
        if ($data['type'] === 'video') {
            $source = $data['video_source'] ?? null;

            // edge: kalau ga ngirim, auto detect
            if (!$source) {
                $source = !empty(trim((string) ($data['drive_id'] ?? ''))) ? 'drive' : 'local';
            }

            if ($source === 'drive') {
                $driveRaw = trim((string) ($data['drive_id'] ?? ''));
                $driveId  = $this->extractDriveId($driveRaw);

                if (!$driveId) {
                    return back()->withErrors(['drive_id' => 'Drive link / file id tidak valid.'])->withInput();
                }

                $payload['drive_id']  = $driveId;
                $payload['url']       = $this->drivePreviewUrl($driveId);
                $payload['file_path'] = null;
            } else {
                $videoUrl = trim((string) ($data['video_url'] ?? ''));

                if ($videoUrl === '') {
                    return back()->withErrors(['video_url' => 'Video URL / path lokal wajib diisi (atau pilih Drive).'])->withInput();
                }

                $payload['url'] = $this->normalizePublicVideoUrl($videoUrl);
            }
        }

        // ===== FILE =====
        if ($data['type'] === 'file') {
            if ($request->hasFile('file')) {
                // delete old file if exists
                if (!empty($material->file_path)) {
                    try { Storage::disk('public')->delete($material->file_path); } catch (\Throwable $e) {}
                }

                $path = $request->file('file')->store('materials', 'public');
                $payload['file_path'] = $path;
            } else {
                // keep existing
                $payload['file_path'] = $data['file_path'] ?? $material->file_path;
            }

            $payload['drive_id'] = null;
            $payload['url'] = null;
        }

        // ===== LINK =====
        if ($data['type'] === 'link') {
            $url = trim((string) ($data['url'] ?? ''));

            if ($url === '') {
                return back()->withErrors(['url' => 'URL wajib diisi untuk link.'])->withInput();
            }

            if (!(str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
                return back()->withErrors(['url' => 'URL harus diawali http:// atau https://'])->withInput();
            }

            $payload['url'] = $url;
        }

        $material->update($payload);

        return back()->with('status', 'Material diupdate.');
    }

    public function destroyMaterial(Material $material)
    {
        $material->load('topic.module.course');
        abort_if($material->topic->module->course->instructor_id !== auth()->id(), 403);

        if ($material->type === 'file' && !empty($material->file_path)) {
            try { Storage::disk('public')->delete($material->file_path); } catch (\Throwable $e) {}
        }

        $material->delete();

        return back()->with('status', 'Material dihapus.');
    }
}
