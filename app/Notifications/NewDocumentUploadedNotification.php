<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewDocumentUploadedNotification extends Notification
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
        $uploaderName = $this->document->uploader?->name ?? 'Klien';
        $caseNum = $this->document->case?->case_number ?? '-';

        return [
            'type'        => 'document_uploaded',
            'title'       => 'Dokumen Baru Diunggah',
            'message'     => "{$uploaderName} telah mengunggah dokumen '{$this->document->name}' pada perkara {$caseNum}.",
            'document_id' => $this->document->id,
            'case_id'     => $this->document->case_id,
            'action_url'  => route('advokat.cases.show', $this->document->case_id),
            'icon'        => 'file-text',
            'color'       => 'blue',
        ];
    }
}
