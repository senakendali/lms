<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'topic_id'      => ['required', 'integer', 'exists:topics,id'],
            'title'         => ['required', 'string', 'max:190'],
            'description'   => ['nullable', 'string'], // bisa HTML
            'max_score'     => ['nullable', 'integer', 'min:1', 'max:1000'],
            'due_at'        => ['nullable', 'date'],
            'is_published'  => ['nullable'], // checkbox
            'order'         => ['nullable', 'integer', 'min:0'],
        ]);

        // default sesuai migration
        $data['max_score'] = $data['max_score'] ?? 100;
        $data['is_published'] = $request->boolean('is_published', false);

        // created_by
        $data['created_by'] = auth()->id();

        // auto order kalau ga dikirim
        if (!array_key_exists('order', $data) || $data['order'] === null) {
            $maxOrder = Assignment::where('topic_id', $data['topic_id'])->max('order');
            $data['order'] = is_null($maxOrder) ? 0 : ($maxOrder + 1);
        }

        Assignment::create($data);

        return back()->with('status', 'Assignment berhasil ditambahkan.');
    }

    public function update(Request $request, Assignment $assignment)
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:190'],
            'description'   => ['nullable', 'string'],
            'max_score'     => ['nullable', 'integer', 'min:1', 'max:1000'],
            'due_at'        => ['nullable', 'date'],
            'is_published'  => ['nullable'], // checkbox
            'order'         => ['nullable', 'integer', 'min:0'],
        ]);

        // checkbox safe
        $data['is_published'] = $request->boolean('is_published', $assignment->is_published);

        // kalau max_score kosong, jangan jadi null
        if (!array_key_exists('max_score', $data) || $data['max_score'] === null) {
            $data['max_score'] = $assignment->max_score ?? 100;
        }

        $assignment->update($data);

        return back()->with('status', 'Assignment berhasil diupdate.');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        return back()->with('status', 'Assignment berhasil dihapus.');
    }
}
