<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DevelopmentController,
    EventController,
    SocialAidController,
    AidApplicationController,
    UserController,
    HeadOfFamilyController,
    ResidentController
};

// ========================
// AUTH
// ========================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ========================
// PROTECTED ROUTES
// ========================
Route::middleware('auth:sanctum')->group(function () {

    /**
     * ========================
     *   ADMIN (Full CRUD)
     * ========================
     */
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('head-of-families', HeadOfFamilyController::class);
        Route::apiResource('residents', ResidentController::class);
        Route::apiResource('social-aids', SocialAidController::class);
        Route::apiResource('aid-applications', AidApplicationController::class);
        Route::apiResource('developments', DevelopmentController::class);
        Route::apiResource('events', EventController::class);
    });

    /**
     * ========================
     *   USER = Kepala Keluarga
     * ========================
     */
    Route::middleware('role:user')->group(function () {
        // Kelola data keluarga & anggota keluarganya
        Route::apiResource('my-head-of-family', HeadOfFamilyController::class)->only(['index','show','store','update','destroy']);
        Route::apiResource('my-residents', ResidentController::class)->only(['index','show','store','update','destroy']);

        // Ajukan & lihat bantuan miliknya
        Route::post('/my-aid-applications', [AidApplicationController::class, 'store']);
        Route::get('/my-aid-applications', [AidApplicationController::class, 'myApplications']);
    });
});
