<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Topic;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        abort_if($course->instructor_id !== auth()->id(), 403);

        // Eager load biar blade lu gak N+1 (materials + assignments)
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
                            // kalau relasi assignments ada, biar blade $topic->assignments gak nembak query berkali-kali
                            'assignments' => function ($a) {
                                $a->orderByDesc('id');
                            },
                        ]);
                    },
                ]);
            },
        ]);

        // IMPORTANT: view path sesuai file lu: resources/views/instructor/courses/materials.blade.php
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

        // kalau kolom order ada, baru kita set
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
            'subtopics' => ['nullable', 'string'], // outline html
            'delivery_type' => ['nullable', Rule::in(['video', 'live', 'hybrid'])],
        ]);

        // ✅ kalau update outline doang, delivery_type jangan dipaksa
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
    public function storeMaterial(Request $request)
    {
        // NOTE:
        // - Video: upload single "file" (video/*) -> storage/app/public/videos
        // - File: upload multiple "files[]" (doc/pdf/ppt/xls) -> storage/app/public/materials
        // - Link: url wajib

        $data = $request->validate([
            'topic_id' => ['required', 'exists:topics,id'],
            'title'    => ['nullable', 'string', 'max:160'],
            'type'     => ['required', 'in:video,file,link'],

            // link
            'url'      => ['nullable', 'url', 'max:2000'],

            // file upload (dipakai untuk video single OR file single legacy)
            'file'     => ['nullable', 'file', 'max:204800'], // 200MB

            // files upload (multiple docs)
            'files'    => ['nullable', 'array'],
            'files.*'  => ['file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max:10240'],
        ]);

        $topic = Topic::with('module.course')->findOrFail($data['topic_id']);
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        $nextOrder = function () use ($topic) {
            return (int) (Material::where('topic_id', $topic->id)->max('order') ?? 0) + 1;
        };

        $titleOr = function (string $fallback) use ($data) {
            $t = trim((string) ($data['title'] ?? ''));
            return $t !== '' ? $t : $fallback;
        };

        // ===== VIDEO (LOCAL UPLOAD) =====
        if ($data['type'] === 'video') {
            if (!$request->hasFile('file')) {
                return back()->withErrors(['file' => 'File video wajib diupload.'])->withInput();
            }

            // validasi mime video lebih ketat pas type=video
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'mimetypes:video/mp4,video/quicktime,video/x-matroska,video/webm,video/avi,video/mpeg,video/3gpp,video/3gpp2,video/x-msvideo',
                    'max:204800',
                ],
            ]);

            $path = $request->file('file')->store('videos', 'public');

            Material::create([
                'topic_id'  => $topic->id,
                'title'     => $titleOr('Video'),
                'type'      => 'video',
                'order'     => $nextOrder(),
                'drive_id'  => null,
                'url'       => null,
                'file_path' => $path,
            ]);

            return back()->with('status', 'Video berhasil diupload.');
        }

        // ===== LINK =====
        if ($data['type'] === 'link') {
            if (empty($data['url'])) {
                return back()->withErrors(['url' => 'URL wajib diisi untuk link.'])->withInput();
            }

            Material::create([
                'topic_id'  => $topic->id,
                'title'     => $titleOr('Link'),
                'type'      => 'link',
                'order'     => $nextOrder(),
                'drive_id'  => null,
                'url'       => $data['url'],
                'file_path' => null,
            ]);

            return back()->with('status', 'Link ditambahkan.');
        }

        // ===== FILE (multiple docs) =====
        if ($data['type'] === 'file') {
            $files = [];

            if ($request->hasFile('files')) {
                $files = $request->file('files');
            } elseif ($request->hasFile('file')) {
                // legacy: single file
                $files = [$request->file('file')];
            }

            if (empty($files)) {
                return back()->withErrors(['files' => 'Minimal upload 1 file.'])->withInput();
            }

            foreach ($files as $f) {
                // enforce doc mimes
                $extOk = in_array(strtolower($f->getClientOriginalExtension()), ['pdf','doc','docx','ppt','pptx','xls','xlsx'], true);
                if (!$extOk) {
                    return back()->withErrors(['files' => 'Tipe file harus pdf/doc/docx/ppt/pptx/xls/xlsx.'])->withInput();
                }

                $path = $f->store('materials', 'public');
                $fallbackTitle = pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME);

                Material::create([
                    'topic_id'  => $topic->id,
                    'title'     => $titleOr($fallbackTitle),
                    'type'      => 'file',
                    'order'     => $nextOrder(),
                    'drive_id'  => null,
                    'url'       => null,
                    'file_path' => $path,
                ]);
            }

            return back()->with('status', 'File berhasil diupload.');
        }

        return back()->withErrors(['type' => 'Tipe material tidak valid.'])->withInput();
    }

    public function updateMaterial(Request $request, Material $material)
    {
        $material->load('topic.module.course');
        abort_if($material->topic->module->course->instructor_id !== auth()->id(), 403);

        $data = $request->validate([
            'title'    => ['required', 'string', 'max:160'],
            'type'     => ['required', 'in:video,file,link'],
            'url'      => ['nullable', 'url', 'max:2000'],

            // optional replace file/video
            'file'     => ['nullable', 'file', 'max:204800'],
        ]);

        $payload = [
            'title' => $data['title'],
            'type'  => $data['type'],
            'drive_id' => null, // kita gak pake drive_id lagi
            'url' => null,
        ];

        // ===== VIDEO (LOCAL) =====
        if ($data['type'] === 'video') {
            // kalau upload video baru → replace
            if ($request->hasFile('file')) {
                $request->validate([
                    'file' => [
                        'required',
                        'file',
                        'mimetypes:video/mp4,video/quicktime,video/x-matroska,video/webm,video/avi,video/mpeg,video/3gpp,video/3gpp2,video/x-msvideo',
                        'max:204800',
                    ],
                ]);

                if ($material->file_path) {
                    Storage::disk('public')->delete($material->file_path);
                }

                $payload['file_path'] = $request->file('file')->store('videos', 'public');
            } else {
                // gak replace → keep existing
                $payload['file_path'] = $material->file_path;
            }

            $payload['url'] = null;

            $material->update($payload);
            return back()->with('status', 'Video diupdate.');
        }

        // ===== LINK =====
        if ($data['type'] === 'link') {
            if (empty($data['url'])) {
                return back()->withErrors(['url' => 'URL wajib diisi untuk link.'])->withInput();
            }

            // kalau sebelumnya punya file_path (dari type file/video), bersihin
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }

            $payload['url'] = $data['url'];
            $payload['file_path'] = null;

            $material->update($payload);
            return back()->with('status', 'Link diupdate.');
        }

        // ===== FILE =====
        if ($data['type'] === 'file') {
            // replace file lama kalau upload baru
            if ($request->hasFile('file')) {
                $request->validate([
                    'file' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max:10240'],
                ]);

                if ($material->file_path) {
                    Storage::disk('public')->delete($material->file_path);
                }

                $payload['file_path'] = $request->file('file')->store('materials', 'public');
            } else {
                $payload['file_path'] = $material->file_path;
            }

            $payload['url'] = null;

            $material->update($payload);
            return back()->with('status', 'File diupdate.');
        }

        return back()->withErrors(['type' => 'Tipe material tidak valid.'])->withInput();
    }

    public function destroyMaterial(Material $material)
    {
        $material->load('topic.module.course');
        abort_if($material->topic->module->course->instructor_id !== auth()->id(), 403);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return back()->with('status', 'Material dihapus.');
    }
}
