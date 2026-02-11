<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $q = Lead::query();

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $leads = $q->latest()->paginate(10)->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'statuses' => Lead::STATUSES,
        ]);
    }

    public function create()
    {
        return view('admin.leads.create', [
            'lead' => new Lead(),
            'statuses' => Lead::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['nullable', 'email', 'max:255', 'unique:leads,email'],
            'phone'  => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:' . implode(',', Lead::STATUSES)],
            'notes'  => ['nullable', 'string'],
        ]);

        $data['status'] = $data['status'] ?? 'new';

        Lead::create($data);

        return redirect()
            ->route('admin.leads.index')
            ->with('status', 'Lead berhasil ditambahkan.');
    }

    public function edit(Lead $lead)
    {
        return view('admin.leads.edit', [
            'lead' => $lead,
            'statuses' => Lead::STATUSES,
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['nullable', 'email', 'max:255', 'unique:leads,email,' . $lead->id],
            'phone'  => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:' . implode(',', Lead::STATUSES)],
            'notes'  => ['nullable', 'string'],
        ]);

        $lead->update($data);

        return redirect()
            ->route('admin.leads.index')
            ->with('status', 'Lead berhasil diupdate.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()
            ->route('admin.leads.index')
            ->with('status', 'Lead berhasil dihapus.');
    }

    /**
     * Convert lead -> user (student)
     */
    public function convert(Lead $lead)
    {
        if ($lead->status === 'converted') {
            return redirect()
                ->route('admin.leads.index')
                ->with('status', 'Lead ini sudah dikonversi.');
        }

        // Minimal data yang dibutuhkan buat akun student: name + email.
        // Kalau email kosong, kita gak bisa create user yang proper.
        if (empty($lead->email)) {
            return redirect()
                ->route('admin.leads.index')
                ->withErrors(['Email wajib diisi untuk convert jadi student.']);
        }

        // Cegah duplikat user
        $exists = User::where('email', $lead->email)->exists();
        if ($exists) {
            // tetap tandain converted biar leadnya "selesai"
            $lead->update(['status' => 'converted']);

            return redirect()
                ->route('admin.leads.index')
                ->with('status', 'User dengan email ini sudah ada. Lead ditandai converted.');
        }

        $plainPassword = Str::random(10);

        User::create([
            'name' => $lead->name,
            'email' => $lead->email,
            'password' => Hash::make($plainPassword),
            'role' => 'student',
        ]);

        $lead->update(['status' => 'converted']);

        return redirect()
            ->route('admin.leads.index')
            ->with('status', "Lead dikonversi jadi student. Password sementara: {$plainPassword}");
    }
}
