<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewConsultationAdminNotification extends Notification
{
    use Queueable;

    public Consultation $consultation;

    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation->load(['client']);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $clientName = $this->consultation->client?->name ?? 'Klien';

        return [
            'type'            => 'new_consultation_admin',
            'title'           => 'Konsultasi Baru Masuk',
            'message'         => "Klien {$clientName} mengajukan permohonan konsultasi baru: '{$this->consultation->title}'. Memerlukan penetapan Advokat.",
            'consultation_id' => $this->consultation->id,
            'action_url'      => route('admin.consultations'),
            'icon'            => 'message-square',
            'color'           => 'gold',
        ];
    }
}
