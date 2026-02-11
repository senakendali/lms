<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'LMS') }}</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Brand CSS -->
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">

    <style>
        body { background:#f3f4f7; }
        .app-shell { min-height:100vh; }
        .sidebar {
            width:260px;
            background:#fff;
            border-right:1px solid rgba(0,0,0,.06);
        }
        .logo { height:28px; width:auto; }
        .sidebar .nav-link {
            border-radius:12px;
            padding:.6rem .75rem;
            color:#374151;
        }
        .sidebar .nav-link:hover {
            background: rgba(91,62,142,.08);
            color: var(--brand-primary);
        }
        .sidebar .nav-link.active {
            background: rgba(91,62,142,.12);
            color: var(--brand-primary);
            font-weight:700;
        }
        .topbar {
            background:#fff;
            border-bottom:1px solid rgba(0,0,0,.06);
        }
        .content-wrap { padding:18px; }
        .card {
            border:1px solid rgba(0,0,0,.06);
            border-radius:16px;
            box-shadow:0 12px 30px rgba(16,24,40,.06);
        }
    </style>
</head>

<body>
<div class="app-shell d-flex">

    <!-- SIDEBAR -->
    <aside class="sidebar d-none d-lg-flex flex-column p-3">
        <div class="d-flex align-items-center gap-2 mb-4">
            <img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo">
           
        </div>

        <nav class="nav flex-column gap-1 flex-grow-1">

            {{-- ================= ADMIN ================= --}}
            @if(auth()->user()->role === 'admin')

                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>

                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people me-2"></i>
                    Users
                </a>

                <a class="nav-link {{ request()->routeIs('admin.instructors.*') ? 'active' : '' }}"
                href="{{ route('admin.instructors.index') }}">
                    <i class="bi bi-person-badge me-2"></i>
                    Instructors
                </a>

                <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}"
                href="{{ route('admin.courses.index') }}">
                    <i class="bi bi-journal-bookmark me-2"></i>
                    Courses
                </a>

            @endif


            {{-- ================= INSTRUCTOR ================= --}}
            @if(auth()->user()->role === 'instructor')

                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>

                <a class="nav-link {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}"
                    href="{{ route('instructor.courses.index') }}">
                        <i class="bi bi-journal-text me-2"></i>
                        My Courses
                    </a>


                <a class="nav-link {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}"
                href="{{ route('instructor.courses.index') }}">
                    <i class="bi bi-collection-play me-2"></i>
                    Materials
                </a>


                <a class="nav-link {{ request()->is('instructor/assignments*') ? 'active' : '' }}"
                href="#">
                    <i class="bi bi-clipboard-check me-2"></i>
                    Assignments
                </a>

                <a class="nav-link {{ request()->is('instructor/submissions*') ? 'active' : '' }}"
                href="#">
                    <i class="bi bi-inbox me-2"></i>
                    Submissions
                </a>

                <a class="nav-link {{ request()->is('instructor/attendance*') ? 'active' : '' }}"
                href="#">
                    <i class="bi bi-calendar-check me-2"></i>
                    Attendance
                </a>

            @endif


            {{-- ================= STUDENT ================= --}}
            @if(auth()->user()->role === 'student')

                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>

                <a class="nav-link {{ request()->is('student/courses*') ? 'active' : '' }}"
                href="#">
                    <i class="bi bi-journal-bookmark-fill me-2"></i>
                    My Courses
                </a>

                <a class="nav-link {{ request()->is('student/progress*') ? 'active' : '' }}"
                href="#">
                    <i class="bi bi-bar-chart-line me-2"></i>
                    Learning Progress
                </a>

                <a class="nav-link {{ request()->is('student/assignments*') ? 'active' : '' }}"
                href="#">
                    <i class="bi bi-pencil-square me-2"></i>
                    Assignments
                </a>

                <a class="nav-link {{ request()->is('student/attendance*') ? 'active' : '' }}"
                href="#">
                    <i class="bi bi-calendar-week me-2"></i>
                    Attendance
                </a>

                <a class="nav-link {{ request()->is('student/certificates*') ? 'active' : '' }}"
                href="#">
                    <i class="bi bi-award me-2"></i>
                    Certificates
                </a>

            @endif
        </nav>


        <!-- LOGOUT -->
        <div class="pt-3 border-top">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary w-100">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-grow-1">
        <div class="topbar px-3 py-3 d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Dashboard</div>
            <div class="small text-muted">
                Hi, <span class="fw-semibold">{{ auth()->user()->name }}</span>
            </div>
        </div>

        <div class="content-wrap">
            {{ $slot }}
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
