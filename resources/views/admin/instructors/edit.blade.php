<x-app-layout>
    <h4 class="fw-bold mb-3" style="color:var(--brand-primary)">
        Edit Instructor
    </h4>

    @if($errors->any())
        <div class="alert alert-danger small rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-4">
            <form method="POST"
                  action="{{ route('admin.instructors.update', $instructor) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name"
                           class="form-control"
                           value="{{ $instructor->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input name="email"
                           type="email"
                           class="form-control"
                           value="{{ $instructor->email }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password (optional)</label>
                    <input name="password" type="password" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input name="password_confirmation" type="password" class="form-control">
                </div>

                <button class="btn btn-brand">Update</button>
                <a href="{{ route('admin.instructors.index') }}"
                   class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>
