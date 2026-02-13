<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\DashboardController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LeadController;

use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\MaterialController;
use App\Http\Controllers\Instructor\AssignmentController;

// ✅ student controllers
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\ProgressController as StudentProgressController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\CertificateController as StudentCertificateController;

use App\Http\Controllers\Student\ProgressController;




Route::get('/', function () {
    return view('welcome');
});

// ✅ Default dashboard (boleh tetap, atau nanti kita redirect sesuai role)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ✅ Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * =========================
 * ADMIN
 * =========================
 */
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('instructors', InstructorController::class)->except(['show']);

        Route::resource('courses', CourseController::class)->except(['show']);

        // ✅ Assign Students (separate page from edit course)
        Route::get('courses/{course}/students', [CourseController::class, 'editStudents'])
            ->name('courses.students.edit');

        Route::put('courses/{course}/students', [CourseController::class, 'updateStudents'])
            ->name('courses.students.update');

        // Leads
        Route::resource('leads', LeadController::class)->except(['show']);
        Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    });

/**
 * =========================
 * INSTRUCTOR
 * =========================
 */
Route::middleware(['auth', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {

        // Courses
        Route::get('courses', [InstructorCourseController::class, 'index'])
            ->name('courses.index');

        Route::get('courses/{course}/materials', [MaterialController::class, 'index'])
            ->name('courses.materials');

        // Modules / Topics / Materials (custom actions)
        Route::post('modules', [MaterialController::class, 'storeModule'])->name('modules.store');
        Route::post('topics', [MaterialController::class, 'storeTopic'])->name('topics.store');
        Route::post('materials', [MaterialController::class, 'storeMaterial'])->name('materials.store');

        Route::put('modules/{module}', [MaterialController::class, 'updateModule'])->name('modules.update');
        Route::put('topics/{topic}', [MaterialController::class, 'updateTopic'])->name('topics.update');
        Route::put('materials/{material}', [MaterialController::class, 'updateMaterial'])->name('materials.update');

        Route::delete('modules/{module}', [MaterialController::class, 'destroyModule'])->name('modules.destroy');
        Route::delete('topics/{topic}', [MaterialController::class, 'destroyTopic'])->name('topics.destroy');
        Route::delete('materials/{material}', [MaterialController::class, 'destroyMaterial'])->name('materials.destroy');

        // ✅ Assignments (topic-based)
        Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::put('assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    });

/**
 * =========================
 * STUDENT
 * =========================
 */
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // ✅ nav lu nanti match ini
        Route::get('dashboard', [StudentDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('courses', [StudentCourseController::class, 'index'])
            ->name('courses.index');

        Route::get('courses/{course}', [StudentCourseController::class, 'show'])
            ->name('courses.show');

        // progress
        Route::get('progress', [StudentProgressController::class, 'index'])
            ->name('progress.index');

        // assignments (list tugas dari course yang diikuti)
        Route::get('assignments', [StudentAssignmentController::class, 'index'])
            ->name('assignments.index');

        Route::get('assignments/{assignment}', [StudentAssignmentController::class, 'show'])
            ->name('assignments.show');

        // attendance (optional)
        Route::get('attendance', [StudentAttendanceController::class, 'index'])
            ->name('attendance.index');

        // certificates (optional)
        Route::get('certificates', [StudentCertificateController::class, 'index'])
            ->name('certificates.index');
    });

Route::prefix('student')->name('student.')->middleware(['auth'])->group(function () {
  Route::post('/topics/{topic}/mark', [ProgressController::class, 'markTopic'])
    ->name('topics.mark');

  Route::post('/videos/{material}/progress', [ProgressController::class, 'saveVideoProgress'])
    ->name('videos.progress');
});

require __DIR__ . '/auth.php';
