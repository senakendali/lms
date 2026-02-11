<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $instructors = User::where('role', 'instructor')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.instructors.index', compact('instructors', 'q'));
    }

    public function create()
    {
        return view('admin.instructors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:120'],
            'email'    => ['required','email','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => 'instructor',
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.instructors.index')
            ->with('status', 'Instructor berhasil dibuat.');
    }

    public function edit(User $instructor)
    {
        abort_if($instructor->role !== 'instructor', 404);

        return view('admin.instructors.edit', compact('instructor'));
    }

    public function update(Request $request, User $instructor)
    {
        abort_if($instructor->role !== 'instructor', 404);

        $validated = $request->validate([
            'name'     => ['required','string','max:120'],
            'email'    => ['required','email', Rule::unique('users','email')->ignore($instructor->id)],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);

        $instructor->name  = $validated['name'];
        $instructor->email = $validated['email'];

        if (!empty($validated['password'])) {
            $instructor->password = Hash::make($validated['password']);
        }

        $instructor->save();

        return redirect()
            ->route('admin.instructors.index')
            ->with('status', 'Instructor berhasil diupdate.');
    }

    public function destroy(User $instructor)
    {
        abort_if($instructor->role !== 'instructor', 404);

        $instructor->delete();

        return redirect()
            ->route('admin.instructors.index')
            ->with('status', 'Instructor berhasil dihapus.');
    }
}
