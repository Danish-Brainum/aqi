<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AQIController;
use App\Http\Controllers\AuthController;


// Authenticated dashboard (home)
Route::middleware('auth')->group(function () {
    Route::get('/', [AQIController::class, 'index'])->name('home');
    Route::post('/upload', [AQIController::class, 'upload'])->name('upload');
    Route::get('/download', [AQIController::class, 'download'])->name('download');
    Route::post('/save_messages', [AQIController::class, 'saveMessages'])->name('save_messages');
    Route::post('/records/update', [AQIController::class, 'update'])->name('records.update');
    Route::post('/records/delete', [AQIController::class, 'delete'])->name('records.delete');
    Route::get('/deleted-table', [AQIController::class, 'deletedTable'])->name('deleted.table');
    Route::get('/status', [AQIController::class, 'status'])->name('status');
    Route::get('/fetch-all', [AQIController::class, 'fetchAll'])->name('fetch.all');
    Route::post('/sendEmails', [AQIController::class, 'sendEmails'])->name('sendEmails');
    Route::post('/sendWhatsapp', [AQIController::class, 'sendWhatsapp'])->name('sendWhatsapp');
    Route::post('/save-CSV', [AQIController::class, 'saveCSV'])->name('saveCSV');
    Route::post('/add-manual-record', [AQIController::class, 'addManualRecord'])->name('add-manual-record');
    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});
