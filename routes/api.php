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
use App\Models\HeadOfFamily;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/public/developments', [DevelopmentController::class, 'publicIndex']);
Route::get('/public/events', [EventController::class, 'publicIndex']);


Route::middleware('auth:sanctum')->group(function () {

    //Admin / Kepala Desa
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('head-of-families', HeadOfFamilyController::class);
        Route::apiResource('residents', ResidentController::class);
        Route::apiResource('social-aids', SocialAidController::class);
        Route::apiResource('aid-applications', AidApplicationController::class);
        Route::apiResource('developments', DevelopmentController::class);
        Route::apiResource('events', EventController::class);
    });

    // Kepala Keluarga
    Route::middleware('role:user')->group(function () {
        Route::get('/my-head-of-family', [HeadOfFamilyController::class, 'myHead']);
        Route::apiResource('my-residents', ResidentController::class)->only(['index','show','store','update','destroy']);

        Route::post('/my-aid-applications', [AidApplicationController::class, 'store']);
        Route::get('/my-aid-applications', [AidApplicationController::class, 'myApplications']);

        Route::get('/developments', [DevelopmentController::class, 'publicIndex']);
    });
});
