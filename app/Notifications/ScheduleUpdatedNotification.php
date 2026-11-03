<?php

namespace App\Notifications;

use App\Models\Consultation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ScheduleUpdatedNotification extends Notification
{
    use Queueable;

    public Consultation $consultation;
    public string $location;

    public function __construct(Consultation $consultation, string $location = 'Kantor Sahabat Hukum')
    {
        $this->consultation = $consultation->load(['lawyer']);
        $this->location = $location;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $formattedDate = $this->consultation->scheduled_at
            ? Carbon::parse($this->consultation->scheduled_at)->isoFormat('D MMMM Y, HH:mm') . ' WIB'
            : '-';
        $lawyerName = $this->consultation->lawyer?->name ?? 'Advokat';

        return [
            'type'            => 'schedule_updated',
            'title'           => 'Jadwal Konsultasi Ditetapkan',
            'message'         => "Jadwal konsultasi '{$this->consultation->title}' bersama Advokat {$lawyerName} ditetapkan pada {$formattedDate} di {$this->location}.",
            'consultation_id' => $this->consultation->id,
            'scheduled_at'    => $this->consultation->scheduled_at,
            'action_url'      => route('klien.schedule'),
            'icon'            => 'calendar',
            'color'           => 'blue',
        ];
    }
}
