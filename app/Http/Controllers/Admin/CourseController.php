<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $instructorId = $request->get('instructor_id', '');
        $active = $request->get('active', '');

        $courses = Course::query()
            ->with('instructor:id,name,email')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%");
            })
            ->when($instructorId !== '', function ($query) use ($instructorId) {
                $query->where('instructor_id', $instructorId);
            })
            ->when($active !== '', function ($query) use ($active) {
                $query->where('is_active', (bool) $active);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $instructors = User::where('role', 'instructor')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.courses.index', compact('courses', 'instructors', 'q', 'instructorId', 'active'));
    }

    public function create()
    {
        $instructors = User::where('role', 'instructor')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.courses.create', compact('instructors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'instructor_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Optional: pastiin yang dipilih beneran instructor
        if (!empty($validated['instructor_id'])) {
            $ok = User::where('id', $validated['instructor_id'])->where('role', 'instructor')->exists();
            if (!$ok) {
                return back()->withErrors(['instructor_id' => 'User yang dipilih bukan instructor.'])->withInput();
            }
        }

        Course::create([
            'title' => $validated['titletitle'] ?? $validated['title'], // safety (typo guard)
            'description' => $validated['description'] ?? null,
            'instructor_id' => $validated['instructor_id'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Course berhasil dibuat.');
    }

    public function edit(Course $course)
    {
        $instructors = User::where('role', 'instructor')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.courses.edit', compact('course', 'instructors'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'instructor_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['instructor_id'])) {
            $ok = User::where('id', $validated['instructor_id'])->where('role', 'instructor')->exists();
            if (!$ok) {
                return back()->withErrors(['instructor_id' => 'User yang dipilih bukan instructor.'])->withInput();
            }
        }

        $course->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'instructor_id' => $validated['instructor_id'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Course berhasil diupdate.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Course berhasil dihapus.');
    }
}
