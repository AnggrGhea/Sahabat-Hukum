<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConsultationAssignedClientNotification extends Notification
{
    use Queueable;

    public Consultation $consultation;

    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation->load(['lawyer']);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $lawyerName = $this->consultation->lawyer?->name ?? 'Advokat';

        return [
            'type'            => 'consultation_assigned_client',
            'title'           => 'Advokat Telah Ditetapkan',
            'message'         => "Konsultasi Anda '{$this->consultation->title}' akan ditangani oleh {$lawyerName}. Percakapan telah aktif.",
            'consultation_id' => $this->consultation->id,
            'action_url'      => route('klien.chat'),
            'icon'            => 'user-check',
            'color'           => 'blue',
        ];
    }
}
