<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\CaseProgress;
use App\Models\Document;
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

        $query = Consultation::where('lawyer_id', $lawyer->id)->with('client');
        if ($filter !== 'Semua') {
            $query->where('status', $filter);
        }
        $consultations = $query->orderBy('created_at', 'desc')->get();

        // Load first item as active by default
        $activeId     = $request->query('id', $consultations->first()?->id);
        $consultation = Consultation::with('client', 'lawyer', 'case')
            ->where('lawyer_id', $lawyer->id)
            ->find($activeId);

        return view('advokat.consultations', compact('consultations', 'consultation', 'filter'));
    }

    public function consultationDetail($id)
    {
        $lawyer       = Auth::user();
        $consultation = Consultation::with('client', 'lawyer', 'case')
            ->where('lawyer_id', $lawyer->id)->findOrFail($id);
        $filter       = 'Semua';
        $consultations = Consultation::where('lawyer_id', $lawyer->id)
            ->with('client')->orderBy('created_at', 'desc')->get();
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

        // Create schedule entry
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
        $case     = LegalCase::with('client', 'lawyer', 'progress', 'documents')
            ->where('lawyer_id', $lawyer->id)->find($activeId);

        return view('advokat.cases', compact('cases', 'case', 'filter'));
    }

    public function caseDetail($id)
    {
        $lawyer = Auth::user();
        $case   = LegalCase::with('client', 'lawyer', 'progress', 'documents')
            ->where('lawyer_id', $lawyer->id)->findOrFail($id);
        $filter = 'Semua';
        $cases  = LegalCase::where('lawyer_id', $lawyer->id)
            ->with('client')->orderBy('created_at', 'desc')->get();
        return view('advokat.cases', compact('cases', 'case', 'filter'));
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
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'priority'    => 'in:Normal,Tinggi',
        ]);

        $lawyer = Auth::user();
        $case   = LegalCase::where('lawyer_id', $lawyer->id)->findOrFail($id);

        Document::create([
            'case_id'     => $case->id,
            'client_id'   => $case->client_id,
            'lawyer_id'   => $lawyer->id,
            'name'        => $request->name,
            'description' => $request->description,
            'due_date'    => $request->due_date,
            'priority'    => $request->priority ?? 'Normal',
            'status'      => 'Belum Diunggah',
        ]);

        return redirect()->route('advokat.cases.show', $id)
            ->with('success', 'Permintaan dokumen berhasil dikirim.');
    }

    public function verifyDocument(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Sudah Diterima,Perlu Diperbaiki',
        ]);

        $lawyer   = Auth::user();
        $document = Document::where('lawyer_id', $lawyer->id)->findOrFail($id);
        $document->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status dokumen berhasil diperbarui.');
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
}
