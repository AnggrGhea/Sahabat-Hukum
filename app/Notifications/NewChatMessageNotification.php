<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewChatMessageNotification extends Notification
{
    use Queueable;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'conversation']);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $senderName = $this->message->sender?->name ?? 'Pengguna';
        $snippet = Str::limit($this->message->body, 50);
        $isRecipientLawyer = ($notifiable->role === 'advokat');

        $actionUrl = $isRecipientLawyer
            ? route('advokat.chat', ['conversation_id' => $this->message->conversation_id])
            : route('klien.chat', ['conversation_id' => $this->message->conversation_id]);

        return [
            'type'            => 'new_chat_message',
            'title'           => "Pesan Baru dari {$senderName}",
            'message'         => $snippet,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'action_url'      => $actionUrl,
            'icon'            => 'message-circle',
            'color'           => 'navy',
        ];
    }
}
