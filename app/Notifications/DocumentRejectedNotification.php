<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentRejectedNotification extends Notification
{
    use Queueable;

    public Document $document;
    public string $reason;

    public function __construct(Document $document, string $reason)
    {
        $this->document = $document;
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $caseNum = $this->document->case?->case_number ?? '-';

        return [
            'type'             => 'document_rejected',
            'title'            => 'Dokumen Perlu Diperbaiki / Ditolak',
            'message'          => "Dokumen '{$this->document->name}' pada perkara {$caseNum} ditolak oleh Advokat: \"{$this->reason}\". Silakan unggah ulang dokumen yang sesuai.",
            'document_id'      => $this->document->id,
            'case_id'          => $this->document->case_id,
            'rejection_reason' => $this->reason,
            'action_url'       => route('klien.cases.show', $this->document->case_id),
            'icon'             => 'alert-triangle',
            'color'            => 'red',
        ];
    }
}
