<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'LMS'))</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Quill CSS (LOAD LANGSUNG DI LAYOUT) -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

    <!-- Brand CSS -->
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">

    @stack('head')
    @stack('styles')

    <style>
        :root { --sidebar-w: 260px; }

        html, body { height: 100%; }
        body { background:#f3f4f7; overflow-x: hidden; }

        .app-shell { min-height:100vh; width:100%; }

        /* ✅ KUNCI SIDEBAR WIDTH biar konsisten di semua halaman */
        .sidebar{
            flex: 0 0 var(--sidebar-w);
            width: var(--sidebar-w);
            min-width: var(--sidebar-w);
            max-width: var(--sidebar-w);

            background:#fff;
            border-right:1px solid rgba(0,0,0,.06);
        }

        /* ✅ Penting banget di flex layout: biar konten yg lebar gak "ngepress" sidebar */
        main.flex-grow-1 { min-width: 0; }
        .content-wrap { padding:18px; min-width: 0; }

        .logo { height:28px; width:auto; }

        .sidebar .nav-link{
            border-radius:12px;
            padding:.6rem .75rem;
            color:#374151;
        }
        .sidebar .nav-link:hover{
            background: rgba(91,62,142,.08);
            color: var(--brand-primary);
        }
        .sidebar .nav-link.active{
            background: rgba(91,62,142,.12);
            color: var(--brand-primary);
            font-weight:700;
        }

        .topbar{
            background:#fff;
            border-bottom:1px solid rgba(0,0,0,.06);
        }

        .card{
            border:1px solid rgba(0,0,0,.06);
            border-radius:16px;
            box-shadow:0 12px 30px rgba(16,24,40,.06);
        }

        /* Quill styling biar nyatu sama Bootstrap */
        .quill-wrap .ql-toolbar{
            border-radius: .5rem .5rem 0 0;
            border-color: rgba(0,0,0,.12);
            background: #fff;
        }
        .quill-wrap .ql-container{
            border-radius: 0 0 .5rem .5rem;
            border-color: rgba(0,0,0,.12);
            background: #fff;
            font-size: .9rem;
        }
        .quill-editor{ min-height: 120px; }
        .ql-editor{ padding: .75rem; }
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
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>

                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                   href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people me-2"></i> Users
                </a>

                <a class="nav-link {{ request()->routeIs('admin.instructors.*') ? 'active' : '' }}"
                   href="{{ route('admin.instructors.index') }}">
                    <i class="bi bi-person-badge me-2"></i> Instructors
                </a>

                <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}"
                   href="{{ route('admin.courses.index') }}">
                    <i class="bi bi-journal-bookmark me-2"></i> Courses
                </a>
                <a class="nav-link {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}"
                href="{{ route('admin.leads.index') }}">
                    <i class="bi bi-person-lines-fill me-2"></i> Potential Students
                </a>

            @endif

            {{-- ================= INSTRUCTOR ================= --}}
            @if(auth()->user()->role === 'instructor')
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>

                <a class="nav-link {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}"
                   href="{{ route('instructor.courses.index') }}">
                    <i class="bi bi-journal-text me-2"></i> My Courses
                </a>

                <a class="nav-link {{ request()->is('instructor/assignments*') ? 'active' : '' }}"
                   href="#">
                    <i class="bi bi-clipboard-check me-2"></i> Assignments
                </a>

                <a class="nav-link {{ request()->is('instructor/submissions*') ? 'active' : '' }}"
                   href="#">
                    <i class="bi bi-inbox me-2"></i> Submissions
                </a>

                <a class="nav-link {{ request()->is('instructor/attendance*') ? 'active' : '' }}"
                   href="#">
                    <i class="bi bi-calendar-check me-2"></i> Attendance
                </a>
            @endif

            {{-- ================= STUDENT ================= --}}
            @if(auth()->user()->role === 'student')
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>

                <a class="nav-link {{ request()->is('student/courses*') ? 'active' : '' }}"
                   href="#">
                    <i class="bi bi-journal-bookmark-fill me-2"></i> My Courses
                </a>

                <a class="nav-link {{ request()->is('student/progress*') ? 'active' : '' }}"
                   href="#">
                    <i class="bi bi-bar-chart-line me-2"></i> Learning Progress
                </a>

                <a class="nav-link {{ request()->is('student/assignments*') ? 'active' : '' }}"
                   href="#">
                    <i class="bi bi-pencil-square me-2"></i> Assignments
                </a>

                <a class="nav-link {{ request()->is('student/attendance*') ? 'active' : '' }}"
                   href="#">
                    <i class="bi bi-calendar-week me-2"></i> Attendance
                </a>

                <a class="nav-link {{ request()->is('student/certificates*') ? 'active' : '' }}"
                   href="#">
                    <i class="bi bi-award me-2"></i> Certificates
                </a>
            @endif
        </nav>

        <!-- LOGOUT -->
        <div class="pt-3 border-top">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary w-100">Logout</button>
            </form>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-grow-1">
        <div class="topbar px-3 py-3 d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                Hi, <span class="fw-semibold">{{ auth()->user()->name }}</span>
            </div>
        </div>

        <div class="content-wrap">
            {{ $slot }}
        </div>
    </main>
</div>

@stack('before-bootstrap')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

@stack('scripts')
@stack('body')
</body>
</html>
