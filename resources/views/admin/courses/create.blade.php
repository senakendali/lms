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
                <i class="bi bi-journal-plus"></i>
              </span>
              <div>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Add Course</h4>
                <div class="text-muted small">Create course and assign instructor</div>
              </div>
            </div>

            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Back
            </a>
          </div>
        </div>
      </div>

      {{-- ERROR (ONLY IF EXISTS) --}}
      @if ($errors->any())
        <div class="col-12">
          <div class="alert alert-danger py-2 small rounded-3 mb-0">
            <div class="fw-semibold mb-1">
              <i class="bi bi-exclamation-triangle me-1"></i> Ada yang salah:
            </div>
            <ul class="mb-0 ps-3">
              @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      {{-- FORM --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.courses.store') }}">
              @csrf

              <div class="mb-3">
                <label class="form-label">Title</label>
                <input class="form-control"
                       name="title"
                       value="{{ old('title') }}"
                       required>
              </div>

              <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <textarea class="form-control"
                          name="description"
                          rows="4">{{ old('description') }}</textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Instructor (optional)</label>
                <select class="form-select" name="instructor_id">
                  <option value="">— Not assigned —</option>
                  @foreach($instructors as $ins)
                    <option value="{{ $ins->id }}"
                      @selected(old('instructor_id')==(string)$ins->id)>
                      {{ $ins->name }} ({{ $ins->email }})
                    </option>
                  @endforeach
                </select>
                <div class="small text-muted mt-1">
                  Instructor harus punya role <b>instructor</b>.
                </div>
              </div>

              <div class="form-check mb-4">
                <input class="form-check-input"
                       type="checkbox"
                       name="is_active"
                       value="1"
                       id="is_active"
                       {{ old('is_active', 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  Active
                </label>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-brand">
                  <i class="bi bi-check-lg me-1"></i> Save
                </button>
                <a class="btn btn-outline-secondary"
                   href="{{ route('admin.courses.index') }}">
                  Cancel
                </a>
              </div>

            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
