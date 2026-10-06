<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewClientRegisteredNotification extends Notification
{
    use Queueable;

    public User $client;

    public function __construct(User $client)
    {
        $this->client = $client;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'new_client_registered',
            'title'      => 'Registrasi Klien Baru',
            'message'    => "Klien baru telah mendaftar di sistem Sahabat Hukum: {$this->client->name} ({$this->client->email}).",
            'client_id'  => $this->client->id,
            'action_url' => route('admin.clients'),
            'icon'       => 'user-plus',
            'color'      => 'green',
        ];
    }
}
