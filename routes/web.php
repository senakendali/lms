<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Instructor\MaterialController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\DashboardController;





use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('instructors', InstructorController::class)->except(['show']);
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('courses', CourseController::class)->except(['show']);

        // ✅ Assign Students (separate page from edit course)
        Route::get('courses/{course}/students', [CourseController::class, 'editStudents'])
            ->name('courses.students.edit');

        Route::put('courses/{course}/students', [CourseController::class, 'updateStudents'])
            ->name('courses.students.update');
    });


Route::middleware(['auth','role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {

        // ✅ INI YANG WAJIB ADA
        Route::get('courses', [InstructorCourseController::class, 'index'])
            ->name('courses.index');

        Route::get('courses/{course}/materials', [MaterialController::class, 'index'])
            ->name('courses.materials');

        // create
        Route::post('modules', [MaterialController::class, 'storeModule'])->name('modules.store');
        Route::post('topics', [MaterialController::class, 'storeTopic'])->name('topics.store');
        Route::post('materials', [MaterialController::class, 'storeMaterial'])->name('materials.store');

        // update
        Route::put('modules/{module}', [MaterialController::class, 'updateModule'])->name('modules.update');
        Route::put('topics/{topic}', [MaterialController::class, 'updateTopic'])->name('topics.update');
        Route::put('materials/{material}', [MaterialController::class, 'updateMaterial'])->name('materials.update');

        // delete
        Route::delete('modules/{module}', [MaterialController::class, 'destroyModule'])->name('modules.destroy');
        Route::delete('topics/{topic}', [MaterialController::class, 'destroyTopic'])->name('topics.destroy');
        Route::delete('materials/{material}', [MaterialController::class, 'destroyMaterial'])->name('materials.destroy');
    });


Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('leads', LeadController::class)->except(['show']);
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
});



require __DIR__.'/auth.php';
