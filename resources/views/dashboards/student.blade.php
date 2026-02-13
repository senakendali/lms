
<div class="container-fluid p-0">
  <div class="row g-3">

    <!-- Header / Welcome -->
    <div class="col-12">
      <div class="card">
        <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                    style="width:40px;height:40px;background:rgba(91,62,142,.12);color:var(--brand-primary)">
                <i class="bi bi-mortarboard-fill"></i>
              </span>
              <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Student Dashboard</h4>
            </div>
            <div class="text-muted">Track your courses, assignments, and learning progress.</div>

            <div class="d-flex flex-wrap gap-2 mt-3">
              <span class="badge rounded-pill text-bg-light">
                <i class="bi bi-journal-bookmark me-1"></i>My Courses: 0
              </span>
              <span class="badge rounded-pill text-bg-light">
                <i class="bi bi-pencil-square me-1"></i>Pending Assignments: 0
              </span>
              <span class="badge rounded-pill text-bg-light">
                <i class="bi bi-bar-chart-line me-1"></i>Progress: 0%
              </span>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.index') }}" class="btn btn-brand">
              <i class="bi bi-journal-text me-1"></i> My Courses
            </a>
            <a href="{{ route('student.assignments.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-pencil-square me-1"></i> Assignments
            </a>
            <a href="{{ route('student.progress.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-bar-chart-line me-1"></i> Progress
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="col-12 col-md-4">
      <div class="card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="text-muted small">Enrolled Courses</div>
              <div class="display-6 fw-bold">0</div>
              <div class="small text-muted mt-1">Courses you joined</div>
            </div>
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center"
                 style="width:44px;height:44px;background:rgba(91,62,142,.10);color:var(--brand-primary)">
              <i class="bi bi-journal-text fs-5"></i>
            </div>
          </div>

          <div class="mt-3">
            <div class="progress" style="height:8px;">
              <div class="progress-bar" role="progressbar" style="width:0%"></div>
            </div>
            <div class="d-flex justify-content-between small text-muted mt-1">
              <span>Completion</span>
              <span>0%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="text-muted small">Completed Assignments</div>
              <div class="display-6 fw-bold">0</div>
              <div class="small text-muted mt-1">Tasks submitted</div>
            </div>
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center"
                 style="width:44px;height:44px;background:rgba(91,62,142,.10);color:var(--brand-primary)">
              <i class="bi bi-check2-square fs-5"></i>
            </div>
          </div>

          <div class="mt-3">
            <div class="progress" style="height:8px;">
              <div class="progress-bar bg-success" role="progressbar" style="width:0%"></div>
            </div>
            <div class="d-flex justify-content-between small text-muted mt-1">
              <span>Submission rate</span>
              <span>0%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="text-muted small">Average Score</div>
              <div class="display-6 fw-bold">0</div>
              <div class="small text-muted mt-1">Across graded assignments</div>
            </div>
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center"
                 style="width:44px;height:44px;background:rgba(91,62,142,.10);color:var(--brand-primary)">
              <i class="bi bi-award fs-5"></i>
            </div>
          </div>

          <div class="mt-3">
            <a href="{{ route('student.assignments.index') }}" class="btn btn-sm btn-outline-secondary w-100">
              <i class="bi bi-list-check me-1"></i> View Assignments
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="col-12 col-lg-8">
      <div class="card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div>
              <div class="fw-semibold">My Courses Overview</div>
              <div class="small text-muted">Courses you are currently enrolled in.</div>
            </div>

            <div class="input-group input-group-sm" style="width:240px; max-width:100%;">
              <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" placeholder="Search course...">
            </div>
          </div>

          <div class="table-responsive mt-3">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Course</th>
                  <th class="text-center">Progress</th>
                  <th class="text-center">Assignments</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="4" class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                    Kamu belum terdaftar di course mana pun.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side -->
    <div class="col-12 col-lg-4">
      <div class="card mb-3">
        <div class="card-body p-4">
          <div class="fw-semibold">Upcoming Deadlines</div>
          <div class="small text-muted">Assignments & schedules</div>

          <div class="mt-3 border rounded-3 p-3 bg-white">
            <div class="d-flex gap-2">
              <div class="text-muted"><i class="bi bi-clock"></i></div>
              <div>
                <div class="fw-semibold">No upcoming deadlines</div>
                <div class="small text-muted">Akan muncul jika ada tugas dengan due date.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-4">
          <div class="fw-semibold">Recent Activity</div>
          <div class="small text-muted">Your latest learning actions</div>

          <div class="mt-3 border rounded-3 p-3 bg-white">
            <div class="d-flex gap-2">
              <div style="color:var(--brand-primary)"><i class="bi bi-dot"></i></div>
              <div>
                <div class="fw-semibold">No activity yet</div>
                <div class="small text-muted">Aktivitas muncul saat kamu mulai belajar.</div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

