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
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Add User</h4>
                <div class="text-muted small">Buat akun admin / instructor / student</div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- ERRORS --}}
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
            <form method="POST" action="{{ route('admin.users.store') }}">
              @csrf

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label small mb-1">Full Name</label>
                  <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="col-12">
                  <label class="form-label small mb-1">Email</label>
                  <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="col-12">
                  <label class="form-label small mb-1">Role</label>
                  <select name="role" class="form-select" required>
                    @foreach($roles as $r)
                      <option value="{{ $r }}" @selected(old('role')===$r)>{{ $r }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label small mb-1">Password</label>
                  <input type="password" name="password" class="form-control" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label small mb-1">Confirm Password</label>
                  <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <div class="col-12">
                  <div class="d-flex gap-2">
                    <button class="btn btn-brand" type="submit">
                      <i class="bi bi-save2 me-1"></i> Save
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">
                      Cancel
                    </a>
                  </div>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
