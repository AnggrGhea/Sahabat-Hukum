<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentActionRequiredAdminNotification extends Notification
{
    use Queueable;

    public Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document->load(['uploader', 'case']);
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
            'type'        => 'document_action_admin',
            'title'       => 'Dokumen Perkara Diunggah',
            'message'     => "{$uploaderName} mengunggah dokumen '{$this->document->name}' ({$this->document->document_type}) pada perkara {$caseNum}.",
            'document_id' => $this->document->id,
            'case_id'     => $this->document->case_id,
            'action_url'  => route('admin.documents'),
            'icon'        => 'file-text',
            'color'       => 'blue',
        ];
    }
}
