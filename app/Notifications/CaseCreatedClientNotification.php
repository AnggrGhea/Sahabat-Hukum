<?php

namespace App\Notifications;

use App\Models\LegalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CaseCreatedClientNotification extends Notification
{
    use Queueable;

    public LegalCase $case;

    public function __construct(LegalCase $case)
    {
        $this->case = $case->load(['lawyer']);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $lawyerName = $this->case->lawyer?->name ?? 'Advokat';

        return [
            'type'       => 'case_created_client',
            'title'      => 'Perkara Hukum Dibuat',
            'message'    => "Perkara Anda '{$this->case->title}' ({$this->case->case_number}) telah resmi dibuat dan ditangani oleh Advokat {$lawyerName}.",
            'case_id'    => $this->case->id,
            'action_url' => route('klien.cases.show', $this->case->id),
            'icon'       => 'briefcase',
            'color'      => 'gold',
        ];
    }
}
