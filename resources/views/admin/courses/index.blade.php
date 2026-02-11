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
                <i class="bi bi-journal-bookmark"></i>
              </span>
              <div>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Courses</h4>
                <div class="text-muted small">Manage course & assign instructor</div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('admin.courses.create') }}" class="btn btn-brand btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Course
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
            <form method="GET" action="{{ route('admin.courses.index') }}" class="row g-2 align-items-end">

              {{-- Search --}}
              <div class="col-12 col-md-5 col-lg-6">
                <label class="form-label small mb-1">Search</label>
                <input class="form-control" name="q" value="{{ $q }}" placeholder="Search title...">
              </div>

              {{-- Instructor --}}
              <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small mb-1">Instructor</label>
                <select class="form-select" name="instructor_id">
                  <option value="">All instructors</option>
                  @foreach($instructors as $ins)
                    <option value="{{ $ins->id }}" @selected((string)$instructorId === (string)$ins->id)>
                      {{ $ins->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- Status + Buttons --}}
              <div class="col-12 col-md-3 col-lg-3">
                <div class="row g-2">
                  <div class="col-12">
                    <label class="form-label small mb-1">Status</label>
                    <select class="form-select" name="active">
                      <option value="">All status</option>
                      <option value="1" @selected((string)$active === '1')>Active</option>
                      <option value="0" @selected((string)$active === '0')>Inactive</option>
                    </select>
                  </div>

                  <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-secondary w-100" type="submit">
                      <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a class="btn btn-outline-secondary w-100" href="{{ route('admin.courses.index') }}">
                      Reset
                    </a>
                  </div>
                </div>
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
                    <th class="ps-3" style="width:34%">Title</th>
                    <th>Instructor</th>
                    <th style="width:14%">Status</th>
                    <th class="text-end pe-3" style="width:320px;">Actions</th>
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
                        @php
                          $statusBadge = $c->is_active ? 'text-bg-success' : 'text-bg-secondary';
                          $statusLabel = $c->is_active ? 'Active' : 'Inactive';
                        @endphp
                        <span class="badge rounded-pill {{ $statusBadge }}">{{ $statusLabel }}</span>
                      </td>

                      <td class="text-end pe-3">
                        @php
                          // Prefer students_count from withCount(); fallback to loaded relation count if any
                          $studentCount = $c->students_count
                            ?? ($c->relationLoaded('students') ? $c->students->count() : null);
                        @endphp

                        <div class="d-inline-flex gap-2 align-items-center justify-content-end flex-wrap">

                          {{-- Assign Students (separate from edit) --}}
                          <a class="btn btn-sm btn-outline-primary"
                             href="{{ route('admin.courses.students.edit', $c) }}"
                             title="Assign students">
                            <i class="bi bi-people me-1"></i> Assign
                            <span class="badge text-bg-light border ms-1">
                              {{ $studentCount ?? 0 }}
                            </span>
                          </a>

                          {{-- Edit --}}
                          <a class="btn btn-sm btn-outline-secondary"
                             href="{{ route('admin.courses.edit', $c) }}"
                             title="Edit course">
                            <i class="bi bi-pencil-square"></i>
                          </a>

                          {{-- Delete --}}
                          <form method="POST"
                                action="{{ route('admin.courses.destroy', $c) }}"
                                onsubmit="return confirm('Hapus course ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete course">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>

                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center text-muted p-4">
                        Belum ada course.
                      </td>
                    </tr>
                  @endforelse
                </tbody>

              </table>
            </div>

            <div class="p-3">
              {{ $courses->links() }}
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
