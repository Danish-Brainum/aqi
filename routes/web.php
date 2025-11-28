<?php

use App\Http\Controllers\AQIController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WhatsappRecipientController;
use App\Http\Controllers\WhatsappWebhookController;
use Illuminate\Support\Facades\Route;



// Authenticated dashboard (home)
Route::middleware('auth')->group(function () {
    Route::get('/', [AQIController::class, 'index'])->name('home');
    Route::post('/upload', [AQIController::class, 'upload'])->name('upload');
    Route::get('/download', [AQIController::class, 'download'])->name('download');
    Route::post('/save_messages', [AQIController::class, 'saveMessages'])->name('save_messages');
    Route::get('/get-city-messages', [AQIController::class, 'getCityMessages'])->name('get_city_messages');
    Route::post('/records/update', [AQIController::class, 'update'])->name('records.update');
    Route::post('/records/delete', [AQIController::class, 'delete'])->name('records.delete');
    Route::get('/deleted-table', [AQIController::class, 'deletedTable'])->name('deleted.table');
    Route::get('/status', [AQIController::class, 'status'])->name('status');
    Route::get('/fetch-all', [AQIController::class, 'fetchAll'])->name('fetch.all');
    Route::post('/sendEmails', [AQIController::class, 'sendEmails'])->name('sendEmails');
    Route::get('/csv-data', [AQIController::class, 'getCsvData'])->name('csv.data');
    Route::post('/sendWhatsapp', [AQIController::class, 'sendWhatsapp'])->name('sendWhatsapp');
    Route::post('/save-CSV', [AQIController::class, 'saveCSV'])->name('saveCSV');
    Route::post('/add-manual-record', [AQIController::class, 'addManualRecord'])->name('add-manual-record');
    
    // WhatsApp Recipients Management
    Route::get('/whatsapp-recipients', [WhatsappRecipientController::class, 'index'])->name('whatsapp-recipients.index');
    Route::get('/whatsapp-recipients/list', [WhatsappRecipientController::class, 'list'])->name('whatsapp-recipients.list');
    Route::post('/whatsapp-recipients', [WhatsappRecipientController::class, 'store'])->name('whatsapp-recipients.store');
    Route::get('/whatsapp-recipients/{id}', [WhatsappRecipientController::class, 'show'])->name('whatsapp-recipients.show');
    Route::put('/whatsapp-recipients/{id}', [WhatsappRecipientController::class, 'update'])->name('whatsapp-recipients.update');
    Route::delete('/whatsapp-recipients/{id}', [WhatsappRecipientController::class, 'destroy'])->name('whatsapp-recipients.destroy');
    Route::post('/whatsapp-recipients/{id}/toggle-active', [WhatsappRecipientController::class, 'toggleActive'])->name('whatsapp-recipients.toggle-active');
    Route::post('/whatsapp-recipients/upload-csv', [WhatsappRecipientController::class, 'uploadCsv'])->name('whatsapp-recipients.upload-csv');
    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::put('/settings/update', [SettingsController::class, 'update'])->name('settings.update');

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

// WhatsApp Webhook routes (must be outside auth middleware and CSRF protection)
Route::get('/webhook/whatsapp', [WhatsappWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/webhook/whatsapp', [WhatsappWebhookController::class, 'receive'])->middleware(['throttle:60,1'])->name('whatsapp.webhook.receive');
