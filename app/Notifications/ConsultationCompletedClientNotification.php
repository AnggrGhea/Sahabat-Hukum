<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConsultationCompletedClientNotification extends Notification
{
    use Queueable;

    public Consultation $consultation;

    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $lawyerName = $this->consultation->lawyer?->name ?? 'Advokat';

        return [
            'type'            => 'consultation_completed',
            'title'           => 'Konsultasi Selesai',
            'message'         => "Konsultasi '{$this->consultation->title}' telah selesai ditangani oleh {$lawyerName}. Resume dan catatan hasil konsultasi telah tersedia.",
            'consultation_id' => $this->consultation->id,
            'action_url'      => route('klien.consultations.show', $this->consultation->id),
            'icon'            => 'check-circle',
            'color'           => 'green',
        ];
    }
}
