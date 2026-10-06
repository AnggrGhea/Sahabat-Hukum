<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConsultationSubmittedClientNotification extends Notification
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
        return [
            'type'            => 'consultation_submitted',
            'title'           => 'Konsultasi Berhasil Diajukan',
            'message'         => "Permohonan konsultasi '{$this->consultation->title}' berhasil diajukan dan sedang menunggu peninjauan Admin serta penetapan Advokat.",
            'consultation_id' => $this->consultation->id,
            'action_url'      => route('klien.consultations.show', $this->consultation->id),
            'icon'            => 'message-square',
            'color'           => 'blue',
        ];
    }
}
