<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\InformasiController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\BabMateriController;
use App\Http\Controllers\Api\SoalController;
use App\Http\Controllers\Api\SoalPertanyaanController;
use App\Http\Controllers\Api\SesiSoalController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/', function () {
    return response()->json([
        'status' => false,
        'message' => 'You are not authorized'
    ], 403);
})->name('login');

Route::post('/user/register', [UserController::class, 'register']);
Route::post('/user/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'index']);
    Route::post('/user/logout', [UserController::class, 'logout']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'delete']);

    Route::get('/informasi', [InformasiController::class, 'index']);
    Route::get('/informasi/{id}', [InformasiController::class, 'show']);
    Route::post('/informasi', [InformasiController::class, 'store']);
    Route::put('/informasi/{id}', [InformasiController::class, 'update']);
    Route::delete('/informasi/{id}', [InformasiController::class, 'destroy']);

    Route::get('/materi', [MateriController::class, 'index']);
    Route::get('/materi/{id}', [MateriController::class, 'show']);
    Route::post('/materi', [MateriController::class, 'store']);
    Route::put('/materi/{id}', [MateriController::class, 'update']);
    Route::delete('/materi/{id}', [MateriController::class, 'destroy']);

    Route::get('/babmateri', [BabMateriController::class, 'index']);
    Route::get('/babmateri/{id}', [BabMateriController::class, 'show']);
    Route::post('/babmateri', [BabMateriController::class, 'store']);
    Route::put('/babmateri/{id}', [BabMateriController::class, 'update']);
    Route::delete('/babmateri/{id}', [BabMateriController::class, 'destroy']);

    Route::get('/soal', [SoalController::class, 'index']);
    Route::get('/soal/{id}', [SoalController::class, 'show']);
    Route::post('/soal', [SoalController::class, 'store']);
    Route::put('/soal/{id}', [SoalController::class, 'update']);
    Route::delete('/soal/{id}', [SoalController::class, 'destroy']);

    Route::get('/soalpertanyaan', [SoalPertanyaanController::class, 'index']);
    Route::get('/soalpertanyaan/{id}', [SoalPertanyaanController::class, 'show']);
    Route::post('/soalpertanyaan', [SoalPertanyaanController::class, 'store']);
    Route::put('/soalpertanyaan/{id}', [SoalPertanyaanController::class, 'update']);
    Route::delete('/soalpertanyaan/{id}', [SoalPertanyaanController::class, 'destroy']);
    Route::get('/soalpertanyaan/materi/{materiId}', [SoalPertanyaanController::class, 'getByMateri']);


    Route::get('/sesisoal/tampilsesi', [SesiSoalController::class, 'tampilsesi']);
    Route::get('/sesisoal/ceksesi', [SesiSoalController::class, 'cekSesiSoal']);
    Route::get('/sesisoal', [SesiSoalController::class, 'index']);
    Route::get('/sesisoal/{id}', [SesiSoalController::class, 'show']);
    Route::post('/sesisoal', [SesiSoalController::class, 'store']);
    Route::post('/sesisoal/submitjawaban', [SesiSoalController::class, 'submitJawaban']);
    Route::put('/sesisoal/{id}', [SesiSoalController::class, 'update']);
    Route::delete('/sesisoal/{id}', [SesiSoalController::class, 'destroy']);



    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
});

