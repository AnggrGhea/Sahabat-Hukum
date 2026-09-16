<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\Document;
use App\Models\KnowledgeSource;

class AdminController extends Controller
{
    public function dashboard()
    {
        $data = [
            'total_clients'         => User::where('role', 'klien')->count(),
            'total_lawyers'         => User::where('role', 'advokat')->count(),
            'active_cases'          => LegalCase::whereNotIn('status', ['Selesai', 'Dibatalkan'])->count(),
            'pending_consultations' => Consultation::where('status', 'Menunggu')->count(),
            'pending_documents'     => Document::where('status', 'Menunggu Pemeriksaan')->count(),
            'today_schedules'       => \App\Models\Schedule::whereDate('start_at', today())->count(),
        ];
        return view('admin.dashboard', $data);
    }

    public function clients()
    {
        $clients = User::where('role', 'klien')
            ->with('clientProfile')
            ->orderBy('name')
            ->get();
        return view('admin.clients', compact('clients'));
    }

    public function lawyers()
    {
        $lawyers = User::where('role', 'advokat')
            ->with('lawyerProfile')
            ->orderBy('name')
            ->get();
        return view('admin.lawyers', compact('lawyers'));
    }

    public function cases()
    {
        $cases = LegalCase::with('client', 'lawyer')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.cases', compact('cases'));
    }

    public function consultations()
    {
        $consultations = Consultation::with('client', 'lawyer')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.consultations', compact('consultations'));
    }

    public function documents(Request $request = null)
    {
        $request = $request ?? request();
        $statusFilter   = $request->query('status', 'all');
        $selectedCaseId = $request->query('case_id');
        $search         = $request->query('q');

        $query = Document::with('client', 'lawyer', 'case', 'uploader', 'verifier')
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
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('case', fn($cq) => $cq->where('case_number', 'like', "%{$search}%"));
            });
        }

        $documents = $query->get();

        $allDocs = Document::all();
        $stats = [
            'total'        => $allDocs->count(),
            'verified'     => $allDocs->filter(fn($d) => in_array($d->status, ['Terverifikasi', 'Sudah Diterima']))->count(),
            'pending'      => $allDocs->filter(fn($d) => in_array($d->status, ['Menunggu Verifikasi', 'Menunggu Pemeriksaan', 'Belum Diunggah']))->count(),
            'rejected'     => $allDocs->filter(fn($d) => in_array($d->status, ['Ditolak', 'Perlu Diperbaiki']))->count(),
        ];

        $cases = LegalCase::orderBy('created_at', 'desc')->get();

        return view('admin.documents', compact('documents', 'stats', 'cases', 'statusFilter', 'selectedCaseId', 'search'));
    }

    public function users()
    {
        $users = User::orderBy('role')->orderBy('name')->get();
        return view('admin.users', compact('users'));
    }

    public function knowledge()
    {
        $sources = KnowledgeSource::with('verifier')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.knowledge', compact('sources'));
    }

    public function storeKnowledge(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'source_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'content'     => 'required|string',
            'status'      => 'required|in:Aktif,Nonaktif',
        ]);

        $validated['verified_by'] = auth()->id();

        KnowledgeSource::create($validated);

        return redirect()->route('admin.knowledge')->with('success', 'Sumber pengetahuan berhasil ditambahkan.');
    }

    public function updateKnowledge(Request $request, $id)
    {
        $source = KnowledgeSource::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'source_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'content'     => 'required|string',
            'status'      => 'required|in:Aktif,Nonaktif',
        ]);

        $validated['verified_by'] = auth()->id();

        $source->update($validated);

        return redirect()->route('admin.knowledge')->with('success', 'Sumber pengetahuan berhasil diperbarui.');
    }

    public function toggleKnowledgeStatus($id)
    {
        $source = KnowledgeSource::findOrFail($id);
        $source->status = ($source->status === 'Aktif') ? 'Nonaktif' : 'Aktif';
        $source->verified_by = auth()->id();
        $source->save();

        return redirect()->route('admin.knowledge')->with('success', "Status sumber '{$source->title}' berhasil diubah menjadi {$source->status}.");
    }

    public function destroyKnowledge($id)
    {
        $source = KnowledgeSource::findOrFail($id);
        $source->delete();

        return redirect()->route('admin.knowledge')->with('success', 'Sumber pengetahuan berhasil dihapus.');
    }

    public function reports()
    {
        $data = [
            'total_consultations'    => Consultation::count(),
            'selesai_consultations'  => Consultation::where('status', 'Selesai')->count(),
            'total_cases'            => LegalCase::count(),
            'active_cases'           => LegalCase::whereNotIn('status', ['Selesai', 'Dibatalkan'])->count(),
            'selesai_cases'          => LegalCase::where('status', 'Selesai')->count(),
            'total_clients'          => User::where('role', 'klien')->count(),
        ];
        return view('admin.reports', compact('data'));
    }

    public function settings()
    {
        return view('admin.settings');
    }
}
