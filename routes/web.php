<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdvokatController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login if not authenticated
Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Admin Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/clients', [AdminController::class, 'clients'])->name('admin.clients');
    Route::get('/lawyers', [AdminController::class, 'lawyers'])->name('admin.lawyers');
    Route::get('/cases', [AdminController::class, 'cases'])->name('admin.cases');
    Route::get('/consultations', [AdminController::class, 'consultations'])->name('admin.consultations');
    Route::get('/documents', [AdminController::class, 'documents'])->name('admin.documents');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/knowledge', [AdminController::class, 'knowledge'])->name('admin.knowledge');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
});

// ─── Advokat Routes ────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:advokat'])->prefix('advokat')->group(function () {
    Route::get('/', [AdvokatController::class, 'dashboard'])->name('advokat.dashboard');

    // Konsultasi
    Route::get('/konsultasi', [AdvokatController::class, 'consultations'])->name('advokat.consultations');
    Route::get('/konsultasi/{id}', [AdvokatController::class, 'consultationDetail'])->name('advokat.consultations.show');
    Route::post('/konsultasi/{id}/jadwalkan', [AdvokatController::class, 'scheduleConsultation'])->name('advokat.consultations.schedule');
    Route::post('/konsultasi/{id}/selesai', [AdvokatController::class, 'completeConsultation'])->name('advokat.consultations.complete');

    // Perkara
    Route::get('/perkara', [AdvokatController::class, 'cases'])->name('advokat.cases');
    Route::get('/perkara/{id}', [AdvokatController::class, 'caseDetail'])->name('advokat.cases.show');
    Route::post('/perkara', [AdvokatController::class, 'storeCase'])->name('advokat.cases.store');
    Route::post('/perkara/{id}/perkembangan', [AdvokatController::class, 'addProgress'])->name('advokat.cases.progress');
    Route::post('/perkara/{id}/dokumen', [AdvokatController::class, 'requestDocument'])->name('advokat.cases.document');
    Route::post('/dokumen/{id}/verifikasi', [AdvokatController::class, 'verifyDocument'])->name('advokat.documents.verify');

    // Klien
    Route::get('/klien', [AdvokatController::class, 'clients'])->name('advokat.clients');
    Route::get('/klien/{id}', [AdvokatController::class, 'clientDetail'])->name('advokat.clients.show');
});

// ─── Klien Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:klien'])->prefix('klien')->group(function () {
    Route::get('/', [ClientController::class, 'dashboard'])->name('klien.dashboard');
    Route::get('/konsultasi', [ClientController::class, 'consultations'])->name('klien.consultations');
    Route::post('/konsultasi', [ClientController::class, 'storeConsultation'])->name('klien.consultations.store');
    Route::get('/konsultasi/{id}', [ClientController::class, 'consultationDetail'])->name('klien.consultations.show');
    Route::get('/perkara', [ClientController::class, 'cases'])->name('klien.cases');
    Route::get('/perkara/{id}', [ClientController::class, 'caseDetail'])->name('klien.cases.show');
    Route::post('/dokumen/{id}/upload', [ClientController::class, 'uploadDocument'])->name('klien.documents.upload');
});
