<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentCollaborationNotification extends Notification
{
    use Queueable;

    public string $eventType;
    public string $messageText;
    public ?Document $document;
    public LegalCase $case;
    public User $actor;
    public ?string $actionUrl;

    public function __construct(string $eventType, string $messageText, ?Document $document, LegalCase $case, User $actor, ?string $actionUrl = null)
    {
        $this->eventType   = $eventType;
        $this->messageText = $messageText;
        $this->document    = $document;
        $this->case        = $case;
        $this->actor       = $actor;
        $this->actionUrl   = $actionUrl ?: route('advokat.cases.collaboration', $case->id);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $title = match ($this->eventType) {
            'new_document'   => 'Dokumen Kolaborasi Baru',
            'new_version'    => 'Revisi Dokumen Baru Diunggah',
            'new_comment'    => 'Catatan/Komentar Dokumen Baru',
            'status_changed' => 'Perubahan Status Dokumen',
            'finalized'      => 'Dokumen Ditetapkan Final',
            'team_joined'    => 'Penugasan Tim Perkara Baru',
            'team_removed'   => 'Pencabutan Akses Tim Perkara',
            default          => 'Pemberitahuan Kolaborasi Dokumen',
        };

        $icon = match ($this->eventType) {
            'new_document'   => 'file-plus',
            'new_version'    => 'history',
            'new_comment'    => 'message-square',
            'status_changed' => 'refresh-cw',
            'finalized'      => 'check-circle-2',
            'team_joined'    => 'users',
            default          => 'file-text',
        };

        $color = match ($this->eventType) {
            'finalized'      => 'emerald',
            'new_version'    => 'indigo',
            'new_comment'    => 'amber',
            'status_changed' => 'blue',
            default          => 'slate',
        };

        return [
            'type'        => 'document_collaboration',
            'event'       => $this->eventType,
            'title'       => $title,
            'message'     => $this->messageText,
            'document_id' => $this->document?->id,
            'case_id'     => $this->case->id,
            'actor_id'    => $this->actor->id,
            'action_url'  => $this->actionUrl,
            'icon'        => $icon,
            'color'       => $color,
        ];
    }
}
