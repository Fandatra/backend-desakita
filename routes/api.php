<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DevelopmentController,
    EventController,
    SocialAidController,
    UserController,
    HeadOfFamilyController,
    ResidentController,
    SocialAidRecipientController
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
        Route::apiResource('developments', DevelopmentController::class);
        Route::apiResource('events', EventController::class);
        Route::get('/social-aids/{socialAid}/recipients', [SocialAidController::class, 'recipients']);
        Route::post('/social-aids/{id}/recipients', [SocialAidController::class, 'addRecipients']);
    });

    // Kepala Keluarga
    Route::middleware('role:user')->group(function () {
        Route::get('/my-head-of-family', [HeadOfFamilyController::class, 'myHead']);
        Route::apiResource('my-residents', ResidentController::class)->only(['index','show','store','update','destroy']);
        Route::get('/my-aids', [HeadOfFamilyController::class, 'myAids'])->middleware('role:user');
    });
});
