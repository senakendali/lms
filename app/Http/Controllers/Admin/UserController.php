<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $role = trim((string) $request->get('role', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($role !== '', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q', 'role'));
    }

    public function create()
    {
        $roles = ['admin', 'instructor', 'student'];
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $roles = ['admin', 'instructor', 'student'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::in($roles)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $roles = ['admin', 'instructor', 'student'];
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $roles = ['admin', 'instructor', 'student'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in($roles)],
            // password optional saat edit
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        // biar admin gak bunuh dirinya sendiri
        if (auth()->id() === $user->id) {
            return back()->with('status', 'Gak bisa delete akun yang sedang login.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User berhasil dihapus.');
    }
}
