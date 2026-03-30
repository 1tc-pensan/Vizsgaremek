<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\Admin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publikus végpontok (autentikáció nélkül)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Publikus lista és részletek
Route::get('/reports',              [ReportController::class, 'index']);
Route::get('/reports/{report}',     [ReportController::class, 'show']);
Route::get('/map/reports',          [ReportController::class, 'mapReports']);
Route::get('/reports/{report}/credibility', [VoteController::class, 'credibility']);
Route::get('/reports/{report}/images',      [ImageController::class, 'index']);

Route::get('/categories',           [CategoryController::class, 'index']);
Route::get('/categories/{category}',[CategoryController::class, 'show']);

Route::get('/statistics',           [StatisticsController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Védett végpontok (Bearer token szükséges)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'check.banned'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);

    // Profil
    Route::get('/profile',                     [ProfileController::class, 'show']);
    Route::put('/profile',                     [ProfileController::class, 'update']);
    Route::get('/users/{userId}/reports',      [ProfileController::class, 'userReports']);

    // Bejelentések (létrehozás, módosítás, törlés)
    Route::post('/reports',                    [ReportController::class, 'store']);
    Route::put('/reports/{report}',            [ReportController::class, 'update']);
    Route::delete('/reports/{report}',         [ReportController::class, 'destroy']);

    // Képek feltöltése / törlése
    Route::post('/reports/{report}/images',    [ImageController::class, 'store']);
    Route::delete('/images/{image}',           [ImageController::class, 'destroy']);

    // Szavazás
    Route::post('/reports/{report}/vote',      [VoteController::class, 'vote']);

    /*
    |--------------------------------------------------------------------------
    | Admin végpontok
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->group(function () {

        // Bejelentés moderálás
        Route::get('/reports',                          [Admin\ReportController::class, 'index']);
        Route::delete('/reports/{report}',              [Admin\ReportController::class, 'destroy']);
        Route::put('/reports/{report}/approve',         [Admin\ReportController::class, 'approve']);
        Route::put('/reports/{report}/reject',          [Admin\ReportController::class, 'reject']);

        // Felhasználó kezelés
        Route::get('/users',                            [Admin\UserController::class, 'index']);
        Route::put('/users/{user}/ban',                 [Admin\UserController::class, 'ban']);
        Route::put('/users/{user}/unban',               [Admin\UserController::class, 'unban']);

        // Kategória kezelés
        Route::post('/categories',                      [CategoryController::class, 'store']);
        Route::put('/categories/{category}',            [CategoryController::class, 'update']);
        Route::delete('/categories/{category}',         [CategoryController::class, 'destroy']);

        // Admin statisztikák
        Route::get('/statistics',                       [Admin\StatisticsController::class, 'index']);
    });
});
