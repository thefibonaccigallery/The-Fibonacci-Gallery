<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursePlayerController;
use App\Http\Controllers\UserCourseController;
use App\Http\Controllers\TypesController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\VideoController;
use Illuminate\Support\Facades\Route;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/types', [TypesController::class, 'index']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // Course player routes
    Route::get('/courses/{courseId}/player', [CoursePlayerController::class, 'show']);
    Route::post('/progress/{videoId}', [CoursePlayerController::class, 'updateProgress']);

    // User course routes
    Route::get('/my-courses', [UserCourseController::class, 'myEnrolledCourses']);
    Route::get('/explore-courses', [UserCourseController::class, 'exploreCourses']);
    Route::get('/courses/{id}', [UserCourseController::class, 'showCourse']);
    Route::post('/courses/{courseId}/enroll', [UserCourseController::class, 'enrollCourse']);
    Route::get('/continue-learning', [UserCourseController::class, 'continueLearningSuggestion']);
});

// Admin routes (protected by auth and admin middleware)
Route::middleware(['auth:api', 'role:admin|super_admin'])->prefix('admin')->group(function () {
    // Course routes
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
    Route::post('/courses/{id}/thumbnail', [CourseController::class, 'uploadThumbnail']);
    Route::delete('/courses/{id}/thumbnail', [CourseController::class, 'deleteThumbnail']);

    // Chapter routes
    Route::get('/chapters', [ChapterController::class, 'index']);
    Route::post('/chapters', [ChapterController::class, 'store']);
    Route::get('/chapters/{id}', [ChapterController::class, 'show']);
    Route::put('/chapters/{id}', [ChapterController::class, 'update']);
    Route::delete('/chapters/{id}', [ChapterController::class, 'destroy']);
    Route::post('/chapters/reorder', [ChapterController::class, 'reorder']);

    // Video routes
    Route::get('/videos', [VideoController::class, 'index']);
    Route::post('/videos', [VideoController::class, 'store']);
    Route::get('/videos/{id}', [VideoController::class, 'show']);
    Route::put('/videos/{id}', [VideoController::class, 'update']);
    Route::delete('/videos/{id}', [VideoController::class, 'destroy']);
    Route::post('/videos/{id}/upload', [VideoController::class, 'uploadVideo']);
    Route::post('/videos/reorder', [VideoController::class, 'reorder']);
});

