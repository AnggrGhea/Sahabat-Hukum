<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentRequestedNotification extends Notification
{
    use Queueable;

    public DocumentRequest $request;

    public function __construct(DocumentRequest $request)
    {
        $this->request = $request;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $caseNum = $this->request->case?->case_number ?? '-';
        $lawyerName = $this->request->requester?->name ?? 'Advokat';

        return [
            'type'        => 'document_requested',
            'title'       => 'Permintaan Dokumen dari Advokat',
            'message'     => "{$lawyerName} meminta Anda mengunggah dokumen '{$this->request->title}' untuk perkara {$caseNum}.",
            'request_id'  => $this->request->id,
            'case_id'     => $this->request->case_id,
            'action_url'  => route('klien.cases.show', $this->request->case_id),
            'icon'        => 'file-plus',
            'color'       => 'orange',
        ];
    }
}
