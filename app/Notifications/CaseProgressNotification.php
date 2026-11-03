<?php

namespace App\Notifications;

use App\Models\CaseProgress;
use App\Models\LegalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CaseProgressNotification extends Notification
{
    use Queueable;

    public LegalCase $case;
    public CaseProgress $progress;

    public function __construct(LegalCase $case, CaseProgress $progress)
    {
        $this->case = $case;
        $this->progress = $progress;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'          => 'case_progress',
            'title'         => "Pembaruan Perkara {$this->case->case_number}",
            'message'       => "Perkembangan baru telah dicatat: '{$this->progress->title}'.",
            'case_id'       => $this->case->id,
            'progress_id'   => $this->progress->id,
            'action_url'    => route('klien.cases.show', $this->case->id),
            'icon'          => 'activity',
            'color'         => 'navy',
        ];
    }
}
