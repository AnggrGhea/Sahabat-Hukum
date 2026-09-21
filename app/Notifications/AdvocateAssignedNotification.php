<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvocateAssignedNotification extends Notification
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
            'type'            => 'advocate_assigned',
            'title'           => 'Penugasan Konsultasi Baru',
            'message'         => "Anda telah ditugaskan oleh Admin untuk menangani konsultasi '{$this->consultation->title}' dari {$clientName}.",
            'consultation_id' => $this->consultation->id,
            'action_url'      => route('advokat.consultations.show', $this->consultation->id),
            'icon'            => 'briefcase',
            'color'           => 'gold',
        ];
    }
}
