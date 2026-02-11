<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Topic;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        ]);

        $module->update(['title' => $data['title']]);

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
            'module_id' => ['required','exists:modules,id'],
            'title'     => ['required','string','max:160'],
        ]);

        $module = Module::with('course')->findOrFail($data['module_id']);
        abort_if($module->course->instructor_id !== auth()->id(), 403);

        $nextOrder = (int) (Topic::where('module_id', $module->id)->max('order') ?? 0) + 1;

        Topic::create([
            'module_id' => $module->id,
            'title'     => $data['title'],
            'order'     => $nextOrder,
        ]);

        return back()->with('status', 'Topic ditambahkan.');
    }

    public function updateTopic(Request $request, Topic $topic)
    {
        $topic->load('module.course');
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        $data = $request->validate([
            'title' => ['required','string','max:160'],
        ]);

        $topic->update(['title' => $data['title']]);

        return back()->with('status', 'Topic diupdate.');
    }

    public function destroyTopic(Topic $topic)
    {
        $topic->load('module.course');
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        $topic->delete();
        return back()->with('status', 'Topic dihapus.');
    }

    // ================= MATERIAL (SUBTOPIC) =================
    public function storeMaterial(Request $request)
    {
        $data = $request->validate([
            'topic_id' => ['required','exists:topics,id'],
            'title'    => ['required','string','max:160'],
            'type'     => ['required','in:video,file,link'],
            'drive_id' => ['nullable','string'],
            'url'      => ['nullable','url'],
            'file'     => ['nullable','file','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx','max:10240'], // 10MB
        ]);

        $topic = Topic::with('module.course')->findOrFail($data['topic_id']);
        abort_if($topic->module->course->instructor_id !== auth()->id(), 403);

        $nextOrder = (int) (Material::where('topic_id', $topic->id)->max('order') ?? 0) + 1;

        $payload = [
            'topic_id' => $topic->id,
            'title'    => $data['title'],
            'type'     => $data['type'],
            'order'    => $nextOrder,
            'drive_id' => null,
            'url'      => null,
            'file_path'=> null,
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
            if (!$request->hasFile('file')) {
                return back()->withErrors(['file' => 'File wajib diupload untuk tipe file.'])->withInput();
            }
            $path = $request->file('file')->store('materials', 'public');
            $payload['file_path'] = $path;
        }

        Material::create($payload);

        return back()->with('status', 'Material ditambahkan.');
    }

    public function updateMaterial(Request $request, Material $material)
    {
        $material->load('topic.module.course');
        abort_if($material->topic->module->course->instructor_id !== auth()->id(), 403);

        $data = $request->validate([
            'title'    => ['required','string','max:160'],
            'type'     => ['required','in:video,file,link'],
            'drive_id' => ['nullable','string'],
            'url'      => ['nullable','url'],
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
            // kalau upload file baru, replace file lama
            if ($request->hasFile('file')) {
                if ($material->file_path) {
                    Storage::disk('public')->delete($material->file_path);
                }
                $payload['file_path'] = $request->file('file')->store('materials', 'public');
            } else {
                // tetap pakai file lama kalau belum upload
                $payload['file_path'] = $material->file_path;
            }
        } else {
            // kalau tipe pindah dari file ke non-file, opsional: hapus file lama
            // biar storage ga numpuk:
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
