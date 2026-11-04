<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\CaseProgress;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\DocumentVersion;
use App\Models\DocumentComment;
use App\Models\DocumentAuditLog;
use App\Models\CaseLawyer;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\DocumentCollaborationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvokatController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $lawyer = Auth::user();
        $today  = Carbon::today();

        $stats = [
            'new_consultations' => Consultation::where('lawyer_id', $lawyer->id)
                ->where('status', 'Menunggu')->count(),
            'active_cases'      => LegalCase::where('lawyer_id', $lawyer->id)
                ->whereNotIn('status', ['Selesai', 'Dibatalkan'])->count(),
            'pending_documents' => Document::where('lawyer_id', $lawyer->id)
                ->where('status', 'Menunggu Pemeriksaan')->count(),
            'today_schedules'   => Schedule::where('lawyer_id', $lawyer->id)
                ->whereDate('start_at', $today)->count(),
        ];

        $todaySchedules = Schedule::where('lawyer_id', $lawyer->id)
            ->whereDate('start_at', $today)
            ->orderBy('start_at')
            ->with(['client', 'consultation', 'case'])
            ->get();

        $newConsultations = Consultation::where('lawyer_id', $lawyer->id)
            ->where('status', 'Menunggu')
            ->orderBy('created_at', 'desc')
            ->with('client')
            ->take(5)
            ->get();

        return view('advokat.dashboard', compact('stats', 'todaySchedules', 'newConsultations'));
    }

    // ─── Konsultasi ───────────────────────────────────────────────────────────
    public function consultations(Request $request)
    {
        $lawyer = Auth::user();
        $filter = $request->query('status', 'Semua');

        $query = Consultation::where('lawyer_id', $lawyer->id)->with(['client', 'conversation']);
        if ($filter !== 'Semua') {
            $query->where('status', $filter);
        }
        $consultations = $query->orderBy('created_at', 'desc')->get();

        // Load first item as active by default
        $activeId     = $request->query('id', $consultations->first()?->id);
        $consultation = Consultation::with(['client', 'lawyer', 'case', 'conversation'])
            ->where('lawyer_id', $lawyer->id)
            ->find($activeId);

        return view('advokat.consultations', compact('consultations', 'consultation', 'filter'));
    }

    public function consultationDetail($id)
    {
        $lawyer       = Auth::user();
        $consultation = Consultation::with(['client', 'lawyer', 'case', 'conversation'])
            ->where('lawyer_id', $lawyer->id)->findOrFail($id);
        $filter       = 'Semua';
        $consultations = Consultation::where('lawyer_id', $lawyer->id)
            ->with(['client', 'conversation'])->orderBy('created_at', 'desc')->get();
        return view('advokat.consultations', compact('consultations', 'consultation', 'filter'));
    }

    public function scheduleConsultation(Request $request, $id)
    {
        $request->validate([
            'scheduled_at' => 'required|date',
            'location'     => 'nullable|string|max:255',
        ]);

        $lawyer       = Auth::user();
        $consultation = Consultation::where('lawyer_id', $lawyer->id)->findOrFail($id);
        $consultation->update([
            'scheduled_at' => $request->scheduled_at,
            'status'       => 'Dijadwalkan',
        ]);

        // Create or update schedule entry to prevent duplicates
        $clientName = $consultation->client?->name ?? 'Klien';
        $existingSchedule = Schedule::where('consultation_id', $consultation->id)->latest()->first();
        if ($existingSchedule) {
            $existingSchedule->update([
                'lawyer_id'   => $lawyer->id,
                'client_id'   => $consultation->client_id,
                'title'       => 'Konsultasi — ' . $clientName,
                'description' => $consultation->title,
                'start_at'    => $request->scheduled_at,
                'location'    => $request->location ?? 'Kantor',
                'status'      => 'Aktif',
            ]);
        } else {
            Schedule::create([
                'lawyer_id'       => $lawyer->id,
                'client_id'       => $consultation->client_id,
                'consultation_id' => $consultation->id,
                'title'           => 'Konsultasi — ' . $clientName,
                'description'     => $consultation->title,
                'start_at'        => $request->scheduled_at,
                'location'        => $request->location ?? 'Kantor',
                'status'          => 'Aktif',
            ]);
        }

        // Notify Client
        if ($consultation->client) {
            try {
                $consultation->client->notify(new \App\Notifications\ScheduleUpdatedNotification($consultation, $request->location ?? 'Kantor'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to notify client on schedule update: " . $e->getMessage());
            }
        }

        return redirect()->route('advokat.consultations.show', $id)
            ->with('success', 'Konsultasi berhasil dijadwalkan.');
    }

    public function completeConsultation(Request $request, $id)
    {
        $request->validate([
            'result_notes' => 'required|string',
        ]);

        $lawyer       = Auth::user();
        $consultation = Consultation::where('lawyer_id', $lawyer->id)->findOrFail($id);
        $consultation->update([
            'status'       => 'Selesai',
            'result_notes' => $request->result_notes,
        ]);

        Schedule::where('consultation_id', $consultation->id)->update([
            'status' => 'Selesai',
        ]);

        if ($consultation->client) {
            $consultation->client->notify(new \App\Notifications\ConsultationCompletedClientNotification($consultation));
        }

        return redirect()->route('advokat.consultations.show', $id)
            ->with('success', 'Hasil konsultasi berhasil dicatat.');
    }

    // ─── Perkara ──────────────────────────────────────────────────────────────
    public function cases(Request $request)
    {
        $lawyer = Auth::user();
        $filter = $request->query('status', 'Semua');

        $query = LegalCase::where(function ($q) use ($lawyer) {
            $q->where('lawyer_id', $lawyer->id)
              ->orWhereHas('caseLawyers', fn($sq) => $sq->where('user_id', $lawyer->id));
        })->with(['client', 'teamMembers']);

        if ($filter !== 'Semua') {
            $query->where('status', $filter);
        }
        $cases = $query->orderBy('created_at', 'desc')->get();

        $availableConsultations = Consultation::where('lawyer_id', $lawyer->id)
            ->whereNotIn('status', ['Dibatalkan'])
            ->whereDoesntHave('case')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeId = $request->query('id', $cases->first()?->id);
        $case     = null;

        if ($activeId) {
            $case = LegalCase::with([
                'client',
                'lawyer',
                'teamMembers',
                'caseLawyers.lawyer',
                'progress',
                'documents' => function ($q) {
                    $q->with('uploader', 'verifier')->orderBy('created_at', 'desc');
                },
                'documentRequests' => function ($q) {
                    $q->with('client')->orderBy('created_at', 'desc');
                },
            ])->where(function ($q) use ($lawyer) {
                $q->where('lawyer_id', $lawyer->id)
                  ->orWhereHas('caseLawyers', fn($sq) => $sq->where('user_id', $lawyer->id));
            })->find($activeId);
        }

        return view('advokat.cases', compact('cases', 'case', 'filter', 'availableConsultations'));
    }

    public function caseDetail($id)
    {
        $lawyer = Auth::user();
        $case   = LegalCase::with([
            'client',
            'lawyer',
            'teamMembers',
            'caseLawyers.lawyer',
            'progress',
            'documents' => function ($q) {
                $q->with('uploader', 'verifier')->orderBy('created_at', 'desc');
            },
            'documentRequests' => function ($q) {
                $q->with('client')->orderBy('created_at', 'desc');
            },
        ])->where(function ($q) use ($lawyer) {
            $q->where('lawyer_id', $lawyer->id)
              ->orWhereHas('caseLawyers', fn($sq) => $sq->where('user_id', $lawyer->id));
        })->findOrFail($id);

        $filter = 'Semua';
        $cases  = LegalCase::where(function ($q) use ($lawyer) {
            $q->where('lawyer_id', $lawyer->id)
              ->orWhereHas('caseLawyers', fn($sq) => $sq->where('user_id', $lawyer->id));
        })->with(['client', 'teamMembers'])->orderBy('created_at', 'desc')->get();

        $availableConsultations = Consultation::where('lawyer_id', $lawyer->id)
            ->whereNotIn('status', ['Dibatalkan'])
            ->whereDoesntHave('case')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('advokat.cases', compact('cases', 'case', 'filter', 'availableConsultations'));
    }

    // ─── Manajemen Dokumen Perkara Advokat ────────────────────────────────────
    public function documents(Request $request = null)
    {
        $request = $request ?? request();
        $lawyer = Auth::user();

        $cases = LegalCase::where(function ($q) use ($lawyer) {
            $q->where('lawyer_id', $lawyer->id)
              ->orWhereHas('caseLawyers', fn($sq) => $sq->where('user_id', $lawyer->id));
        })
            ->with('client')
            ->orderBy('started_at', 'desc')
            ->get();

        $caseIds = $cases->pluck('id')->toArray();

        $selectedCaseId = $request->query('case_id');
        $statusFilter   = $request->query('status', 'all');
        $search         = $request->query('q');

        $query = Document::where(function ($q) use ($lawyer, $caseIds) {
            $q->where('lawyer_id', $lawyer->id)
              ->orWhere('uploaded_by', $lawyer->id)
              ->orWhereIn('case_id', $caseIds);
        })->with(['case.client', 'client', 'uploader', 'verifier'])
          ->orderBy('created_at', 'desc');

        if ($selectedCaseId) {
            $query->where('case_id', $selectedCaseId);
        }

        if ($statusFilter && $statusFilter !== 'all') {
            if ($statusFilter === 'Terverifikasi') {
                $query->whereIn('status', ['Terverifikasi', 'Sudah Diterima']);
            } elseif ($statusFilter === 'Ditolak') {
                $query->whereIn('status', ['Ditolak', 'Perlu Diperbaiki']);
            } elseif ($statusFilter === 'Menunggu') {
                $query->whereIn('status', ['Menunggu Verifikasi', 'Menunggu Pemeriksaan', 'Belum Diunggah']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $documents = $query->get();

        $allLawyerDocs = Document::where(function ($q) use ($lawyer, $caseIds) {
            $q->where('lawyer_id', $lawyer->id)
              ->orWhereIn('case_id', $caseIds);
        })->get();

        $documentRequests = DocumentRequest::where('requested_by', $lawyer->id)
            ->with(['case', 'client'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total'        => $allLawyerDocs->count(),
            'verified'     => $allLawyerDocs->filter(fn($d) => in_array($d->status, ['Terverifikasi', 'Sudah Diterima']))->count(),
            'pending'      => $allLawyerDocs->filter(fn($d) => in_array($d->status, ['Menunggu Verifikasi', 'Menunggu Pemeriksaan', 'Belum Diunggah']))->count(),
            'rejected'     => $allLawyerDocs->filter(fn($d) => in_array($d->status, ['Ditolak', 'Perlu Diperbaiki']))->count(),
            'requests'     => $documentRequests->where('status', 'Menunggu Upload')->count(),
        ];

        return view('advokat.documents', compact(
            'documents',
            'cases',
            'documentRequests',
            'stats',
            'selectedCaseId',
            'statusFilter',
            'search'
        ));
    }

    public function storeCase(Request $request)
    {
        $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'case_number'     => 'required|string|unique:cases,case_number',
            'case_type'       => 'required|string',
            'title'           => 'required|string|max:255',
            'summary'         => 'nullable|string',
            'started_at'      => 'required|date',
        ]);

        $lawyer       = Auth::user();
        $consultation = Consultation::where('lawyer_id', $lawyer->id)
            ->whereNotIn('status', ['Dibatalkan'])
            ->whereDoesntHave('case')
            ->findOrFail($request->consultation_id);

        $case = LegalCase::create([
            'client_id'       => $consultation->client_id,
            'lawyer_id'       => $lawyer->id,
            'consultation_id' => $consultation->id,
            'case_number'     => $request->case_number,
            'case_type'       => $request->case_type,
            'title'           => $request->title,
            'summary'         => $request->summary,
            'status'          => 'Persiapan',
            'started_at'      => $request->started_at,
        ]);

        // Add initial progress
        CaseProgress::create([
            'case_id'       => $case->id,
            'title'         => 'Perkara dibuat berdasarkan hasil konsultasi',
            'description'   => 'Advokat membuat perkara berdasarkan hasil konsultasi yang telah selesai dilaksanakan.',
            'progress_date' => now()->toDateString(),
            'created_by'    => $lawyer->id,
        ]);

        // Link conversation to the new case after validating participants
        $conversation = \App\Models\Conversation::where('consultation_id', $consultation->id)->first();
        if ($conversation && (int)$conversation->client_id === (int)$case->client_id && (int)$conversation->lawyer_id === (int)$case->lawyer_id) {
            $conversation->update([
                'case_id' => $case->id,
                'title'   => 'Perkara: ' . $case->case_number . ' — ' . $case->title,
            ]);
        } elseif (!$conversation) {
            \App\Models\Conversation::create([
                'consultation_id' => $consultation->id,
                'case_id'         => $case->id,
                'client_id'       => $case->client_id,
                'lawyer_id'       => $case->lawyer_id,
                'title'           => 'Perkara: ' . $case->case_number . ' — ' . $case->title,
                'status'          => 'active',
                'last_message_at' => null,
            ]);
        }

        // Notify Client
        if ($case->client) {
            try {
                $case->client->notify(new \App\Notifications\CaseCreatedClientNotification($case));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to notify client on case creation: " . $e->getMessage());
            }
        }

        // Notify Admins
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewCaseAdminNotification($case));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to notify admins on case creation: " . $e->getMessage());
        }

        return redirect()->route('advokat.cases.show', $case->id)
            ->with('success', 'Perkara berhasil dibuat.');
    }

    public function addProgress(Request $request, $id)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'progress_date' => 'required|date',
        ]);

        $lawyer = Auth::user();
        $case   = LegalCase::where('lawyer_id', $lawyer->id)->findOrFail($id);

        $progress = CaseProgress::create([
            'case_id'       => $case->id,
            'title'         => $request->title,
            'description'   => $request->description,
            'progress_date' => $request->progress_date,
            'created_by'    => $lawyer->id,
        ]);

        // Notify Client
        if ($case->client) {
            try {
                $case->client->notify(new \App\Notifications\CaseProgressNotification($case, $progress));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to notify client on case progress: " . $e->getMessage());
            }
        }

        // Notify Admins
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\CaseProgressAdminNotification($case, $progress));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to notify admins on case progress: " . $e->getMessage());
        }

        return redirect()->route('advokat.cases.show', $id)
            ->with('success', 'Perkembangan perkara berhasil ditambahkan.');
    }

    public function requestDocument(Request $request, $id)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'document_type' => 'nullable|string|max:100',
            'description'   => 'nullable|string|max:1000',
            'due_date'      => 'nullable|date',
            'priority'      => 'in:Normal,Tinggi',
        ], [
            'title.required' => 'Judul dokumen yang diminta wajib diisi.',
        ]);

        $lawyer = Auth::user();
        $case   = LegalCase::where('lawyer_id', $lawyer->id)->findOrFail($id);

        $docReq = \App\Models\DocumentRequest::create([
            'case_id'       => $case->id,
            'requested_by'  => $lawyer->id,
            'client_id'     => $case->client_id,
            'title'         => $request->title,
            'document_type' => $request->document_type ?: 'Dokumen Pendukung',
            'description'   => $request->description,
            'due_date'      => $request->due_date,
            'priority'      => $request->priority ?? 'Normal',
            'status'        => 'Menunggu Upload',
        ]);

        // Audit Log
        \App\Models\DocumentAuditLog::record(
            null,
            $case->id,
            $lawyer->id,
            'request',
            "Advokat ({$lawyer->name}) mengirimkan permintaan dokumen: '{$docReq->title}' kepada Klien."
        );

        // Notify Client
        if ($case->client) {
            $case->client->notify(new \App\Notifications\DocumentRequestedNotification($docReq));
        }

        return redirect()->route('advokat.cases.show', $id)
            ->with('success', 'Permintaan dokumen berhasil dikirim ke Klien.');
    }

    public function verifyDocument(Request $request, $id)
    {
        $lawyer   = Auth::user();
        $document = Document::with('case')->where('lawyer_id', $lawyer->id)->findOrFail($id);

        $document->update([
            'status'           => 'Terverifikasi',
            'rejection_reason' => null,
            'verified_by'      => $lawyer->id,
            'verified_at'      => now(),
        ]);

        if ($document->document_request_id) {
            $document->request?->update(['status' => 'Selesai']);
        }

        // Audit Log
        \App\Models\DocumentAuditLog::record(
            $document->id,
            $document->case_id,
            $lawyer->id,
            'verify',
            "Advokat ({$lawyer->name}) memverifikasi dokumen '{$document->name}' sebagai sah/diterima."
        );

        // Notify Client
        $targetClient = $document->client ?? $document->case?->client;
        if ($targetClient) {
            $targetClient->notify(new \App\Notifications\DocumentVerifiedNotification($document));
        }

        return redirect()->back()->with('success', 'Dokumen berhasil diverifikasi.');
    }

    public function rejectDocument(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan dokumen wajib diisi.',
            'rejection_reason.min'      => 'Alasan penolakan minimal 5 karakter agar klien memahami instruksi perbaikan.',
        ]);

        $lawyer   = Auth::user();
        $document = Document::with('case')->where('lawyer_id', $lawyer->id)->findOrFail($id);

        $document->update([
            'status'           => 'Ditolak',
            'rejection_reason' => $request->rejection_reason,
            'verified_by'      => $lawyer->id,
            'verified_at'      => now(),
        ]);

        if ($document->document_request_id) {
            $document->request?->update(['status' => 'Menunggu Upload']);
        }

        // Audit Log
        \App\Models\DocumentAuditLog::record(
            $document->id,
            $document->case_id,
            $lawyer->id,
            'reject',
            "Advokat ({$lawyer->name}) menolak dokumen '{$document->name}'. Alasan: \"{$request->rejection_reason}\""
        );

        // Notify Client
        $targetClient = $document->client ?? $document->case?->client;
        if ($targetClient) {
            $targetClient->notify(new \App\Notifications\DocumentRejectedNotification($document, $request->rejection_reason));
        }

        return redirect()->back()->with('success', 'Dokumen telah ditolak dan klien telah menerima notifikasi perbaikan.');
    }

    /**
     * Advokat mengunggah dokumen resmi/tambahan untuk perkara
     */
    public function uploadCaseDocument(Request $request, $caseId)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'document_type' => 'required|string|max:100',
            'description'   => 'nullable|string|max:1000',
            'file'          => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (!$value || !($value instanceof \Illuminate\Http\UploadedFile)) return;
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format file tidak diizinkan. Hanya file PDF, JPG, JPEG, PNG, DOC, dan DOCX yang diperbolehkan.');
                    }
                },
            ],
        ], [
            'file.required'          => 'Berkas dokumen wajib diunggah.',
            'file.max'               => 'Ukuran file terlalu besar (maksimal 10 MB).',
            'name.required'          => 'Nama dokumen wajib diisi.',
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
        ]);

        $lawyer = Auth::user();
        $case   = LegalCase::where('lawyer_id', $lawyer->id)->findOrFail($caseId);

        $file       = $request->file('file');
        $ext        = strtolower($file->getClientOriginalExtension());
        $uniqueName = (string) \Illuminate\Support\Str::uuid() . '.' . $ext;
        $storedPath = $file->storeAs("documents/{$case->id}", $uniqueName, 'local');

        $mimeType = null;
        try {
            $mimeType = $file->getMimeType();
        } catch (\Throwable $e) {
            $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
        }

        $document = Document::create([
            'case_id'           => $case->id,
            'client_id'         => $case->client_id,
            'lawyer_id'         => $lawyer->id,
            'uploaded_by'       => $lawyer->id,
            'name'              => $request->name,
            'document_type'     => $request->document_type,
            'description'       => $request->description,
            'file_path'         => $storedPath,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $mimeType,
            'file_size'         => $file->getSize(),
            'status'            => 'Terverifikasi',
            'is_from_lawyer'    => true, // Ditandai dokumen resmi dari advokat
            'verified_by'       => $lawyer->id,
            'verified_at'       => now(),
        ]);

        // Audit Log
        \App\Models\DocumentAuditLog::record(
            $document->id,
            $case->id,
            $lawyer->id,
            'upload',
            "Advokat ({$lawyer->name}) mengunggah dokumen perkara: '{$document->name}' (Dokumen dari Advokat)."
        );

        return redirect()->route('advokat.cases.show', $case->id)
            ->with('success', 'Dokumen dari Advokat berhasil diunggah.');
    }

    // ─── Klien ────────────────────────────────────────────────────────────────
    public function clients(Request $request)
    {
        $lawyer = Auth::user();
        $search = $request->query('search', '');
        $filter = $request->query('filter', 'Semua');

        // Get unique client IDs from cases handled by this lawyer
        $clientIds = LegalCase::where('lawyer_id', $lawyer->id)
            ->pluck('client_id')->merge(
                Consultation::where('lawyer_id', $lawyer->id)->pluck('client_id')
            )->unique();

        $query = User::whereIn('id', $clientIds)->where('role', 'klien');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $clients = $query->with('clientProfile')->orderBy('name')->get();

        $activeId = $request->query('id', $clients->first()?->id);
        $client   = null;
        if ($activeId) {
            $client = User::with('clientProfile')->find($activeId);
            if ($client) {
                $client->activeCases       = LegalCase::where('client_id', $client->id)
                    ->where('lawyer_id', $lawyer->id)
                    ->whereNotIn('status', ['Selesai', 'Dibatalkan'])->count();
                $client->totalCases        = LegalCase::where('client_id', $client->id)
                    ->where('lawyer_id', $lawyer->id)->count();
                $client->caseList          = LegalCase::where('client_id', $client->id)
                    ->where('lawyer_id', $lawyer->id)->orderBy('created_at', 'desc')->get();
                $client->lastConsultation  = Consultation::where('client_id', $client->id)
                    ->where('lawyer_id', $lawyer->id)
                    ->latest()->first();
            }
        }

        return view('advokat.clients', compact('clients', 'client', 'search', 'filter'));
    }

    public function clientDetail($id)
    {
        $lawyer = Auth::user();
        $client = User::with('clientProfile')->findOrFail($id);

        $client->activeCases      = LegalCase::where('client_id', $client->id)
            ->where('lawyer_id', $lawyer->id)
            ->whereNotIn('status', ['Selesai', 'Dibatalkan'])->count();
        $client->totalCases       = LegalCase::where('client_id', $client->id)
            ->where('lawyer_id', $lawyer->id)->count();
        $client->caseList         = LegalCase::where('client_id', $client->id)
            ->where('lawyer_id', $lawyer->id)->orderBy('created_at', 'desc')->get();
        $client->lastConsultation = Consultation::where('client_id', $client->id)
            ->where('lawyer_id', $lawyer->id)->latest()->first();

        $clientIds = LegalCase::where('lawyer_id', $lawyer->id)
            ->pluck('client_id')->merge(
                Consultation::where('lawyer_id', $lawyer->id)->pluck('client_id')
            )->unique();
        $clients = User::whereIn('id', $clientIds)->where('role', 'klien')
            ->with('clientProfile')->orderBy('name')->get();
        $search  = '';
        $filter  = 'Semua';

        return view('advokat.clients', compact('clients', 'client', 'search', 'filter'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // JADWAL ADVOKAT (READ-ONLY)
    // ─────────────────────────────────────────────────────────────────────────
    public function schedule(Request $request)
    {
        $lawyer = Auth::user();
        $now    = Carbon::now('Asia/Jakarta');

        // Parameter view: bulanan, mingguan, agenda (default: bulanan)
        $view = strtolower($request->query('view', 'bulanan'));
        if (!in_array($view, ['bulanan', 'mingguan', 'agenda'])) {
            $view = 'bulanan';
        }

        // Parameter bulan (format YYYY-MM, default: bulan berjalan)
        $monthParam = $request->query('month');
        if ($monthParam && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthParam)) {
            try {
                $targetDate = Carbon::createFromFormat('Y-m', $monthParam, 'Asia/Jakarta')->startOfMonth();
            } catch (\Throwable $e) {
                $targetDate = $now->copy()->startOfMonth();
            }
        } else {
            $targetDate = $now->copy()->startOfMonth();
        }

        $currentMonth = $targetDate->format('Y-m');

        // Query berdasarkan view yang dipilih
        if ($view === 'mingguan') {
            // Rentang mingguan: Minggu ke Sabtu berdasarkan targetDate / week param
            $weekStart = $targetDate->copy()->startOfWeek(Carbon::SUNDAY);
            $weekEnd   = $targetDate->copy()->endOfWeek(Carbon::SATURDAY);

            $schedules = Schedule::where('lawyer_id', $lawyer->id)
                ->whereBetween('start_at', [$weekStart, $weekEnd])
                ->with(['client', 'case', 'consultation'])
                ->orderBy('start_at', 'asc')
                ->get();
        } elseif ($view === 'agenda') {
            // Rentang agenda: dari awal bulan hingga 2 bulan ke depan, limit 50, kronologis
            $agendaStart = $targetDate->copy()->startOfMonth();
            $agendaEnd   = $targetDate->copy()->endOfMonth()->addMonths(2);

            $schedules = Schedule::where('lawyer_id', $lawyer->id)
                ->whereBetween('start_at', [$agendaStart, $agendaEnd])
                ->with(['client', 'case', 'consultation'])
                ->orderBy('start_at', 'asc')
                ->limit(50)
                ->get();
        } else {
            // Bulanan (default): Rentang hari pertama kalender (Minggu) sampai hari terakhir (Sabtu)
            $monthStart = $targetDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
            $monthEnd   = $targetDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

            $schedules = Schedule::where('lawyer_id', $lawyer->id)
                ->whereBetween('start_at', [$monthStart, $monthEnd])
                ->with(['client', 'case', 'consultation'])
                ->orderBy('start_at', 'asc')
                ->get();
        }

        // Query Jadwal Mendatang (khusus status 'Aktif' dan start_at >= hari ini, limit 5)
        $upcomingSchedules = Schedule::where('lawyer_id', $lawyer->id)
            ->where('status', 'Aktif')
            ->where('start_at', '>=', $now->copy()->startOfDay())
            ->with(['client', 'case', 'consultation'])
            ->orderBy('start_at', 'asc')
            ->limit(5)
            ->get();

        // Parameter tanggal terpilih opsional
        $selectedDate = null;
        $selectedDateParam = $request->query('date');
        if ($selectedDateParam && preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $selectedDateParam)) {
            $selectedDate = $selectedDateParam;
        }

        // Respon JSON jika diminta AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'           => true,
                'view'              => $view,
                'month'             => $currentMonth,
                'schedules'         => $schedules,
                'upcomingSchedules' => $upcomingSchedules,
            ]);
        }

        return view('advokat.schedule', compact(
            'lawyer',
            'view',
            'currentMonth',
            'targetDate',
            'selectedDate',
            'schedules',
            'upcomingSchedules'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PEMBERITAHUAN / NOTIFIKASI ADVOKAT
    // ─────────────────────────────────────────────────────────────────────────

    public function notifications(Request $request)
    {
        $lawyer = Auth::user();
        $filter = $request->query('filter', 'all');

        $query = $lawyer->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount   = $lawyer->unreadNotifications()->count();
        $totalCount    = $lawyer->notifications()->count();

        return view('advokat.notifications', compact('notifications', 'filter', 'unreadCount', 'totalCount'));
    }

    public function openNotification($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $actionUrl = $notification->data['action_url'] ?? route('advokat.notifications');
        return redirect($actionUrl);
    }

    public function markNotificationAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi berhasil ditandai sudah dibaca.');
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KOLABORASI DOKUMEN PERKARA & MANAJEMEN TIM ADVOKAT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Ruang Kerja Kolaborasi Dokumen Perkara
     */
    public function caseCollaboration(Request $request, $id)
    {
        $lawyer = Auth::user();
        $case   = LegalCase::with([
            'client',
            'lawyer',
            'teamMembers',
            'caseLawyers.lawyer',
            'caseLawyers.assignedByUser',
        ])->findOrFail($id);

        if (!$case->isLawyerAuthorized($lawyer)) {
            abort(403, 'Akses ditolak. Anda bukan bagian dari tim advokat yang berwenang menangani perkara ini.');
        }

        // Query Dokumen Perkara
        $docQuery = Document::where('case_id', $case->id)
            ->with([
                'uploader',
                'finalizer',
                'documentVersions.uploader',
                'comments.user',
                'comments.version',
            ])
            ->orderBy('updated_at', 'desc');

        // Filter status kolaborasi
        $statusFilter = $request->query('status', 'all');
        if ($statusFilter && $statusFilter !== 'all') {
            $docQuery->where('collaboration_status', $statusFilter);
        }

        // Filter tipe dokumen
        $typeFilter = $request->query('type', 'all');
        if ($typeFilter && $typeFilter !== 'all') {
            $docQuery->where('document_type', $typeFilter);
        }

        // Filter visibilitas internal / eksternal
        $visibilityFilter = $request->query('visibility', 'all');
        if ($visibilityFilter === 'internal') {
            $docQuery->where('is_internal', true);
        } elseif ($visibilityFilter === 'klien') {
            $docQuery->where('is_internal', false);
        }

        // Pencarian dokumen
        $search = $request->query('q');
        if ($search) {
            $docQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $documents = $docQuery->get();

        // Dokumen aktif untuk panel detail / inspeksi
        $activeDocId = $request->query('doc_id', $documents->first()?->id);
        $activeDocument = null;
        if ($activeDocId) {
            $activeDocument = $documents->firstWhere('id', $activeDocId);
        }

        // Statistik dokumen kolaborasi
        $allDocs = Document::where('case_id', $case->id)->get();
        $stats = [
            'total'      => $allDocs->count(),
            'draf'       => $allDocs->where('collaboration_status', 'Draf')->count(),
            'revisi'     => $allDocs->where('collaboration_status', 'Dalam Revisi')->count(),
            'review'     => $allDocs->where('collaboration_status', 'Menunggu Persetujuan')->count(),
            'final'      => $allDocs->where('collaboration_status', 'Final')->count(),
            'internal'   => $allDocs->where('is_internal', true)->count(),
            'shared'     => $allDocs->where('is_internal', false)->count(),
        ];

        // Daftar advokat yang dapat ditambahkan ke tim perkara (hanya untuk lead lawyer/admin)
        $existingTeamIds = array_merge([$case->lawyer_id], $case->caseLawyers->pluck('user_id')->toArray());
        $availableLawyers = User::where('role', 'advokat')
            ->whereNotIn('id', $existingTeamIds)
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        // Riwayat aktivitas perkara
        $auditLogs = DocumentAuditLog::where('case_id', $case->id)
            ->with(['user', 'document'])
            ->orderBy('created_at', 'desc')
            ->take(25)
            ->get();

        $isLead = $case->isLeadLawyer($lawyer);

        return view('advokat.collaboration', compact(
            'case',
            'documents',
            'activeDocument',
            'stats',
            'availableLawyers',
            'auditLogs',
            'isLead',
            'statusFilter',
            'typeFilter',
            'visibilityFilter',
            'search'
        ));
    }

    /**
     * Unggah Dokumen Kolaborasi Baru
     */
    public function storeCollaborationDocument(Request $request, $caseId)
    {
        $lawyer = Auth::user();
        $case   = LegalCase::findOrFail($caseId);

        if (!$case->isLawyerAuthorized($lawyer)) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'name'          => 'required|string|max:255',
            'document_type' => 'required|string|max:100',
            'description'   => 'nullable|string|max:1000',
            'revision_notes'=> 'nullable|string|max:1000',
            'is_internal'   => 'required|in:1,0',
            'file'          => [
                'required',
                'file',
                'max:10240', // 10MB
                function ($attribute, $value, $fail) {
                    if (!$value || !($value instanceof \Illuminate\Http\UploadedFile)) return;
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format file tidak diizinkan. Gunakan PDF, DOC, DOCX, JPG, JPEG, PNG, XLS, atau XLSX.');
                    }
                },
            ],
        ], [
            'name.required'          => 'Nama dokumen hukum wajib diisi.',
            'document_type.required' => 'Jenis dokumen hukum wajib dipilih.',
            'file.required'          => 'Berkas file dokumen wajib diunggah.',
            'file.max'               => 'Ukuran berkas maksimal 10 MB.',
        ]);

        return DB::transaction(function () use ($request, $case, $lawyer) {
            $file       = $request->file('file');
            $ext        = strtolower($file->getClientOriginalExtension());
            $uniqueName = (string) Str::uuid() . '.' . $ext;
            $storedPath = $file->storeAs("documents/{$case->id}", $uniqueName, 'local');

            $mimeType = null;
            try {
                $mimeType = $file->getMimeType();
            } catch (\Throwable $e) {
                $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
            }

            $isInternal = (bool) $request->input('is_internal', 1);

            $document = Document::create([
                'case_id'              => $case->id,
                'client_id'            => $case->client_id,
                'lawyer_id'            => $case->lawyer_id,
                'uploaded_by'          => $lawyer->id,
                'name'                 => $request->name,
                'document_type'        => $request->document_type,
                'description'          => $request->description,
                'file_path'            => $storedPath,
                'original_filename'    => $file->getClientOriginalName(),
                'mime_type'            => $mimeType,
                'file_size'            => $file->getSize(),
                'version'              => 1,
                'status'               => 'Menunggu Verifikasi',
                'collaboration_status' => 'Draf',
                'is_internal'          => $isInternal,
                'is_from_lawyer'       => true,
            ]);

            DocumentVersion::create([
                'document_id'       => $document->id,
                'version_number'    => 1,
                'file_path'         => $storedPath,
                'original_filename' => $file->getClientOriginalName(),
                'file_size'         => $file->getSize(),
                'mime_type'         => $mimeType,
                'uploaded_by'       => $lawyer->id,
                'revision_notes'    => $request->revision_notes ?: 'Draf versi awal (v1).',
                'status'            => 'Draf',
                'is_final'          => false,
            ]);

            $visibilityText = $isInternal ? 'Internal Tim Advokat' : 'Terbuka / Dibagikan ke Klien';
            DocumentAuditLog::record(
                $document->id,
                $case->id,
                $lawyer->id,
                'upload',
                "Advokat ({$lawyer->name}) mengunggah dokumen baru '{$document->name}' (v1) [{$visibilityText}]."
            );

            // Notifikasi ke seluruh anggota tim perkara
            $this->notifyAdvocateTeam(
                $case,
                $lawyer,
                'new_document',
                "Advokat {$lawyer->name} mengunggah dokumen baru '{$document->name}' (v1) pada perkara {$case->case_number}.",
                $document
            );

            return redirect()->route('advokat.cases.collaboration', ['id' => $case->id, 'doc_id' => $document->id])
                ->with('success', "Dokumen '{$document->name}' berhasil diunggah sebagai Draf v1.");
        });
    }

    /**
     * Unggah Versi Revisi Baru Dokumen
     */
    public function uploadDocumentVersion(Request $request, $documentId)
    {
        $lawyer   = Auth::user();
        $document = Document::with('case')->findOrFail($documentId);
        $case     = $document->case;

        if (!$case || !$case->isLawyerAuthorized($lawyer)) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'revision_notes' => 'required|string|min:4|max:1000',
            'status'         => 'required|in:Draf,Dalam Revisi,Menunggu Persetujuan',
            'file'           => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (!$value || !($value instanceof \Illuminate\Http\UploadedFile)) return;
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format file tidak diizinkan. Gunakan PDF, DOC, DOCX, JPG, JPEG, PNG, XLS, atau XLSX.');
                    }
                },
            ],
        ], [
            'revision_notes.required' => 'Catatan revisi wajib diisi agar perubahan versi terdokumentasi dengan baik.',
            'revision_notes.min'      => 'Catatan revisi minimal 4 karakter.',
            'file.required'           => 'Berkas revisi wajib diunggah.',
        ]);

        return DB::transaction(function () use ($request, $document, $case, $lawyer) {
            $file       = $request->file('file');
            $ext        = strtolower($file->getClientOriginalExtension());
            $uniqueName = (string) Str::uuid() . '.' . $ext;
            $storedPath = $file->storeAs("documents/{$case->id}", $uniqueName, 'local');

            $mimeType = null;
            try {
                $mimeType = $file->getMimeType();
            } catch (\Throwable $e) {
                $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
            }

            $currentMaxVer = (int) ($document->documentVersions()->max('version_number') ?: $document->version ?: 1);
            $newVerNumber  = $currentMaxVer + 1;

            $version = DocumentVersion::create([
                'document_id'       => $document->id,
                'version_number'    => $newVerNumber,
                'file_path'         => $storedPath,
                'original_filename' => $file->getClientOriginalName(),
                'file_size'         => $file->getSize(),
                'mime_type'         => $mimeType,
                'uploaded_by'       => $lawyer->id,
                'revision_notes'    => $request->revision_notes,
                'status'            => $request->status,
                'is_final'          => false,
            ]);

            // Update master pointer dokumen ke versi terbaru
            $document->update([
                'file_path'            => $storedPath,
                'original_filename'    => $file->getClientOriginalName(),
                'file_size'            => $file->getSize(),
                'mime_type'            => $mimeType,
                'version'              => $newVerNumber,
                'collaboration_status' => $request->status,
                'uploaded_by'          => $lawyer->id,
            ]);

            DocumentAuditLog::record(
                $document->id,
                $case->id,
                $lawyer->id,
                'new_version',
                "Advokat ({$lawyer->name}) mengunggah revisi versi baru (v{$newVerNumber}) untuk '{$document->name}'. Catatan: \"{$request->revision_notes}\""
            );

            $this->notifyAdvocateTeam(
                $case,
                $lawyer,
                'new_version',
                "Advokat {$lawyer->name} mengunggah versi baru v{$newVerNumber} untuk dokumen '{$document->name}'.",
                $document
            );

            return redirect()->route('advokat.cases.collaboration', ['id' => $case->id, 'doc_id' => $document->id])
                ->with('success', "Versi baru (v{$newVerNumber}) berhasil diunggah dengan status '{$request->status}'.");
        });
    }

    /**
     * Tambah Catatan Revisi / Komentar pada Dokumen
     */
    public function addDocumentComment(Request $request, $documentId)
    {
        $lawyer   = Auth::user();
        $document = Document::with('case')->findOrFail($documentId);
        $case     = $document->case;

        if (!$case || !$case->isLawyerAuthorized($lawyer)) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'comment'             => 'required|string|min:2|max:2000',
            'document_version_id' => 'nullable|exists:document_versions,id',
        ], [
            'comment.required' => 'Isi komentar atau catatan revisi tidak boleh kosong.',
            'comment.min'      => 'Komentar minimal 2 karakter.',
        ]);

        $comment = DocumentComment::create([
            'document_id'         => $document->id,
            'document_version_id' => $request->document_version_id,
            'user_id'             => $lawyer->id,
            'comment'             => $request->comment,
        ]);

        $verText = $comment->version ? " pada v{$comment->version->version_number}" : "";
        DocumentAuditLog::record(
            $document->id,
            $case->id,
            $lawyer->id,
            'comment',
            "Advokat ({$lawyer->name}) menambahkan catatan{$verText}: \"{$request->comment}\""
        );

        $this->notifyAdvocateTeam(
            $case,
            $lawyer,
            'new_comment',
            "Advokat {$lawyer->name} memberikan komentar pada dokumen '{$document->name}'{$verText}.",
            $document
        );

        return redirect()->route('advokat.cases.collaboration', ['id' => $case->id, 'doc_id' => $document->id])
            ->with('success', 'Catatan revisi berhasil disimpan.');
    }

    /**
     * Perbarui Status Kolaborasi Dokumen
     */
    public function updateDocumentCollaborationStatus(Request $request, $documentId)
    {
        $lawyer   = Auth::user();
        $document = Document::with('case')->findOrFail($documentId);
        $case     = $document->case;

        if (!$case || !$case->isLawyerAuthorized($lawyer)) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'status' => 'required|in:Draf,Dalam Revisi,Menunggu Persetujuan',
        ]);

        $oldStatus = $document->collaboration_status;
        $newStatus = $request->status;

        $document->update(['collaboration_status' => $newStatus]);

        // Update juga status pada versi terakhir jika belum final
        $latestVersion = $document->documentVersions()->orderBy('version_number', 'desc')->first();
        if ($latestVersion && !$latestVersion->is_final) {
            $latestVersion->update(['status' => $newStatus]);
        }

        DocumentAuditLog::record(
            $document->id,
            $case->id,
            $lawyer->id,
            'status_change',
            "Advokat ({$lawyer->name}) mengubah status dokumen '{$document->name}' dari '{$oldStatus}' menjadi '{$newStatus}'."
        );

        $this->notifyAdvocateTeam(
            $case,
            $lawyer,
            'status_changed',
            "Status dokumen '{$document->name}' diubah menjadi '{$newStatus}' oleh {$lawyer->name}.",
            $document
        );

        return redirect()->route('advokat.cases.collaboration', ['id' => $case->id, 'doc_id' => $document->id])
            ->with('success', "Status dokumen berhasil diubah menjadi '{$newStatus}'.");
    }

    /**
     * Menetapkan Dokumen sebagai Versi Final
     */
    public function finalizeDocument(Request $request, $documentId)
    {
        $lawyer   = Auth::user();
        $document = Document::with('case')->findOrFail($documentId);
        $case     = $document->case;

        if (!$case || !$case->isLawyerAuthorized($lawyer)) {
            abort(403, 'Akses ditolak.');
        }

        $latestVersion = $document->documentVersions()->orderBy('version_number', 'desc')->first();

        if (!$latestVersion) {
            return redirect()->back()->with('error', 'Dokumen belum memiliki berkas versi fisik untuk difinalisasi.');
        }

        return DB::transaction(function () use ($document, $case, $lawyer, $latestVersion) {
            $latestVersion->update([
                'status'   => 'Final',
                'is_final' => true,
            ]);

            $document->update([
                'collaboration_status' => 'Final',
                'status'               => 'Terverifikasi',
                'finalized_by'         => $lawyer->id,
                'finalized_at'         => now(),
            ]);

            DocumentAuditLog::record(
                $document->id,
                $case->id,
                $lawyer->id,
                'finalized',
                "Advokat ({$lawyer->name}) menetapkan versi v{$latestVersion->version_number} dari dokumen '{$document->name}' sebagai Final."
            );

            $this->notifyAdvocateTeam(
                $case,
                $lawyer,
                'finalized',
                "Dokumen '{$document->name}' (v{$latestVersion->version_number}) telah ditetapkan sebagai FINAL oleh {$lawyer->name}.",
                $document
            );

            return redirect()->route('advokat.cases.collaboration', ['id' => $case->id, 'doc_id' => $document->id])
                ->with('success', "Versi v{$latestVersion->version_number} dari dokumen '{$document->name}' berhasil ditetapkan sebagai Versi Final.");
        });
    }

    /**
     * Tambah Anggota Tim Advokat pada Perkara
     */
    public function addTeamMember(Request $request, $caseId)
    {
        $lawyer = Auth::user();
        $case   = LegalCase::findOrFail($caseId);

        if (!$case->isLeadLawyer($lawyer)) {
            abort(403, 'Hanya Ketua Tim / Advokat Penanggung Jawab perkara yang dapat mengelola anggota tim.');
        }

        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'role_in_case' => 'nullable|string|max:100',
        ], [
            'user_id.required' => 'Pilih advokat yang akan ditugaskan ke dalam tim perkara.',
        ]);

        $targetAdvokat = User::where('id', $request->user_id)->where('role', 'advokat')->firstOrFail();

        if ($targetAdvokat->id === $case->lawyer_id) {
            return redirect()->back()->with('error', 'Advokat ini adalah penanggung jawab utama perkara.');
        }

        $alreadyMember = CaseLawyer::where('case_id', $case->id)->where('user_id', $targetAdvokat->id)->exists();
        if ($alreadyMember) {
            return redirect()->back()->with('error', 'Advokat ini sudah terdaftar dalam tim perkara.');
        }

        $roleInCase = $request->role_in_case ?: 'Anggota Tim';

        CaseLawyer::create([
            'case_id'      => $case->id,
            'user_id'      => $targetAdvokat->id,
            'role_in_case' => $roleInCase,
            'assigned_by'  => $lawyer->id,
        ]);

        DocumentAuditLog::record(
            null,
            $case->id,
            $lawyer->id,
            'team_member_added',
            "Ketua Tim ({$lawyer->name}) menugaskan {$targetAdvokat->name} ({$roleInCase}) ke dalam tim penanganan perkara."
        );

        // Kirim notifikasi kepada advokat baru
        $targetAdvokat->notify(new DocumentCollaborationNotification(
            'team_joined',
            "Anda telah ditugaskan sebagai {$roleInCase} pada perkara {$case->case_number} oleh {$lawyer->name}.",
            null,
            $case,
            $lawyer
        ));

        return redirect()->route('advokat.cases.collaboration', $case->id)
            ->with('success', "Advokat {$targetAdvokat->name} berhasil ditambahkan sebagai {$roleInCase}.");
    }

    /**
     * Hapus / Cabut Hak Akses Advokat dari Tim Perkara
     */
    public function removeTeamMember(Request $request, $caseId, $userId)
    {
        $lawyer = Auth::user();
        $case   = LegalCase::findOrFail($caseId);

        if (!$case->isLeadLawyer($lawyer)) {
            abort(403, 'Hanya Ketua Tim / Advokat Penanggung Jawab perkara yang dapat mencabut anggota tim.');
        }

        if ((int) $userId === (int) $case->lawyer_id) {
            return redirect()->back()->with('error', 'Penanggung jawab utama perkara tidak dapat dihapus.');
        }

        $membership = CaseLawyer::where('case_id', $case->id)->where('user_id', $userId)->firstOrFail();
        $removedUser = User::find($userId);

        $membership->delete();

        DocumentAuditLog::record(
            null,
            $case->id,
            $lawyer->id,
            'team_member_removed',
            "Ketua Tim ({$lawyer->name}) mencabut penugasan advokat {$removedUser?->name} dari tim perkara."
        );

        if ($removedUser) {
            $removedUser->notify(new DocumentCollaborationNotification(
                'team_removed',
                "Penugasan Anda pada perkara {$case->case_number} telah dicabut oleh {$lawyer->name}.",
                null,
                $case,
                $lawyer
            ));
        }

        return redirect()->route('advokat.cases.collaboration', $case->id)
            ->with('success', "Penugasan advokat {$removedUser?->name} berhasil dicabut.");
    }

    /**
     * Helper privat kirim notifikasi ke seluruh tim advokat perkara
     */
    private function notifyAdvocateTeam(LegalCase $case, User $actor, string $eventType, string $messageText, ?Document $document = null): void
    {
        try {
            $teamIds = $case->allLawyerIds();
            // Kecualikan actor sendiri
            $recipientIds = array_diff($teamIds, [$actor->id]);

            if (empty($recipientIds)) {
                return;
            }

            $recipients = User::whereIn('id', $recipientIds)->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new DocumentCollaborationNotification(
                    $eventType,
                    $messageText,
                    $document,
                    $case,
                    $actor
                ));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim notifikasi kolaborasi dokumen: " . $e->getMessage());
        }
    }
}
