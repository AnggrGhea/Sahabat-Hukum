<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Determine whether the user can view the conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'advokat') {
            return (int) $conversation->lawyer_id === (int) $user->id;
        }

        if ($user->role === 'klien') {
            return (int) $conversation->client_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can send a message in the conversation.
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return (int) $conversation->client_id === (int) $user->id 
            || (int) $conversation->lawyer_id === (int) $user->id;
    }

    /**
     * Determine whether the user can mark messages in the conversation as read.
     */
    public function markAsRead(User $user, Conversation $conversation): bool
    {
        return (int) $conversation->client_id === (int) $user->id 
            || (int) $conversation->lawyer_id === (int) $user->id;
    }
}
