<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{id}', function ($user, $id) {
    if ($user->role === 'admin') {
        return true;
    }

    $conversation = \App\Models\Conversation::find($id);
    if (!$conversation) {
        return false;
    }

    return (int) $user->id === (int) $conversation->client_id 
        || (int) $user->id === (int) $conversation->lawyer_id;
});

