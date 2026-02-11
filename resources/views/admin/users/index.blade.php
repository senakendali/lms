<x-app-layout>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1 fw-bold" style="color:var(--brand-primary)">Users</h4>
            <div class="text-muted small">Manage admin, instructor, dan student</div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-brand">
            + Add User
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 small rounded-3">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md-6">
                    <input type="text" name="q" class="form-control" placeholder="Search name/email..." value="{{ $q }}">
                </div>
                <div class="col-12 col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" @selected($role==='admin')>admin</option>
                        <option value="instructor" @selected($role==='instructor')>instructor</option>
                        <option value="student" @selected($role==='student')>student</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-secondary w-100" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary w-100" href="{{ route('admin.users.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end pe-3" style="width: 220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">{{ $u->name }}</div>
                                    <div class="small text-muted">ID: {{ $u->id }}</div>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $u->role }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin hapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Belum ada user.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
