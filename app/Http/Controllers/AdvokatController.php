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
use App\Models\Schedule;
use App\Models\User;

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
        $existingSchedule = Schedule::where('consultation_id', $consultation->id)->latest()->first();
        if ($existingSchedule) {
            $existingSchedule->update([
                'lawyer_id'   => $lawyer->id,
                'client_id'   => $consultation->client_id,
                'title'       => 'Konsultasi — ' . $consultation->client->name,
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
                'title'           => 'Konsultasi — ' . $consultation->client->name,
                'description'     => $consultation->title,
                'start_at'        => $request->scheduled_at,
                'location'        => $request->location ?? 'Kantor',
                'status'          => 'Aktif',
            ]);
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

        return redirect()->route('advokat.consultations.show', $id)
            ->with('success', 'Hasil konsultasi berhasil dicatat.');
    }

    // ─── Perkara ──────────────────────────────────────────────────────────────
    public function cases(Request $request)
    {
        $lawyer = Auth::user();
        $filter = $request->query('status', 'Semua');

        $query = LegalCase::where('lawyer_id', $lawyer->id)->with('client');
        if ($filter !== 'Semua') {
            $query->where('status', $filter);
        }
        $cases = $query->orderBy('created_at', 'desc')->get();

        $activeId = $request->query('id', $cases->first()?->id);
        $case     = LegalCase::with([
            'client',
            'lawyer',
            'progress',
            'documents' => function ($q) {
                $q->with('uploader', 'verifier')->orderBy('created_at', 'desc');
            },
            'documentRequests' => function ($q) {
                $q->with('client')->orderBy('created_at', 'desc');
            },
        ])->where('lawyer_id', $lawyer->id)->find($activeId);

        return view('advokat.cases', compact('cases', 'case', 'filter'));
    }

    public function caseDetail($id)
    {
        $lawyer = Auth::user();
        $case   = LegalCase::with([
            'client',
            'lawyer',
            'progress',
            'documents' => function ($q) {
                $q->with('uploader', 'verifier')->orderBy('created_at', 'desc');
            },
            'documentRequests' => function ($q) {
                $q->with('client')->orderBy('created_at', 'desc');
            },
        ])->where('lawyer_id', $lawyer->id)->findOrFail($id);
        $filter = 'Semua';
        $cases  = LegalCase::where('lawyer_id', $lawyer->id)
            ->with('client')->orderBy('created_at', 'desc')->get();
        return view('advokat.cases', compact('cases', 'case', 'filter'));
    }

    // ─── Manajemen Dokumen Perkara Advokat ────────────────────────────────────
    public function documents(Request $request = null)
    {
        $request = $request ?? request();
        $lawyer = Auth::user();

        $cases = LegalCase::where('lawyer_id', $lawyer->id)
            ->with('client')
            ->orderBy('started_at', 'desc')
            ->get();

        $caseIds = $cases->pluck('id')->toArray();

        $selectedCaseId = $request->query('case_id');
        $statusFilter   = $request->query('status', 'all');
        $search         = $request->query('q');

        $query = Document::where(function ($q) use ($lawyer, $caseIds) {
            $q->where('lawyer_id', $lawyer->id)
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

        CaseProgress::create([
            'case_id'       => $case->id,
            'title'         => $request->title,
            'description'   => $request->description,
            'progress_date' => $request->progress_date,
            'created_by'    => $lawyer->id,
        ]);

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
        if ($document->client) {
            $document->client->notify(new \App\Notifications\DocumentVerifiedNotification($document));
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
        if ($document->client) {
            $document->client->notify(new \App\Notifications\DocumentRejectedNotification($document, $request->rejection_reason));
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
}
