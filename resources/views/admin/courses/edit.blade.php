<x-app-layout>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1 fw-bold" style="color:var(--brand-primary)">Edit Course</h4>
            <div class="text-muted small">Update course info & instructor</div>
        </div>
        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small rounded-3">
            <div class="fw-semibold mb-1">Ada yang salah:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.courses.update', $course) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input class="form-control" name="title" value="{{ old('title', $course->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description (optional)</label>
                    <textarea class="form-control" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Instructor (optional)</label>
                    <select class="form-select" name="instructor_id">
                        <option value="">— Not assigned —</option>
                        @foreach($instructors as $ins)
                            <option value="{{ $ins->id }}"
                                @selected((string)old('instructor_id', $course->instructor_id) === (string)$ins->id)>
                                {{ $ins->name }} ({{ $ins->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                           @checked((bool) old('is_active', $course->is_active))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button class="btn btn-brand">Update</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.courses.index') }}">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>
