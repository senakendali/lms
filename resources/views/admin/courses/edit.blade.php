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
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Edit Course</h4>
                <div class="text-muted small">Update course info & instructor</div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
              </a>
            </div>

          </div>
        </div>
      </div>

      {{-- ERROR --}}
      @if ($errors->any())
        <div class="col-12">
          <div class="alert alert-danger py-2 small rounded-3 mb-0">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <span class="fw-semibold">Ada yang salah:</span>
            <ul class="mb-0 ps-3 mt-1">
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
            <form method="POST" action="{{ route('admin.courses.update', $course) }}">
              @csrf
              @method('PUT')

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Title</label>
                  <input class="form-control"
                         name="title"
                         value="{{ old('title', $course->title) }}"
                         required>
                </div>

                <div class="col-12">
                  <label class="form-label">Description (optional)</label>
                  <textarea class="form-control"
                            name="description"
                            rows="4">{{ old('description', $course->description) }}</textarea>
                </div>

                <div class="col-12">
                  <label class="form-label">Instructor (optional)</label>
                  <select class="form-select" name="instructor_id">
                    <option value="">— Not assigned —</option>
                    @foreach($instructors as $ins)
                      <option value="{{ $ins->id }}"
                        @selected((string)old('instructor_id', $course->instructor_id) === (string)$ins->id)>
                        {{ $ins->name }} ({{ $ins->email }})
                      </option>
                    @endforeach
                  </select>
                  <div class="small text-muted mt-1">
                    Instructor harus punya role <b>instructor</b>.
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           value="1"
                           id="is_active"
                           @checked((bool) old('is_active', $course->is_active))>
                    <label class="form-check-label" for="is_active">Active</label>
                  </div>
                </div>

                <div class="col-12 d-flex gap-2">
                  <button class="btn btn-brand" type="submit">
                    <i class="bi bi-save2 me-1"></i> Update
                  </button>
                  <a class="btn btn-outline-secondary" href="{{ route('admin.courses.index') }}">
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
