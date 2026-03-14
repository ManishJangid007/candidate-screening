<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ExcelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
    Route::get('/candidates/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');
    Route::post('/candidates/{candidate}/evaluate', [CandidateController::class, 'evaluate'])->name('candidates.evaluate');

    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::post('/candidates/{candidate}/assign', [CandidateController::class, 'assignInterviewer'])->name('candidates.assign');
        Route::post('/candidates/{candidate}/revert', [CandidateController::class, 'revert'])->name('candidates.revert');
        Route::post('/excel/upload', [ExcelController::class, 'upload'])->name('excel.upload');
    });
});
