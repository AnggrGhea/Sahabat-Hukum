<?php

namespace App\Notifications;

use App\Models\CaseProgress;
use App\Models\LegalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CaseProgressAdminNotification extends Notification
{
    use Queueable;

    public LegalCase $case;
    public CaseProgress $progress;

    public function __construct(LegalCase $case, CaseProgress $progress)
    {
        $this->case = $case->load(['lawyer', 'client']);
        $this->progress = $progress;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $lawyerName = $this->case->lawyer?->name ?? 'Advokat';

        return [
            'type'        => 'case_progress_admin',
            'title'       => "Perkembangan Perkara {$this->case->case_number}",
            'message'     => "Advokat {$lawyerName} mencatat perkembangan baru: '{$this->progress->title}'.",
            'case_id'     => $this->case->id,
            'progress_id' => $this->progress->id,
            'action_url'  => route('admin.cases'),
            'icon'        => 'activity',
            'color'       => 'blue',
        ];
    }
}
