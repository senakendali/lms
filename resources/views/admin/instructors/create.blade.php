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
                <i class="bi bi-person-plus"></i>
              </span>
              <div>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Add Instructor</h4>
                <div class="text-muted small">Create instructor account</div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('admin.instructors.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- ERRORS (ONLY IF EXISTS) --}}
      @if($errors->any())
        <div class="col-12">
          <div class="alert alert-danger py-2 small rounded-3 mb-0">
            <div class="fw-semibold mb-1">
              <i class="bi bi-exclamation-triangle me-1"></i> Ada yang salah:
            </div>
            <ul class="mb-0 ps-3">
              @foreach($errors->all() as $e)
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
            <form method="POST" action="{{ route('admin.instructors.store') }}">
              @csrf

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Name</label>
                  <input name="name"
                         class="form-control"
                         value="{{ old('name') }}"
                         required>
                </div>

                <div class="col-12">
                  <label class="form-label">Email</label>
                  <input name="email"
                         type="email"
                         class="form-control"
                         value="{{ old('email') }}"
                         required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label">Password</label>
                  <input name="password" type="password" class="form-control" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label">Confirm Password</label>
                  <input name="password_confirmation" type="password" class="form-control" required>
                </div>

                <div class="col-12 d-flex gap-2">
                  <button class="btn btn-brand" type="submit">
                    <i class="bi bi-save2 me-1"></i> Save
                  </button>
                  <a href="{{ route('admin.instructors.index') }}" class="btn btn-outline-secondary">
                    Cancel
                  </a>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
