<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursePlayerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserCourseController;
use App\Http\Controllers\TypesController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Admin\AffiliatePayoutController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Illuminate\Support\Facades\Route;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/types', [TypesController::class, 'index']);
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])
    ->name('payments.webhook');
Route::get('/public-courses', [UserCourseController::class, 'publicCourses']);
Route::get('/explore-courses', [UserCourseController::class, 'exploreCourses']);
Route::get('/courses/{uuid}', [UserCourseController::class, 'showCourse']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::get('/verify-reset-token', [PasswordResetController::class, 'verifyToken']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);


// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // Course player routes
    Route::get('/courses/{uuid}/player', [CoursePlayerController::class, 'show']);
    Route::get('/videos/{uuid}/token', [CoursePlayerController::class, 'getVideoToken']);
    Route::post('/progress/{uuid}', [CoursePlayerController::class, 'updateProgress']);

    // User course routes
    Route::get('/my-courses', [UserCourseController::class, 'myEnrolledCourses']);
    Route::post('/courses/{uuid}/enroll', [UserCourseController::class, 'enrollCourse']);
    Route::get('/continue-learning', [UserCourseController::class, 'continueLearningSuggestion']);

    // Dashboard stats
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Enrollment routes
    Route::post('/enroll/{uuid}', [EnrollmentController::class, 'enroll']);
    Route::get('/enroll/{uuid}/status', [EnrollmentController::class, 'status']);

    Route::get('/payments/currencies', [PaymentController::class, 'currencies']);
    Route::post('/payments/initiate/{uuid}', [PaymentController::class, 'initiatePayment']);
    Route::get('/payments/status/{orderUuid}',   [PaymentController::class, 'checkStatus']);
    Route::post('/payments/cancel/{orderUuid}', [PaymentController::class, 'cancelPayment']);

    // Coupon validation (user-facing)
    Route::post('/coupons/validate', [CouponController::class, 'validate']);

    // Affiliate routes (student-facing)
    Route::get('/affiliate/dashboard', [AffiliateController::class, 'dashboard']);
    Route::get('/affiliate/commissions', [AffiliateController::class, 'commissions']);
    Route::get('/affiliate/payouts', [AffiliateController::class, 'payouts']);
    Route::post('/affiliate/payouts', [AffiliateController::class, 'requestPayout']);
});

// Admin routes (protected by auth and admin middleware)
Route::middleware(['auth:api', 'role:admin|super_admin'])->prefix('admin')->group(function () {
    // Dashboard stats & recent data
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // Course routes
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/{uuid}', [CourseController::class, 'show']);
    Route::put('/courses/{uuid}', [CourseController::class, 'update']);
    Route::delete('/courses/{uuid}', [CourseController::class, 'destroy']);
    Route::post('/courses/{uuid}/thumbnail', [CourseController::class, 'uploadThumbnail']);
    Route::delete('/courses/{uuid}/thumbnail', [CourseController::class, 'deleteThumbnail']);

    // Chapter routes
    Route::get('/chapters', [ChapterController::class, 'index']);
    Route::post('/chapters', [ChapterController::class, 'store']);
    Route::get('/chapters/{uuid}', [ChapterController::class, 'show']);
    Route::put('/chapters/{uuid}', [ChapterController::class, 'update']);
    Route::delete('/chapters/{uuid}', [ChapterController::class, 'destroy']);
    Route::post('/chapters/reorder', [ChapterController::class, 'reorder']);

    // Video routes
    Route::get('/videos', [VideoController::class, 'index']);
    Route::post('/videos', [VideoController::class, 'store']);
    Route::get('/videos/{uuid}', [VideoController::class, 'show']);
    Route::put('/videos/{uuid}', [VideoController::class, 'update']);
    Route::delete('/videos/{uuid}', [VideoController::class, 'destroy']);
    Route::post('/videos/{uuid}/upload', [VideoController::class, 'uploadVideo']);
    Route::post('/videos/reorder', [VideoController::class, 'reorder']);

    // Order routes
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::put('/orders/{uuid}', [AdminOrderController::class, 'update']);

    // Student routes
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students/{uuid}/ban', [StudentController::class, 'ban']);
    Route::post('/students/{uuid}/unban', [StudentController::class, 'unban']);

    // Enrollment routes
    Route::get('/enrollments', [AdminEnrollmentController::class, 'index']);
    Route::post('/enrollments', [AdminEnrollmentController::class, 'store']);
    Route::delete('/enrollments/{uuid}', [AdminEnrollmentController::class, 'destroy']);

    // Coupon routes
    Route::get('/coupons', [AdminCouponController::class, 'index']);
    Route::post('/coupons', [AdminCouponController::class, 'store']);
    Route::get('/coupons/{uuid}', [AdminCouponController::class, 'show']);
    Route::put('/coupons/{uuid}', [AdminCouponController::class, 'update']);
    Route::delete('/coupons/{uuid}', [AdminCouponController::class, 'destroy']);
    Route::patch('/coupons/{uuid}/toggle', [AdminCouponController::class, 'toggle']);
    Route::get('/coupons/{uuid}/usage', [AdminCouponController::class, 'usage']);

    // Affiliate payout routes
    Route::get('/affiliate/payouts', [AffiliatePayoutController::class, 'index']);
    Route::get('/affiliate/payouts/{uuid}', [AffiliatePayoutController::class, 'show']);
    Route::post('/affiliate/payouts/{uuid}/approve', [AffiliatePayoutController::class, 'approve']);
    Route::post('/affiliate/payouts/{uuid}/complete', [AffiliatePayoutController::class, 'complete']);
    Route::post('/affiliate/payouts/{uuid}/reject', [AffiliatePayoutController::class, 'reject']);
});
