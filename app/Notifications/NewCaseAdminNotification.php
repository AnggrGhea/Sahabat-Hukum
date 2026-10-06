<?php

namespace App\Notifications;

use App\Models\LegalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCaseAdminNotification extends Notification
{
    use Queueable;

    public LegalCase $case;

    public function __construct(LegalCase $case)
    {
        $this->case = $case->load(['client', 'lawyer']);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $lawyerName = $this->case->lawyer?->name ?? 'Advokat';
        $clientName = $this->case->client?->name ?? 'Klien';

        return [
            'type'       => 'new_case_admin',
            'title'      => 'Perkara Baru Didaftarkan',
            'message'    => "Perkara baru {$this->case->case_number} ('{$this->case->title}') telah didaftarkan oleh Advokat {$lawyerName} untuk Klien {$clientName}.",
            'case_id'    => $this->case->id,
            'action_url' => route('admin.cases'),
            'icon'       => 'folder-open',
            'color'      => 'gold',
        ];
    }
}
