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


class MaterialController extends Controller
{
    public function index(Course $course)
    {
        abort_if($course->instructor_id !== auth()->id(), 403);

        $course->load(['modules.topics.materials']);

        return view('instructor.materials.index', compact('course'));
    }

    // ================= MODULE =================
    public function storeModule(Request $request)
    {
        $data = $request->validate([
            'course_id' => ['required','exists:courses,id'],
            'title'     => ['required','string','max:160'],
            'learning_objectives' => ['nullable','string','max:2000'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        abort_if($course->instructor_id !== auth()->id(), 403);

        $nextOrder = (int) (Module::where('course_id', $course->id)->max('order') ?? 0) + 1;

        Module::create([
            'course_id' => $course->id,
            'title'     => $data['title'],
            'learning_objectives' => $data['learning_objectives'] ?? null,
            'order'     => $nextOrder,
        ]);

        return back()->with('status', 'Module ditambahkan.');
    }

    public function updateModule(Request $request, Module $module)
    {
        $module->load('course');
        abort_if($module->course->instructor_id !== auth()->id(), 403);

        $data = $request->validate([
            'title' => ['required','string','max:160'],
            'learning_objectives' => ['nullable','string','max:2000'],
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

        // kalau table topics punya kolom `order`, kita set auto ke last+1
        $order = null;
        $topicTableHasOrder = \Schema::hasColumn('topics', 'order');
        if ($topicTableHasOrder) {
            $max = Topic::where('module_id', $data['module_id'])->max('order');
            $order = is_null($max) ? 1 : ((int)$max + 1);
        }

        Topic::create([
            'module_id' => $data['module_id'],
            'title' => $data['title'],
            'delivery_type' => $delivery,
            'order' => $order,
        ]);

        return back()->with('status', 'Topic berhasil ditambahkan.');
    }

    public function updateTopic(Request $request, Topic $topic)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subtopics' => ['nullable', 'string'], // outline html
            'delivery_type' => ['nullable', Rule::in(['video', 'live', 'hybrid'])],
        ]);

        // ✅ penting: kalau form update outline cuma kirim title+subtopics,
        // delivery_type jangan dipaksa ada (kita keep existing).
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
        // - Video form: title optional? (UI ngisi, tapi boleh kosong)
        // - File upload form: name="files[]" multiple
        // - Link form: title optional
        $data = $request->validate([
            'topic_id' => ['required','exists:topics,id'],
            'title'    => ['nullable','string','max:160'],
            'type'     => ['required','in:video,file,link'],
            'drive_id' => ['nullable','string','max:255'],
            'url'      => ['nullable','url','max:2000'],
            'file'     => ['nullable','file','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx','max:10240'],
            'files'    => ['nullable','array'],
            'files.*'  => ['file','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx','max:10240'],
        ]);

        $topic = Topic::with('module.course')->findOrFail($data['topic_id']);
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        // helper buat ambil order berikutnya per insert
        $nextOrder = function () use ($topic) {
            return (int) (Material::where('topic_id', $topic->id)->max('order') ?? 0) + 1;
        };

        // default title fallback biar ga kosong
        $titleOr = function (string $fallback) use ($data) {
            $t = trim((string)($data['title'] ?? ''));
            return $t !== '' ? $t : $fallback;
        };

        // ===== VIDEO =====
        if ($data['type'] === 'video') {
            if (empty($data['drive_id'])) {
                return back()->withErrors(['drive_id' => 'Drive File ID wajib diisi untuk video.'])->withInput();
            }

            Material::create([
                'topic_id' => $topic->id,
                'title'    => $titleOr('Video'),
                'type'     => 'video',
                'order'    => $nextOrder(),
                'drive_id' => $data['drive_id'],
                'url'      => null,
                'file_path'=> null,
            ]);

            return back()->with('status', 'Video ditambahkan.');
        }

        // ===== LINK =====
        if ($data['type'] === 'link') {
            if (empty($data['url'])) {
                return back()->withErrors(['url' => 'URL wajib diisi untuk link.'])->withInput();
            }

            Material::create([
                'topic_id' => $topic->id,
                'title'    => $titleOr('Link'),
                'type'     => 'link',
                'order'    => $nextOrder(),
                'drive_id' => null,
                'url'      => $data['url'],
                'file_path'=> null,
            ]);

            return back()->with('status', 'Link ditambahkan.');
        }

        // ===== FILE (multiple) =====
        // UI sekarang upload multiple: files[]
        if ($data['type'] === 'file') {
            // support 2 kemungkinan: "files[]" (multiple) atau "file" (single)
            $files = [];

            if ($request->hasFile('files')) {
                $files = $request->file('files');
            } elseif ($request->hasFile('file')) {
                $files = [$request->file('file')];
            }

            if (empty($files)) {
                return back()->withErrors(['files' => 'Minimal upload 1 file.'])->withInput();
            }

            foreach ($files as $f) {
                $path = $f->store('materials', 'public');

                // kalau title kosong, pakai nama file
                $fallbackTitle = pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME);

                Material::create([
                    'topic_id' => $topic->id,
                    'title'    => $titleOr($fallbackTitle),
                    'type'     => 'file',
                    'order'    => $nextOrder(),
                    'drive_id' => null,
                    'url'      => null,
                    'file_path'=> $path,
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
            'title'    => ['required','string','max:160'],
            'type'     => ['required','in:video,file,link'],
            'drive_id' => ['nullable','string','max:255'],
            'url'      => ['nullable','url','max:2000'],
            'file'     => ['nullable','file','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx','max:10240'],
        ]);

        $payload = [
            'title' => $data['title'],
            'type'  => $data['type'],
            'drive_id' => null,
            'url' => null,
        ];

        if ($data['type'] === 'video') {
            if (empty($data['drive_id'])) {
                return back()->withErrors(['drive_id' => 'Drive File ID wajib diisi untuk video.'])->withInput();
            }
            $payload['drive_id'] = $data['drive_id'];
        }

        if ($data['type'] === 'link') {
            if (empty($data['url'])) {
                return back()->withErrors(['url' => 'URL wajib diisi untuk link.'])->withInput();
            }
            $payload['url'] = $data['url'];
        }

        if ($data['type'] === 'file') {
            // replace file lama kalau upload baru
            if ($request->hasFile('file')) {
                if ($material->file_path) {
                    Storage::disk('public')->delete($material->file_path);
                }
                $payload['file_path'] = $request->file('file')->store('materials', 'public');
            } else {
                $payload['file_path'] = $material->file_path;
            }
        } else {
            // kalau pindah dari file ke non-file, hapus file lama biar storage ga numpuk
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $payload['file_path'] = null;
        }

        $material->update($payload);

        return back()->with('status', 'Material diupdate.');
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
