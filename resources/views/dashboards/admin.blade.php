@php
  $role = $dashboardRole ?? auth()->user()->role;
@endphp

@if($role === 'admin')
  <div class="container-fluid p-0">
    <div class="row g-3">

      {{-- HEADER --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                    style="width:40px;height:40px;background:rgba(91,62,142,.12);color:var(--brand-primary)">
                <i class="bi bi-speedometer2"></i>
              </span>
              <div>
                <h4 class="fw-bold mb-0" style="color:var(--brand-primary)">Admin Dashboard</h4>
                <div class="text-muted small">Control panel LMS</div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-people me-1"></i> Manage Users
              </a>
              <a href="{{ route('admin.instructors.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-person-badge me-1"></i> Manage Instructors
              </a>
              <a href="{{ route('admin.courses.index') }}" class="btn btn-brand btn-sm">
                <i class="bi bi-journal-bookmark me-1"></i> Manage Courses
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- STATS --}}
      @foreach(($stats ?? []) as $s)
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-muted small">{{ $s['label'] }}</div>
                  <div class="display-6 fw-bold mb-1">{{ $s['value'] }}</div>
                  <div class="small text-muted">{{ $s['hint'] }}</div>
                </div>

                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                      style="width:40px;height:40px;background:rgba(91,62,142,.10);color:var(--brand-primary)">
                  <i class="bi {{ $s['icon'] }}"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      @endforeach

      {{-- SYSTEM SNAPSHOT --}}
      <div class="col-12 col-lg-7">
        <div class="card h-100">
          <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div class="fw-semibold d-flex align-items-center">
                <i class="bi bi-activity me-2" style="color:var(--brand-primary)"></i>
                System Snapshot
              </div>
              <span class="badge rounded-pill text-bg-light border">Live Overview</span>
            </div>

            <div class="row g-3">

              {{-- Active Courses --}}
              <div class="col-12 col-md-6">
                <div class="p-3 rounded-4 border bg-white h-100">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <div class="text-muted small">Active Courses</div>
                      <div class="h3 fw-bold mb-1">{{ $activeCourses ?? 0 }}</div>
                      <div class="small text-muted">Course yang sedang berjalan</div>
                    </div>

                    <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                          style="width:40px;height:40px;background:rgba(91,62,142,.10);color:var(--brand-primary)">
                      <i class="bi bi-lightning-charge"></i>
                    </span>
                  </div>
                </div>
              </div>

              {{-- New Users --}}
              <div class="col-12 col-md-6">
                <div class="p-3 rounded-4 border bg-white h-100">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <div class="text-muted small">New Users (7d)</div>
                      <div class="h3 fw-bold mb-1">{{ $newUsers7d ?? 0 }}</div>
                      <div class="small text-muted">Pendaftaran 7 hari terakhir</div>
                    </div>

                    <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                          style="width:40px;height:40px;background:rgba(91,62,142,.10);color:var(--brand-primary)">
                      <i class="bi bi-person-plus"></i>
                    </span>
                  </div>
                </div>
              </div>

              {{-- Notes --}}
              <div class="col-12">
                <div class="p-3 rounded-4 border bg-white">
                  <div class="fw-semibold d-flex align-items-center mb-2">
                    <i class="bi bi-info-circle me-2" style="color:var(--brand-primary)"></i>
                    System Notes
                  </div>

                  <div class="text-muted small">
                     Ringkasan kondisi umum sistem dan insight cepat untuk membantu monitoring operasional LMS.
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>
      </div>

      {{-- RECENT ACTIVITY --}}
      <div class="col-12 col-lg-5">
        <div class="card h-100">
          <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div class="fw-semibold d-flex align-items-center">
                <i class="bi bi-clock-history me-2" style="color:var(--brand-primary)"></i>
                Recent Activity
              </div>

              <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-people me-1"></i> View Users
              </a>
            </div>

            <div class="text-muted small mb-3">
              Update terbaru dari sistem (tanpa tabel activity).
            </div>

            <div class="d-flex flex-column gap-3">

              {{-- Latest Users --}}
              <div class="p-3 rounded-4 border bg-white">
                <div class="fw-semibold small mb-2 d-flex align-items-center">
                  <i class="bi bi-person-plus me-2" style="color:var(--brand-primary)"></i>
                  Latest Users
                </div>

                @forelse(($recentUsers ?? []) as $u)
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                      <div class="small fw-semibold">{{ $u->name }}</div>
                      <div class="text-muted small">{{ $u->role }} • ID: {{ $u->id }}</div>
                    </div>
                    <span class="badge rounded-pill text-bg-light border">
                      {{ optional($u->created_at)->diffForHumans() }}
                    </span>
                  </div>
                  @if(!$loop->last)<hr class="my-2">@endif
                @empty
                  <div class="text-muted small">Tidak ada data</div>
                @endforelse
              </div>

              {{-- Course Updated --}}
              <div class="p-3 rounded-4 border bg-white">
                <div class="fw-semibold small mb-2 d-flex align-items-center">
                  <i class="bi bi-journal-check me-2" style="color:var(--brand-primary)"></i>
                  Course Updated
                </div>

                @forelse(($recentCourses ?? []) as $c)
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                      <div class="small fw-semibold">{{ $c->title }}</div>
                      <div class="text-muted small">ID: {{ $c->id }}</div>
                    </div>
                    <span class="badge rounded-pill text-bg-light border">
                      {{ optional($c->updated_at)->diffForHumans() }}
                    </span>
                  </div>
                  @if(!$loop->last)<hr class="my-2">@endif
                @empty
                  <div class="text-muted small">Tidak ada data</div>
                @endforelse
              </div>

              {{-- Instructor Assigned --}}
              <div class="p-3 rounded-4 border bg-white">
                <div class="fw-semibold small mb-2 d-flex align-items-center">
                  <i class="bi bi-person-badge me-2" style="color:var(--brand-primary)"></i>
                  Instructor Assigned
                </div>

                @forelse(($recentAssigned ?? []) as $a)
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                      <div class="small fw-semibold">{{ $a->title }}</div>
                      <div class="text-muted small">
                        {{ optional($a->instructor)->name ?: '—' }} • ID: {{ $a->id }}
                      </div>
                    </div>
                    <span class="badge rounded-pill text-bg-light border">
                      {{ optional($a->updated_at)->diffForHumans() }}
                    </span>
                  </div>
                  @if(!$loop->last)<hr class="my-2">@endif
                @empty
                  <div class="text-muted small">Tidak ada data</div>
                @endforelse
              </div>

            </div>

          </div>
        </div>
      </div>

      {{-- POTENTIAL STUDENTS --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <div class="fw-semibold d-flex align-items-center">
                <i class="bi bi-person-lines-fill me-2" style="color:var(--brand-primary)"></i>
                Potential Students
              </div>

              <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.leads.index') }}">
                <i class="bi bi-person-lines-fill me-1"></i> Open Leads
              </a>
            </div>

            <div class="text-muted small">
              Total leads: <b>{{ $leadsTotal ?? 0 }}</b> • New: <b>{{ $leadsNew ?? 0 }}</b>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

@else
  {{-- sementara role lain placeholder dulu --}}
  <div class="card">
    <div class="card-body p-4">
      <div class="fw-semibold">Dashboard</div>
      <div class="text-muted small">Role: {{ $role }}</div>
    </div>
  </div>
@endif
