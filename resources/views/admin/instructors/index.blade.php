<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold" style="color:var(--brand-primary)">Instructors</h4>
            <div class="text-muted small">Manage instructor accounts</div>
        </div>
        <a href="{{ route('admin.instructors.create') }}" class="btn btn-brand">
            + Add Instructor
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success py-2 small rounded-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="q" value="{{ $q }}"
                           class="form-control"
                           placeholder="Search name or email">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-outline-secondary w-100">Search</button>
                    <a href="{{ route('admin.instructors.index') }}"
                       class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Name</th>
                    <th>Email</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($instructors as $i)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold">{{ $i->name }}</div>
                            <div class="small text-muted">ID: {{ $i->id }}</div>
                        </td>
                        <td>{{ $i->email }}</td>
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.instructors.edit', $i) }}"
                               class="btn btn-sm btn-outline-secondary">Edit</a>

                            <form method="POST"
                                  action="{{ route('admin.instructors.destroy', $i) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Hapus instructor ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            Belum ada instructor.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $instructors->links() }}
        </div>
    </div>
</x-app-layout>
