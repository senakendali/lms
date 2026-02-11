<x-app-layout>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1 fw-bold" style="color:var(--brand-primary)">Edit User</h4>
            <div class="text-muted small">Update data user (password optional)</div>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
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
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $r)
                                <option value="{{ $r }}" @selected(old('role', $user->role)===$r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">New Password (optional)</label>
                        <input type="password" name="password" class="form-control" placeholder="kosongkan jika tidak diubah">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="confirm password">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-brand" type="submit">Update</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
