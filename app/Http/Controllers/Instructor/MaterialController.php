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

        // ✅ FIX: sesuai file UI lu
        // resources/views/instructor/courses/materials.blade.php
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

        $nextOrder = 1;
        if (Schema::hasColumn('modules', 'order')) {
            $nextOrder = (int) (Module::where('course_id', $course->id)->max('order') ?? 0) + 1;
        }

        $payload = [
            'course_id' => $course->id,
            'title'     => $data['title'],
            'learning_objectives' => $data['learning_objectives'] ?? null,
        ];

        if (Schema::hasColumn('modules', 'order')) {
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
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subtopics' => ['nullable', 'string'],
            'delivery_type' => ['nullable', Rule::in(['video', 'live', 'hybrid'])],
        ]);

        if (!array_key_exists('delivery_type', $data) || empty($data['delivery_type'])) {
            unset($data['delivery_type']);
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

        // kalau cuma ID (umumnya panjang 10+ dan tanpa spasi)
        if (!str_contains($input, 'http')) {
            // basic guard
            return preg_match('/^[a-zA-Z0-9_-]{10,}$/', $input) ? $input : null;
        }

        // file/d/{id}/
        if (preg_match('#/file/d/([^/]+)#', $input, $m)) {
            return $m[1] ?? null;
        }

        // ?id={id}
        if (preg_match('#[?&]id=([^&]+)#', $input, $m)) {
            return $m[1] ?? null;
        }

        // uc?id={id}
        if (preg_match('#/uc\?id=([^&]+)#', $input, $m)) {
            return $m[1] ?? null;
        }

        return null;
    }

    private function drivePreviewUrl(string $driveId): string
    {
        return "https://drive.google.com/file/d/{$driveId}/preview";
    }

    private function nextMaterialOrder(int $topicId): int
    {
        if (!Schema::hasColumn('materials', 'order')) return 0;
        return (int) (Material::where('topic_id', $topicId)->max('order') ?? 0) + 1;
    }

    private function normalizeTitle(?string $title, string $fallback): string
    {
        $t = trim((string) $title);
        return $t !== '' ? $t : $fallback;
    }

    public function storeMaterial(Request $request)
    {
        /**
         * Sesuai UI baru:
         * - type=video:
         *    - local => input: video_ref (path / url)
         *    - drive => input: drive_ref (link / id)
         * - type=file:
         *    - upload => input: files[]  (disimpan file_path)
         *    - optional drive => input: drive_ref
         * - type=link:
         *    - url => input: url
         */
        $data = $request->validate([
            'topic_id'  => ['required', 'exists:topics,id'],
            'title'     => ['nullable', 'string', 'max:160'],
            'type'      => ['required', Rule::in(['video', 'file', 'link'])],

            // video
            'video_ref' => ['nullable', 'string', 'max:2000'],
            'drive_ref' => ['nullable', 'string', 'max:2000'],

            // file upload
            'files'     => ['nullable', 'array'],
            'files.*'   => ['file', 'max:51200'], // 50MB per file, adjust kalau mau

            // link
            'url'       => ['nullable', 'string', 'max:2000'],
        ]);

        $topic = Topic::with('module.course')->findOrFail($data['topic_id']);
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        // ===== VIDEO =====
        if ($data['type'] === 'video') {
            $videoRef = trim((string) ($data['video_ref'] ?? ''));
            $driveRef = trim((string) ($data['drive_ref'] ?? ''));

            // kalau drive diisi -> simpan drive
            if ($driveRef !== '') {
                $driveId = $this->extractDriveId($driveRef);

                if (!$driveId) {
                    return back()->withErrors([
                        'drive_ref' => 'Drive link / file id tidak valid.',
                    ])->withInput();
                }

                Material::create([
                    'topic_id'  => $topic->id,
                    'title'     => $this->normalizeTitle($data['title'] ?? null, 'Video'),
                    'type'      => 'video',
                    'order'     => $this->nextMaterialOrder($topic->id),
                    'drive_id'  => $driveId,
                    'url'       => $this->drivePreviewUrl($driveId), // ✅ preview url buat iframe
                    'file_path' => null,
                ]);

                return back()->with('status', 'Video (Google Drive) ditambahkan.');
            }

            // default: local/url
            if ($videoRef === '') {
                return back()->withErrors([
                    'video_ref' => 'Video URL / path lokal wajib diisi (atau isi Drive).',
                ])->withInput();
            }

            // guard ringan: path local boleh tanpa "/" (misal videos/a.mp4)
            // URL harus http(s)
            if (
                !str_starts_with($videoRef, 'http://') &&
                !str_starts_with($videoRef, 'https://') &&
                !str_starts_with($videoRef, '/') &&
                !preg_match('#^[a-zA-Z0-9_\-\/]+\.(mp4|webm|ogg|mov|m4v)$#i', $videoRef)
            ) {
                return back()->withErrors([
                    'video_ref' => 'Format video_ref tidak dikenali. Contoh: videos/intro.mp4 atau /storage/videos/intro.mp4 atau https://...',
                ])->withInput();
            }

            Material::create([
                'topic_id'  => $topic->id,
                'title'     => $this->normalizeTitle($data['title'] ?? null, 'Video'),
                'type'      => 'video',
                'order'     => $this->nextMaterialOrder($topic->id),
                'drive_id'  => null,
                'url'       => $videoRef,
                'file_path' => null,
            ]);

            return back()->with('status', 'Video (local/url) ditambahkan.');
        }

        // ===== FILE =====
        if ($data['type'] === 'file') {
            // 1) kalau upload files[] ada -> create banyak record
            if ($request->hasFile('files')) {
                $created = 0;

                foreach ((array) $request->file('files') as $uploaded) {
                    if (!$uploaded) continue;

                    $path = $uploaded->store('materials', 'public');

                    Material::create([
                        'topic_id'  => $topic->id,
                        'title'     => $this->normalizeTitle($data['title'] ?? null, $uploaded->getClientOriginalName()),
                        'type'      => 'file',
                        'order'     => $this->nextMaterialOrder($topic->id),
                        'drive_id'  => null,
                        'url'       => null,
                        'file_path' => $path,
                    ]);

                    $created++;
                }

                return back()->with('status', $created > 0
                    ? "{$created} file berhasil diupload."
                    : "Tidak ada file yang diupload."
                );
            }

            // 2) optional: drive_ref untuk file (kalau nanti dipakai)
            $driveRef = trim((string) ($data['drive_ref'] ?? ''));
            if ($driveRef !== '') {
                $driveId = $this->extractDriveId($driveRef);

                if (!$driveId) {
                    return back()->withErrors([
                        'drive_ref' => 'Drive link / file id tidak valid untuk file.',
                    ])->withInput();
                }

                Material::create([
                    'topic_id'  => $topic->id,
                    'title'     => $this->normalizeTitle($data['title'] ?? null, 'File'),
                    'type'      => 'file',
                    'order'     => $this->nextMaterialOrder($topic->id),
                    'drive_id'  => $driveId,
                    'url'       => null,
                    'file_path' => null,
                ]);

                return back()->with('status', 'File (Google Drive) ditambahkan.');
            }

            return back()->withErrors([
                'files' => 'Upload file wajib (atau isi Drive untuk file).',
            ])->withInput();
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

            Material::create([
                'topic_id'  => $topic->id,
                'title'     => $this->normalizeTitle($data['title'] ?? null, 'Link'),
                'type'      => 'link',
                'order'     => $this->nextMaterialOrder($topic->id),
                'drive_id'  => null,
                'url'       => $url,
                'file_path' => null,
            ]);

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

            // video (sesuai UI edit modal)
            'video_ref' => ['nullable', 'string', 'max:2000'],
            'drive_ref' => ['nullable', 'string', 'max:2000'],
            'drive_id'  => ['nullable', 'string', 'max:255'], // tetap support kalau UI ngirim ini

            // file replace (edit)
            'file'      => ['nullable', 'file', 'max:51200'],

            // link
            'url'       => ['nullable', 'string', 'max:2000'],

            // keep file_path from hidden
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
            $driveRef = trim((string) ($data['drive_ref'] ?? ''));
            $driveIdIncoming = trim((string) ($data['drive_id'] ?? ''));

            // kalau pilih drive: drive_ref / drive_id harus ada
            if ($driveRef !== '' || $driveIdIncoming !== '') {
                $driveId = $driveIdIncoming ?: $this->extractDriveId($driveRef);

                if (!$driveId) {
                    return back()->withErrors([
                        'drive_ref' => 'Drive link / file id tidak valid.',
                    ])->withInput();
                }

                $payload['drive_id'] = $driveId;
                $payload['url'] = $this->drivePreviewUrl($driveId);
                $payload['file_path'] = null;
            } else {
                // local/url
                $videoRef = trim((string) ($data['video_ref'] ?? ''));

                if ($videoRef === '') {
                    return back()->withErrors([
                        'video_ref' => 'Video URL / path lokal wajib diisi (atau isi Drive).',
                    ])->withInput();
                }

                $payload['url'] = $videoRef;
            }
        }

        // ===== FILE =====
        if ($data['type'] === 'file') {
            // kalau replace file diupload
            if ($request->hasFile('file')) {
                // hapus file lama kalau ada
                if (!empty($material->file_path)) {
                    try { Storage::disk('public')->delete($material->file_path); } catch (\Throwable $e) {}
                }

                $path = $request->file('file')->store('materials', 'public');
                $payload['file_path'] = $path;
            } else {
                // kalau tidak upload, pertahankan file_path lama via hidden atau record existing
                $payload['file_path'] = $data['file_path'] ?? $material->file_path;
            }

            // optional drive_ref untuk file (kalau mau)
            $driveRef = trim((string) ($data['drive_ref'] ?? ''));
            if ($driveRef !== '') {
                $driveId = $this->extractDriveId($driveRef);
                if (!$driveId) {
                    return back()->withErrors(['drive_ref' => 'Drive link / file id tidak valid untuk file.'])->withInput();
                }
                $payload['drive_id'] = $driveId;
            }
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

        // kalau file upload, hapus storage-nya
        if ($material->type === 'file' && !empty($material->file_path)) {
            try { Storage::disk('public')->delete($material->file_path); } catch (\Throwable $e) {}
        }

        $material->delete();

        return back()->with('status', 'Material dihapus.');
    }
}
