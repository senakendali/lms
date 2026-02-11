<x-app-layout>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1 fw-bold" style="color:var(--brand-primary)">Courses</h4>
            <div class="text-muted small">Manage course & assign instructor</div>
        </div>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-brand">+ Add Course</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 small rounded-3">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md-5">
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Search title...">
                </div>

                <div class="col-12 col-md-4">
                    <select class="form-select" name="instructor_id">
                        <option value="">All instructors</option>
                        @foreach($instructors as $ins)
                            <option value="{{ $ins->id }}" @selected((string)$instructorId === (string)$ins->id)>
                                {{ $ins->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 d-flex gap-2">
                    <select class="form-select" name="active">
                        <option value="">All status</option>
                        <option value="1" @selected((string)$active==='1')>Active</option>
                        <option value="0" @selected((string)$active==='0')>Inactive</option>
                    </select>
                    <button class="btn btn-outline-secondary" type="submit">Go</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.courses.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Title</th>
                    <th>Instructor</th>
                    <th>Status</th>
                    <th class="text-end pe-3" style="width:220px;">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($courses as $c)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold">{{ $c->title }}</div>
                            <div class="small text-muted">ID: {{ $c->id }}</div>
                        </td>
                        <td>
                            @if($c->instructor)
                                <div class="fw-semibold">{{ $c->instructor->name }}</div>
                                <div class="small text-muted">{{ $c->instructor->email }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($c->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a class="btn btn-sm btn-outline-secondary"
                               href="{{ route('admin.courses.edit', $c) }}">Edit</a>

                            <form method="POST"
                                  action="{{ route('admin.courses.destroy', $c) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Hapus course ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada course.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $courses->links() }}
        </div>
    </div>
</x-app-layout>
