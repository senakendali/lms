<x-app-layout>
  <div class="container-fluid p-0">
    <div class="row g-3">

      {{-- HEADER --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                    style="width:40px;height:40px;background:rgba(91,62,142,.12);color:var(--brand-primary)">
                <i class="bi bi-people"></i>
              </span>
              <div>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Users</h4>
                <div class="text-muted small">Manage admin, instructor, dan student</div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('admin.users.create') }}" class="btn btn-brand btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add User
              </a>
              <a href="{{ route('admin.instructors.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-person-badge me-1"></i> View Instructors
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- FLASH (ONLY IF EXISTS) --}}
      @if(session('status') || $errors->any())
        <div class="col-12">
          @if(session('status'))
            <div class="alert alert-success py-2 small rounded-3 mb-0">
              <i class="bi bi-check-circle me-1"></i> {!! nl2br(e(session('status'))) !!}
            </div>
          @endif

          @if($errors->any())
            <div class="alert alert-danger py-2 small rounded-3 mb-0">
              <i class="bi bi-exclamation-triangle me-1"></i> {{ $errors->first() }}
            </div>
          @endif
        </div>
      @endif

      {{-- FILTERS --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">

              <div class="col-12 col-md-6">
                <label class="form-label small mb-1">Search</label>
                <input type="text"
                       name="q"
                       class="form-control"
                       placeholder="Search name/email..."
                       value="{{ $q }}">
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Role</label>
                <select name="role" class="form-select">
                  <option value="">All Roles</option>
                  <option value="admin" @selected($role==='admin')>admin</option>
                  <option value="instructor" @selected($role==='instructor')>instructor</option>
                  <option value="student" @selected($role==='student')>student</option>
                </select>
              </div>

              <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-outline-secondary w-100" type="submit">
                  <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a class="btn btn-outline-secondary w-100" href="{{ route('admin.users.index') }}">
                  Reset
                </a>
              </div>

            </form>
          </div>
        </div>
      </div>

      {{-- TABLE --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:28%" class="ps-3">Name</th>
                    <th>Email</th>
                    <th style="width:14%">Role</th>
                    <th class="text-end pe-3" style="width: 220px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($users as $u)
                    @php
                      $roleBadge = match($u->role) {
                        'admin' => 'text-bg-dark',
                        'instructor' => 'text-bg-info',
                        'student' => 'text-bg-light',
                        default => 'text-bg-light'
                      };
                    @endphp
                    <tr>
                      <td class="ps-3">
                        <div class="fw-semibold">{{ $u->name }}</div>
                        <div class="small text-muted">ID: {{ $u->id }}</div>
                      </td>
                      <td class="small">{{ $u->email }}</td>
                      <td>
                        <span class="badge rounded-pill {{ $roleBadge }}">
                          {{ $u->role }}
                        </span>
                      </td>
                      <td class="text-end pe-3">
                        <div class="d-inline-flex gap-2">
                          <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil-square"></i>
                          </a>

                          <form action="{{ route('admin.users.destroy', $u) }}" method="POST"
                                onsubmit="return confirm('Yakin hapus user ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center text-muted p-4">
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
      </div>

    </div>
  </div>
</x-app-layout>
