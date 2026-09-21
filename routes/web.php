<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdvokatController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LegalAssistantController;

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ConversationController;

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

// ─── Shared Authenticated Document Security Routes (IDOR Protected) ─────────
Route::middleware('auth')->group(function () {
    Route::get('/documents/{id}/view', [DocumentController::class, 'view'])->name('documents.view');
    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])->name('documents.download');
});

// ─── Admin Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/clients', [AdminController::class, 'clients'])->name('admin.clients');
    Route::get('/lawyers', [AdminController::class, 'lawyers'])->name('admin.lawyers');
    Route::get('/cases', [AdminController::class, 'cases'])->name('admin.cases');
    Route::get('/consultations', [AdminController::class, 'consultations'])->name('admin.consultations');
    Route::post('/consultations/{id}/assign-lawyer', [AdminController::class, 'assignLawyer'])->name('admin.consultations.assign');
    Route::get('/documents', [AdminController::class, 'documents'])->name('admin.documents');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/knowledge', [AdminController::class, 'knowledge'])->name('admin.knowledge');
    Route::post('/knowledge', [AdminController::class, 'storeKnowledge'])->name('admin.knowledge.store');
    Route::put('/knowledge/{id}', [AdminController::class, 'updateKnowledge'])->name('admin.knowledge.update');
    Route::patch('/knowledge/{id}/toggle-status', [AdminController::class, 'toggleKnowledgeStatus'])->name('admin.knowledge.toggle');
    Route::delete('/knowledge/{id}', [AdminController::class, 'destroyKnowledge'])->name('admin.knowledge.destroy');
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

    // Perkara & Dokumen
    Route::get('/perkara', [AdvokatController::class, 'cases'])->name('advokat.cases');
    Route::get('/perkara/{id}', [AdvokatController::class, 'caseDetail'])->name('advokat.cases.show');
    Route::post('/perkara', [AdvokatController::class, 'storeCase'])->name('advokat.cases.store');
    Route::post('/perkara/{id}/perkembangan', [AdvokatController::class, 'addProgress'])->name('advokat.cases.progress');
    Route::get('/dokumen', [AdvokatController::class, 'documents'])->name('advokat.documents');
    Route::post('/perkara/{id}/dokumen', [AdvokatController::class, 'requestDocument'])->name('advokat.cases.document');
    Route::post('/perkara/{id}/dokumen/upload', [AdvokatController::class, 'uploadCaseDocument'])->name('advokat.cases.document.upload');
    Route::post('/dokumen/{id}/verifikasi', [AdvokatController::class, 'verifyDocument'])->name('advokat.documents.verify');
    Route::post('/dokumen/{id}/tolak', [AdvokatController::class, 'rejectDocument'])->name('advokat.documents.reject');

    // Klien
    Route::get('/klien', [AdvokatController::class, 'clients'])->name('advokat.clients');
    Route::get('/klien/{id}', [AdvokatController::class, 'clientDetail'])->name('advokat.clients.show');

    // Asisten Hukum (RAG + JDIH BPK + Gemini AI)
    Route::get('/asisten-hukum', [LegalAssistantController::class, 'index'])->name('advokat.assistant');
    Route::post('/asisten-hukum/chat', [LegalAssistantController::class, 'chat'])->name('advokat.assistant.chat');
    Route::post('/asisten-hukum/clear', [LegalAssistantController::class, 'clearHistory'])->name('advokat.assistant.clear');

    // Jadwal
    Route::get('/jadwal', [AdvokatController::class, 'schedule'])->name('advokat.schedule');

    // Percakapan
    Route::get('/percakapan', [ConversationController::class, 'index'])->name('advokat.chat');
    Route::post('/percakapan/{id}/messages', [ConversationController::class, 'sendMessage'])->name('advokat.chat.send');
    Route::get('/percakapan/{id}/messages', [ConversationController::class, 'getMessages'])->name('advokat.chat.messages');
});

// ─── Klien Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:klien'])->prefix('klien')->group(function () {
    Route::get('/', [ClientController::class, 'dashboard'])->name('klien.dashboard');
    Route::get('/konsultasi', [ClientController::class, 'consultations'])->name('klien.consultations');
    Route::post('/konsultasi', [ClientController::class, 'storeConsultation'])->name('klien.consultations.store');
    Route::get('/konsultasi/{id}', [ClientController::class, 'consultationDetail'])->name('klien.consultations.show');
    Route::get('/perkara', [ClientController::class, 'cases'])->name('klien.cases');
    Route::get('/perkara/{id}', [ClientController::class, 'caseDetail'])->name('klien.cases.show');
    Route::get('/dokumen', [ClientController::class, 'documents'])->name('klien.documents');
    Route::post('/perkara/{id}/dokumen', [ClientController::class, 'uploadCaseDocument'])->name('klien.cases.document.upload');
    Route::post('/dokumen/{id}/reupload', [ClientController::class, 'reuploadDocument'])->name('klien.documents.reupload');

    // Jadwal
    Route::get('/jadwal', [ClientController::class, 'schedule'])->name('klien.schedule');

    // Asisten Hukum (RAG + Gemini AI)
    Route::get('/asisten-hukum', [LegalAssistantController::class, 'index'])->name('klien.assistant');
    Route::post('/asisten-hukum/chat', [LegalAssistantController::class, 'chat'])->name('klien.assistant.chat');
    Route::post('/asisten-hukum/clear', [LegalAssistantController::class, 'clearHistory'])->name('klien.assistant.clear');

    // Percakapan
    Route::get('/percakapan', [ConversationController::class, 'index'])->name('klien.chat');
    Route::post('/percakapan/{id}/messages', [ConversationController::class, 'sendMessage'])->name('klien.chat.send');
    Route::get('/percakapan/{id}/messages', [ConversationController::class, 'getMessages'])->name('klien.chat.messages');
});
