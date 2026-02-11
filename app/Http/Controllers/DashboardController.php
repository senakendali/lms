<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Lead; // kalau modul leads lo ada
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Role based dashboard view
        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        if ($user->role === 'instructor') {
            return $this->instructorDashboard($user);
        }

        // default student
        return $this->studentDashboard($user);
    }

    protected function adminDashboard()
    {
        $now = Carbon::now();
        $from7d = $now->copy()->subDays(7);

        // ====== COUNTS ======
        $totalStudents = User::where('role', 'student')->count();
        $totalInstructors = User::where('role', 'instructor')->count();
        $totalCourses = Course::count();

        // kalau belum ada modul certificate, amanin aja 0 dulu
        // (nanti kalau table certificate udah ada, tinggal ganti query count)
        $certificatesIssued = 0;

        // ====== SNAPSHOT ======
        $activeCourses = Course::where('is_active', 1)->count();
        $newUsers7d = User::where('created_at', '>=', $from7d)->count();

        // progress bar (biar ada “feel”)
        // activeCourses dibanding total courses
        $activeCoursesPct = $totalCourses > 0 ? (int) round(($activeCourses / $totalCourses) * 100) : 0;

        // new users dibanding total user
        $totalUsers = User::count();
        $newUsersPct = $totalUsers > 0 ? (int) round(($newUsers7d / $totalUsers) * 100) : 0;

        // ====== RECENT ACTIVITY (OPS 1: tanpa table activity) ======
        $recentUsers = User::orderByDesc('created_at')
            ->limit(3)
            ->get(['id', 'name', 'role', 'created_at']);

        $recentCourses = Course::orderByDesc('updated_at')
            ->limit(3)
            ->get(['id', 'title', 'updated_at']);

        // “Instructor assigned” = course yg instructor_id nya ada, terbaru diupdate
        $recentAssigned = Course::whereNotNull('instructor_id')
            ->with('instructor:id,name')
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get(['id', 'title', 'instructor_id', 'updated_at']);

        // ====== LEADS (kalau modul leads udah ada) ======
        $leadsTotal = class_exists(\App\Models\Lead::class) ? Lead::count() : 0;
        $leadsNew = class_exists(\App\Models\Lead::class) ? Lead::where('status', 'new')->count() : 0;

        return view('dashboard', [
            'dashboardRole' => 'admin',

            // cards stats
            'stats' => [
                [
                    'label' => 'Total Students',
                    'value' => $totalStudents,
                    'icon'  => 'bi-mortarboard',
                    'hint'  => 'Akun role student',
                ],
                [
                    'label' => 'Total Instructors',
                    'value' => $totalInstructors,
                    'icon'  => 'bi-person-badge',
                    'hint'  => 'Akun role instructor',
                ],
                [
                    'label' => 'Total Courses',
                    'value' => $totalCourses,
                    'icon'  => 'bi-journal-bookmark',
                    'hint'  => 'Total course',
                ],
                [
                    'label' => 'Certificates Issued',
                    'value' => $certificatesIssued,
                    'icon'  => 'bi-award',
                    'hint'  => 'Total sertifikat terbit',
                ],
            ],

            // snapshot
            'activeCourses' => $activeCourses,
            'newUsers7d' => $newUsers7d,
            'activeCoursesPct' => $activeCoursesPct,
            'newUsersPct' => $newUsersPct,

            // recent activity
            'recentUsers' => $recentUsers,
            'recentCourses' => $recentCourses,
            'recentAssigned' => $recentAssigned,

            // leads
            'leadsTotal' => $leadsTotal,
            'leadsNew' => $leadsNew,
        ]);
    }

    protected function instructorDashboard(User $instructor)
    {
        // placeholder dulu, nanti kita isi real sesuai struktur course-material-progress lo
        return view('dashboard', [
            'dashboardRole' => 'instructor',
        ]);
    }

    protected function studentDashboard(User $student)
    {
        // placeholder dulu
        return view('dashboard', [
            'dashboardRole' => 'student',
        ]);
    }
}
