<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Preview document inline in browser (e.g. PDF, Images)
     */
    public function view($id)
    {
        $document = Document::with('case')->findOrFail($id);

        Gate::authorize('view', $document);

        if (!$document->file_path || !Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Berkas dokumen fisik tidak ditemukan di penyimpanan server.');
        }

        $user = Auth::user();
        $roleLabel = ucfirst($user->role);
        $note = "{$roleLabel} ({$user->name}) membuka preview dokumen.";
        if ($user->isAdmin()) {
            $note = "Admin ({$user->name}) melakukan supervisi dan melihat dokumen perkara {$document->case?->case_number}.";
        }

        DocumentAuditLog::record(
            $document->id,
            $document->case_id,
            $user->id,
            'view',
            $note
        );

        $fullPath = Storage::disk('local')->path($document->file_path);
        $mimeType = $document->mime_type ?: (mime_content_type($fullPath) ?: 'application/octet-stream');
        $filename = $document->original_filename ?: ($document->name . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Download document file with original filename
     */
    public function download($id)
    {
        $document = Document::with('case')->findOrFail($id);

        Gate::authorize('download', $document);

        if (!$document->file_path || !Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Berkas dokumen fisik tidak ditemukan di penyimpanan server.');
        }

        $user = Auth::user();
        $roleLabel = ucfirst($user->role);
        $note = "{$roleLabel} ({$user->name}) mengunduh dokumen.";
        if ($user->isAdmin()) {
            $note = "Admin ({$user->name}) melakukan supervisi dan mengunduh berkas dokumen perkara {$document->case?->case_number}.";
        }

        DocumentAuditLog::record(
            $document->id,
            $document->case_id,
            $user->id,
            'download',
            $note
        );

        $filename = $document->original_filename ?: ($document->name . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));

        return Storage::disk('local')->download($document->file_path, $filename);
    }
}
