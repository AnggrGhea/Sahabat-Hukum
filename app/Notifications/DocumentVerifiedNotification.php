<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentVerifiedNotification extends Notification
{
    use Queueable;

    public Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $caseNum = $this->document->case?->case_number ?? '-';

        return [
            'type'        => 'document_verified',
            'title'       => 'Dokumen Terverifikasi',
            'message'     => "Dokumen '{$this->document->name}' pada perkara {$caseNum} telah diverifikasi oleh Advokat.",
            'document_id' => $this->document->id,
            'case_id'     => $this->document->case_id,
            'action_url'  => route('klien.cases.show', $this->document->case_id),
            'icon'        => 'check-circle',
            'color'       => 'green',
        ];
    }
}
