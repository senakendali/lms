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
                <i class="bi bi-person-badge"></i>
              </span>
              <div>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Instructors</h4>
                <div class="text-muted small">Manage instructor accounts</div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('admin.instructors.create') }}" class="btn btn-brand btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Instructor
              </a>
              <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-people me-1"></i> View Users
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
            <form method="GET" class="row g-2 align-items-end" action="{{ route('admin.instructors.index') }}">
              <div class="col-12 col-md-8">
                <label class="form-label small mb-1">Search</label>
                <input type="text"
                       name="q"
                       value="{{ $q }}"
                       class="form-control"
                       placeholder="Search name or email">
              </div>

              <div class="col-12 col-md-4 d-flex gap-2">
                <button class="btn btn-outline-secondary w-100" type="submit">
                  <i class="bi bi-search me-1"></i> Search
                </button>
                <a href="{{ route('admin.instructors.index') }}" class="btn btn-outline-secondary w-100">
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
              <table class="table align-middle mb-0 table-hover">
                <thead class="table-light">
                  <tr>
                    <th class="ps-3" style="width:32%">Name</th>
                    <th>Email</th>
                    <th class="text-end pe-3" style="width:220px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                @forelse($instructors as $i)
                  <tr>
                    <td class="ps-3">
                      <div class="fw-semibold">{{ $i->name }}</div>
                      <div class="small text-muted">ID: {{ $i->id }}</div>
                    </td>
                    <td class="small">{{ $i->email }}</td>
                    <td class="text-end pe-3">
                      <div class="d-inline-flex gap-2">
                        <a href="{{ route('admin.instructors.edit', $i) }}"
                           class="btn btn-sm btn-outline-secondary">
                          <i class="bi bi-pencil-square"></i>
                        </a>

                        <form method="POST"
                              action="{{ route('admin.instructors.destroy', $i) }}"
                              onsubmit="return confirm('Hapus instructor ini?')">
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
                    <td colspan="3" class="text-center text-muted p-4">
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
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
