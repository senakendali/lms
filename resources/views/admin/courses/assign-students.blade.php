<x-app-layout>
  <div class="container-fluid p-0">
    <div class="row g-3">

      {{-- HEADER --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                    style="width:40px;height:40px;background:rgba(13,110,253,.12);color:#0d6efd">
                <i class="bi bi-people"></i>
              </span>
              <div>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">
                  Assign Students
                </h4>
                <div class="text-muted small">
                  Course: <span class="fw-semibold">{{ $course->title }}</span>
                </div>
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

      {{-- FORM --}}
      <div class="col-12">
        <form method="POST" action="{{ route('admin.courses.students.update', $course) }}">
          @csrf
          @method('PUT')

          <div class="card">
            <div class="card-body p-4">

              <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                  <div class="fw-semibold mb-1">Students</div>
                  <div class="text-muted small">Pilih student yang ikut course ini (multi select).</div>
                </div>

                <div class="text-end">
                  <div class="small text-muted mb-2">
                    Terpilih: <span id="selectedCount">0</span>
                  </div>
                  <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="btnSelectAll">
                      <i class="bi bi-check2-all me-1"></i> All
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnClearAll">
                      <i class="bi bi-x-lg me-1"></i> Clear
                    </button>
                  </div>
                </div>
              </div>

              

              <select class="form-select mt-3" id="student_ids" name="student_ids[]" multiple size="16">
                @foreach($students as $s)
                  <option value="{{ $s->id }}" @selected(in_array($s->id, old('student_ids', $enrolledStudentIds)))>
                    {{ $s->name }}{{ $s->email ? ' — '.$s->email : '' }}
                  </option>
                @endforeach
              </select>

              <div class="form-text mt-2">
                Windows: tahan <b>Ctrl</b> • Mac: tahan <b>Cmd</b>
              </div>

              <div class="d-flex justify-content-end mt-3 gap-2">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                  Cancel
                </a>
                <button class="btn btn-brand" type="submit">
                  <i class="bi bi-save me-1"></i> Save Assignment
                </button>
              </div>

            </div>
          </div>
        </form>
      </div>

    </div>
  </div>

  <script>
    (function () {
      const select = document.getElementById('student_ids');
      const countEl = document.getElementById('selectedCount');
      const btnAll = document.getElementById('btnSelectAll');
      const btnClear = document.getElementById('btnClearAll');
      if (!select) return;

      function updateCount() {
        const selected = Array.from(select.options).filter(o => o.selected).length;
        if (countEl) countEl.textContent = selected;
      }

      btnAll?.addEventListener('click', () => {
        Array.from(select.options).forEach(o => o.selected = true);
        updateCount();
      });

      btnClear?.addEventListener('click', () => {
        Array.from(select.options).forEach(o => o.selected = false);
        updateCount();
      });

      select.addEventListener('change', updateCount);
      updateCount();
    })();
  </script>
</x-app-layout>
