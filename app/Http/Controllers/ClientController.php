<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Schedule;
use App\Models\User;

class ClientController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $now = Carbon::now('Asia/Jakarta');
        $client = Auth::user();

        // Greeting
        $hour = $now->hour;

        if ($hour < 11) {
            $greeting = 'Pagi';
        } elseif ($hour < 15) {
            $greeting = 'Siang';
        } elseif ($hour < 18) {
            $greeting = 'Sore';
        } else {
            $greeting = 'Malam';
        }

        // Nama depan
        $firstName = explode(' ', trim($client->name))[0];

        // Sapaan
        $salutation = 'Bapak';

        // Tanggal Indonesia
        $date = $now->locale('id')->isoFormat('dddd, D MMMM YYYY');

        // ─────────────────────────────────────────────────────────────────────
        // STATISTIK DASHBOARD
        // ─────────────────────────────────────────────────────────────────────

        $activeConsultations = Consultation::where('client_id', $client->id)
            ->whereIn('status', ['Menunggu', 'Dijadwalkan'])
            ->count();

        $activeCases = LegalCase::where('client_id', $client->id)
            ->whereNotIn('status', ['Selesai', 'Dibatalkan'])
            ->count();

        $pendingDocuments = Document::where('client_id', $client->id)
            ->where('status', 'Belum Diunggah')
            ->count();

        // Cari jadwal konsultasi terdekat
        $nearestSchedule = Consultation::where('client_id', $client->id)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', $now)
            ->whereIn('status', ['Dijadwalkan'])
            ->orderBy('scheduled_at', 'asc')
            ->value('scheduled_at');

        if ($nearestSchedule) {
            $nearestSchedule = Carbon::parse($nearestSchedule)
                ->locale('id')
                ->isoFormat('D MMM YYYY, HH:mm');
        } else {
            $nearestSchedule = '-';
        }

        $stats = [
            'active_consultations' => $activeConsultations,
            'active_cases'         => $activeCases,
            'pending_documents'    => $pendingDocuments,
            'nearest_schedule'     => $nearestSchedule,
        ];

        // ─────────────────────────────────────────────────────────────────────
        // PERKARA AKTIF TERBARU
        // ─────────────────────────────────────────────────────────────────────

        $latestCase = LegalCase::where('client_id', $client->id)
            ->whereNotIn('status', ['Selesai', 'Dibatalkan'])
            ->with([
                'lawyer',
                'progress' => function ($query) {
                    $query->orderBy('progress_date', 'desc');
                }
            ])
            ->latest('created_at')
            ->first();

        return view('klien.dashboard', compact(
            'greeting',
            'firstName',
            'salutation',
            'date',
            'stats',
            'latestCase'
        ));
    }


    // ─────────────────────────────────────────────────────────────────────────
    // KONSULTASI
    // ─────────────────────────────────────────────────────────────────────────

    public function consultations(Request $request)
    {
        $client = Auth::user();

        $consultations = Consultation::where('client_id', $client->id)
            ->with(['lawyer', 'conversation'])
            ->orderBy('created_at', 'desc')
            ->get();

        $activeId = $request->query('id');

        // Kalau tidak ada ID dari URL, gunakan konsultasi terbaru
        if (!$activeId) {
            $activeId = $consultations->first()?->id;
        }

        $consultation = null;

        if ($activeId) {
            $consultation = Consultation::with(['lawyer', 'conversation'])
                ->where('client_id', $client->id)
                ->find($activeId);
        }

        return view('klien.consultations', compact(
            'consultations',
            'consultation'
        ));
    }


    // ─────────────────────────────────────────────────────────────────────────
    // AJUKAN KONSULTASI
    // ─────────────────────────────────────────────────────────────────────────

    public function storeConsultation(Request $request)
    {
        $validated = $request->validate([
            'problem_type' => 'required|string|max:100',
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
        ]);

        $client = Auth::user();

        Consultation::create([
            'client_id'    => $client->id,
            'lawyer_id'    => null,
            'problem_type' => $validated['problem_type'],
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'status'       => 'Menunggu',
        ]);

        return redirect()
            ->route('klien.consultations')
            ->with(
                'success',
                'Konsultasi berhasil diajukan! Menunggu peninjauan Admin dan penetapan Advokat.'
            );
    }


    // ─────────────────────────────────────────────────────────────────────────
    // DETAIL KONSULTASI
    // ─────────────────────────────────────────────────────────────────────────

    public function consultationDetail($id)
    {
        $client = Auth::user();

        $consultation = Consultation::with(['lawyer', 'conversation'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        $consultations = Consultation::where('client_id', $client->id)
            ->with(['lawyer', 'conversation'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('klien.consultations', compact(
            'consultations',
            'consultation'
        ));
    }


    // ─────────────────────────────────────────────────────────────────────────
    // PERKARA
    // ─────────────────────────────────────────────────────────────────────────

    public function cases(Request $request)
    {
        $client = Auth::user();

        $cases = LegalCase::where('client_id', $client->id)
            ->with('lawyer')
            ->orderBy('started_at', 'desc')
            ->get();

        $activeId = $request->query('id');

        if (!$activeId) {
            $activeId = $cases->first()?->id;
        }

        $case = null;

        if ($activeId) {
            $case = LegalCase::with([
                'lawyer',
                'progress',
                'documents' => function ($q) {
                    $q->with('uploader', 'verifier')->orderBy('created_at', 'desc');
                },
                'documentRequests' => function ($q) {
                    $q->with('requester')->orderBy('created_at', 'desc');
                },
            ])
                ->where('client_id', $client->id)
                ->find($activeId);
        }

        return view('klien.cases', compact(
            'cases',
            'case'
        ));
    }


    // ─────────────────────────────────────────────────────────────────────────
    // DETAIL PERKARA
    // ─────────────────────────────────────────────────────────────────────────

    public function caseDetail($id)
    {
        $client = Auth::user();

        $case = LegalCase::with([
            'lawyer',
            'progress',
            'documents' => function ($q) {
                $q->with('uploader', 'verifier')->orderBy('created_at', 'desc');
            },
            'documentRequests' => function ($q) {
                $q->with('requester')->orderBy('created_at', 'desc');
            },
        ])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        $cases = LegalCase::where('client_id', $client->id)
            ->with('lawyer')
            ->orderBy('started_at', 'desc')
            ->get();

        return view('klien.cases', compact(
            'cases',
            'case'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DOKUMEN SAYA (HALAMAN MANAJEMEN DOKUMEN KLIEN)
    // ─────────────────────────────────────────────────────────────────────────

    public function documents(Request $request)
    {
        $client = Auth::user();

        $cases = LegalCase::where('client_id', $client->id)
            ->with('lawyer')
            ->orderBy('started_at', 'desc')
            ->get();

        $caseIds = $cases->pluck('id')->toArray();

        $selectedCaseId = $request->query('case_id');
        $statusFilter   = $request->query('status', 'all');
        $search         = $request->query('q');

        $query = Document::where(function ($q) use ($client, $caseIds) {
            $q->where('client_id', $client->id)
              ->orWhereIn('case_id', $caseIds);
        })->with(['case.lawyer', 'uploader', 'verifier'])
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
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $documents = $query->get();

        // Permintaan dokumen yang masih pending dari advokat
        $pendingRequests = DocumentRequest::where('client_id', $client->id)
            ->where('status', 'Menunggu Upload')
            ->with(['case.lawyer', 'requester'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung statistik dokumen klien
        $allClientDocs = Document::where(function ($q) use ($client, $caseIds) {
            $q->where('client_id', $client->id)
              ->orWhereIn('case_id', $caseIds);
        })->get();

        $stats = [
            'total'        => $allClientDocs->count(),
            'verified'     => $allClientDocs->filter(fn($d) => in_array($d->status, ['Terverifikasi', 'Sudah Diterima']))->count(),
            'pending'      => $allClientDocs->filter(fn($d) => in_array($d->status, ['Menunggu Verifikasi', 'Menunggu Pemeriksaan', 'Belum Diunggah']))->count(),
            'rejected'     => $allClientDocs->filter(fn($d) => in_array($d->status, ['Ditolak', 'Perlu Diperbaiki']))->count(),
            'requests'     => $pendingRequests->count(),
        ];

        return view('klien.documents', compact(
            'documents',
            'cases',
            'pendingRequests',
            'stats',
            'selectedCaseId',
            'statusFilter',
            'search'
        ));
    }


    // ─────────────────────────────────────────────────────────────────────────
    // UPLOAD DOKUMEN PERKARA
    // ─────────────────────────────────────────────────────────────────────────

    public function uploadCaseDocument(Request $request, $caseId)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'document_type'       => 'required|string|max:100',
            'description'         => 'nullable|string|max:1000',
            'document_request_id' => 'nullable|exists:document_requests,id',
            'file'                => [
                'required',
                'file',
                'max:10240', // 10MB
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
            'file.required'    => 'Berkas dokumen wajib diunggah.',
            'file.max'         => 'Ukuran file terlalu besar. Maksimum ukuran berkas adalah 10 MB.',
            'name.required'    => 'Nama atau judul dokumen wajib diisi.',
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
        ]);

        $client = Auth::user();
        $case   = LegalCase::where('client_id', $client->id)->findOrFail($caseId);

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
            'case_id'             => $case->id,
            'client_id'           => $client->id,
            'lawyer_id'           => $case->lawyer_id,
            'document_request_id' => $request->document_request_id,
            'uploaded_by'         => $client->id,
            'name'                => $request->name,
            'document_type'       => $request->document_type,
            'description'         => $request->description,
            'file_path'           => $storedPath,
            'original_filename'   => $file->getClientOriginalName(),
            'mime_type'           => $mimeType,
            'file_size'           => $file->getSize(),
            'status'              => 'Menunggu Verifikasi',
            'is_from_lawyer'      => false,
        ]);

        // If fulfilling a document request
        if ($request->filled('document_request_id')) {
            $docReq = \App\Models\DocumentRequest::where('case_id', $case->id)
                ->where('client_id', $client->id)
                ->find($request->document_request_id);
            if ($docReq) {
                $docReq->update(['status' => 'Sudah Diupload']);
            }
        }

        // Audit Log
        \App\Models\DocumentAuditLog::record(
            $document->id,
            $case->id,
            $client->id,
            'upload',
            "Klien ({$client->name}) mengunggah dokumen '{$document->name}' ({$document->document_type})."
        );

        // Notify Lawyer
        if ($case->lawyer) {
            $case->lawyer->notify(new \App\Notifications\NewDocumentUploadedNotification($document));
        }

        return redirect()
            ->route('klien.cases.show', $case->id)
            ->with('success', 'Dokumen berhasil diunggah dan sedang menunggu verifikasi dari Advokat.');
    }

    /**
     * Upload ulang dokumen yang sebelumnya ditolak
     */
    public function reuploadDocument(Request $request, $id)
    {
        $request->validate([
            'file' => [
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
            'description' => 'nullable|string|max:1000',
        ], [
            'file.required'  => 'Berkas dokumen pengganti wajib diunggah.',
            'file.max'       => 'Ukuran file terlalu besar. Maksimum ukuran berkas adalah 10 MB.',
        ]);

        $client = Auth::user();
        $oldDoc = Document::with('case')->where('client_id', $client->id)->findOrFail($id);

        if (!in_array($oldDoc->status, ['Ditolak', 'Perlu Diperbaiki'])) {
            return redirect()->back()->with('error', 'Hanya dokumen berstatus ditolak yang dapat diunggah ulang.');
        }

        $case       = $oldDoc->case;
        $file       = $request->file('file');
        $ext        = strtolower($file->getClientOriginalExtension());
        $uniqueName = (string) \Illuminate\Support\Str::uuid() . '.' . $ext;
        $storedPath = $file->storeAs("documents/{$case->id}", $uniqueName, 'local');

        $rootParentId = $oldDoc->parent_id ?: $oldDoc->id;
        $latestVersion = Document::where('id', $rootParentId)
            ->orWhere('parent_id', $rootParentId)
            ->max('version') ?? 1;

        $mimeType = null;
        try {
            $mimeType = $file->getMimeType();
        } catch (\Throwable $e) {
            $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
        }

        $newDoc = Document::create([
            'case_id'             => $case->id,
            'client_id'           => $client->id,
            'lawyer_id'           => $oldDoc->lawyer_id,
            'document_request_id' => $oldDoc->document_request_id,
            'uploaded_by'         => $client->id,
            'parent_id'           => $rootParentId,
            'version'             => $latestVersion + 1,
            'name'                => $oldDoc->name,
            'document_type'       => $oldDoc->document_type,
            'description'         => $request->description ?: $oldDoc->description,
            'file_path'           => $storedPath,
            'original_filename'   => $file->getClientOriginalName(),
            'mime_type'           => $mimeType,
            'file_size'           => $file->getSize(),
            'status'              => 'Menunggu Verifikasi',
            'is_from_lawyer'      => false,
        ]);

        // Audit Log
        \App\Models\DocumentAuditLog::record(
            $newDoc->id,
            $case->id,
            $client->id,
            'reupload',
            "Klien ({$client->name}) mengunggah ulang revisi dokumen '{$newDoc->name}' (Versi {$newDoc->version}) menggantikan versi sebelumnya yang ditolak dengan alasan: '{$oldDoc->rejection_reason}'."
        );

        // Notify Lawyer
        if ($case && $case->lawyer) {
            $case->lawyer->notify(new \App\Notifications\NewDocumentUploadedNotification($newDoc));
        }

        return redirect()
            ->route('klien.cases.show', $case->id)
            ->with('success', 'Dokumen revisi berhasil diunggah ulang dan sedang menunggu verifikasi.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // JADWAL KLIEN (READ-ONLY)
    // ─────────────────────────────────────────────────────────────────────────
    public function schedule(Request $request)
    {
        $client = Auth::user();
        $now    = Carbon::now('Asia/Jakarta');

        // Parameter view: bulanan, mingguan, agenda (default: bulanan)
        $view = strtolower($request->query('view', 'bulanan'));
        if (!in_array($view, ['bulanan', 'mingguan', 'agenda'])) {
            $view = 'bulanan';
        }

        // Parameter bulan (format YYYY-MM, default: bulan berjalan)
        $monthParam = $request->query('month');
        if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            try {
                $targetDate = Carbon::createFromFormat('Y-m', $monthParam, 'Asia/Jakarta')->startOfMonth();
            } catch (\Throwable $e) {
                $targetDate = $now->copy()->startOfMonth();
            }
        } else {
            $targetDate = $now->copy()->startOfMonth();
        }

        $currentMonth = $targetDate->format('Y-m');

        // Rentang tanggal kalender bulanan (dimulai hari Minggu, berakhir hari Sabtu)
        $monthStart = $targetDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $monthEnd   = $targetDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // Query jadwal bulan aktif untuk Klien ini (authorization: client_id = Auth::id())
        $schedules = Schedule::where('client_id', $client->id)
            ->whereBetween('start_at', [$monthStart, $monthEnd])
            ->with(['lawyer', 'case', 'consultation'])
            ->orderBy('start_at', 'asc')
            ->get();

        // Query Jadwal Mendatang (minimal hari ini, limit 5 terdekat, non-dibatalkan)
        $upcomingSchedules = Schedule::where('client_id', $client->id)
            ->where('start_at', '>=', $now->copy()->startOfDay())
            ->where('status', '!=', 'Dibatalkan')
            ->with(['lawyer', 'case', 'consultation'])
            ->orderBy('start_at', 'asc')
            ->limit(5)
            ->get();

        // Parameter tanggal terpilih opsional
        $selectedDate = null;
        $selectedDateParam = $request->query('date');
        if ($selectedDateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDateParam)) {
            $selectedDate = $selectedDateParam;
        }

        // Respon JSON untuk interaksi AJAX jika diminta
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'           => true,
                'month'             => $currentMonth,
                'schedules'         => $schedules,
                'upcomingSchedules' => $upcomingSchedules,
            ]);
        }

        return view('klien.schedule', compact(
            'client',
            'view',
            'currentMonth',
            'targetDate',
            'selectedDate',
            'schedules',
            'upcomingSchedules'
        ));
    }
}