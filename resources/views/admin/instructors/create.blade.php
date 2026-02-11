<x-app-layout>
    <h4 class="fw-bold mb-3" style="color:var(--brand-primary)">
        Add Instructor
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
            <form method="POST" action="{{ route('admin.instructors.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input name="password" type="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input name="password_confirmation" type="password" class="form-control" required>
                </div>

                <button class="btn btn-brand">Save</button>
                <a href="{{ route('admin.instructors.index') }}"
                   class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>
