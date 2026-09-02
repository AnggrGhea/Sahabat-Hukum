<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\Document;
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
            ->with('lawyer')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeId = $request->query('id');

        // Kalau tidak ada ID dari URL, gunakan konsultasi terbaru
        if (!$activeId) {
            $activeId = $consultations->first()?->id;
        }

        $consultation = null;

        if ($activeId) {
            $consultation = Consultation::with('lawyer')
                ->where('client_id', $client->id)
                ->find($activeId);
        }

        // Cari advokat yang pernah menangani client
        $defaultLawyer = Consultation::where('client_id', $client->id)
            ->whereNotNull('lawyer_id')
            ->with('lawyer')
            ->latest()
            ->first()?->lawyer;

        // Kalau belum pernah ditangani advokat, ambil advokat pertama
        if (!$defaultLawyer) {
            $defaultLawyer = User::where('role', 'advokat')->first();
        }

        return view('klien.consultations', compact(
            'consultations',
            'consultation',
            'defaultLawyer'
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

        // Cari advokat yang pernah menangani client
        $lawyer = Consultation::where('client_id', $client->id)
            ->whereNotNull('lawyer_id')
            ->latest()
            ->first()?->lawyer;

        // Kalau belum ada, gunakan advokat pertama
        if (!$lawyer) {
            $lawyer = User::where('role', 'advokat')->first();
        }

        Consultation::create([
            'client_id'    => $client->id,
            'lawyer_id'    => $lawyer?->id,
            'problem_type' => $validated['problem_type'],
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'status'       => 'Menunggu',
        ]);

        return redirect()
            ->route('klien.consultations')
            ->with(
                'success',
                'Konsultasi berhasil diajukan! Advokat akan segera menghubungi Anda.'
            );
    }


    // ─────────────────────────────────────────────────────────────────────────
    // DETAIL KONSULTASI
    // ─────────────────────────────────────────────────────────────────────────

    public function consultationDetail($id)
    {
        $client = Auth::user();

        $consultation = Consultation::with('lawyer')
            ->where('client_id', $client->id)
            ->findOrFail($id);

        $consultations = Consultation::where('client_id', $client->id)
            ->with('lawyer')
            ->orderBy('created_at', 'desc')
            ->get();

        $defaultLawyer = $consultation->lawyer;

        return view('klien.consultations', compact(
            'consultations',
            'consultation',
            'defaultLawyer'
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
                'documents'
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
            'documents'
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
    // UPLOAD DOKUMEN
    // ─────────────────────────────────────────────────────────────────────────

    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $client = Auth::user();

        // Pastikan dokumen memang milik client yang sedang login
        $document = Document::where('client_id', $client->id)
            ->findOrFail($id);

        // Upload file
        $path = $request->file('file')
            ->store('documents', 'public');

        // Update dokumen
        $document->update([
            'file_path' => $path,
            'status'    => 'Menunggu Pemeriksaan',
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Dokumen berhasil diunggah dan menunggu pemeriksaan advokat.'
            );
    }
}