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
              <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Instructor Dashboard</h4>
            </div>
            <div class="text-muted">Manage your materials, students progress, and submissions.</div>

            <div class="d-flex flex-wrap gap-2 mt-3">
              <span class="badge rounded-pill text-bg-light"><i class="bi bi-check2-circle me-1"></i>Today: 0 sessions</span>
              <span class="badge rounded-pill text-bg-light"><i class="bi bi-people me-1"></i>Active students: 0</span>
              <span class="badge rounded-pill text-bg-light"><i class="bi bi-clock-history me-1"></i>Pending reviews: 0</span>
            </div>
          </div>

          <!-- Quick Actions (tanpa create course) -->
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('instructor.courses.index') }}" class="btn btn-brand">
              <i class="bi bi-collection-play me-1"></i> Materials
            </a>
            <a href="#" class="btn btn-outline-secondary">
              <i class="bi bi-clipboard-check me-1"></i> Submissions
            </a>
            <a href="#" class="btn btn-outline-secondary">
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
              <div class="text-muted small">Assigned Courses</div>
              <div class="display-6 fw-bold">0</div>
              <div class="small text-muted mt-1">Courses you teach</div>
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
              <span>Teaching load</span>
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
              <div class="text-muted small">Total Students</div>
              <div class="display-6 fw-bold">0</div>
              <div class="small text-muted mt-1">Across your courses</div>
            </div>
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center"
                 style="width:44px;height:44px;background:rgba(91,62,142,.10);color:var(--brand-primary)">
              <i class="bi bi-people fs-5"></i>
            </div>
          </div>

          <div class="mt-3">
            <div class="d-flex align-items-center justify-content-between small text-muted mb-1">
              <span>Engagement</span><span>0%</span>
            </div>
            <div class="progress" style="height:8px;">
              <div class="progress-bar" role="progressbar" style="width:0%"></div>
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
              <div class="text-muted small">Pending Reviews</div>
              <div class="display-6 fw-bold">0</div>
              <div class="small text-muted mt-1">Assignments to review</div>
            </div>
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center"
                 style="width:44px;height:44px;background:rgba(91,62,142,.10);color:var(--brand-primary)">
              <i class="bi bi-inbox fs-5"></i>
            </div>
          </div>

          <div class="mt-3">
            <a href="#" class="btn btn-sm btn-outline-secondary w-100">
              <i class="bi bi-list-check me-1"></i> View Queue
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
              <div class="small text-muted">Quick snapshot of your assigned courses.</div>
            </div>

            <div class="d-flex gap-2">
              <div class="input-group input-group-sm" style="width:240px; max-width:100%;">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" placeholder="Search course...">
              </div>
            </div>
          </div>

          <div class="table-responsive mt-3">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Course</th>
                  <th class="text-center">Modules</th>
                  <th class="text-center">Students</th>
                  <th class="text-center">Pending</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                    Belum ada course yang di-assign ke instructor.
                  </td>
                </tr>

                <!-- contoh row dummy
                <tr>
                  <td>
                    <div class="fw-semibold">Laravel Basics</div>
                    <div class="small text-muted">Last updated: 2 days ago</div>
                  </td>
                  <td class="text-center">8</td>
                  <td class="text-center">24</td>
                  <td class="text-center"><span class="badge text-bg-warning">3</span></td>
                  <td class="text-end">
                    <a href="#" class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-collection-play me-1"></i> Materials
                    </a>
                  </td>
                </tr>
                -->
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
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">Upcoming</div>
              <div class="small text-muted">Deadlines / sessions</div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button">
              <i class="bi bi-calendar-week"></i>
            </button>
          </div>

          <div class="mt-3">
            <div class="border rounded-3 p-3 bg-white">
              <div class="d-flex align-items-start gap-2">
                <div class="text-muted"><i class="bi bi-clock"></i></div>
                <div class="flex-grow-1">
                  <div class="fw-semibold">No upcoming items</div>
                  <div class="small text-muted">Akan muncul kalau ada schedule/assignment.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">Recent Activity</div>
              <div class="small text-muted">Latest events</div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>

          <div class="mt-3 d-flex flex-column gap-2">
            <div class="border rounded-3 p-3 bg-white">
              <div class="d-flex gap-2">
                <div style="color:var(--brand-primary)"><i class="bi bi-dot"></i></div>
                <div class="flex-grow-1">
                  <div class="fw-semibold">No activity yet</div>
                  <div class="small text-muted">Aktivitas tampil saat ada progress/submission.</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>
