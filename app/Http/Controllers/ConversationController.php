<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewChatMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    /**
     * Display the conversation interface.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdvokat = ($user->role === 'advokat');
        $isAdmin = ($user->role === 'admin');

        // Fetch conversations strictly for the current user
        $conversationsQuery = Conversation::with([
            'client.clientProfile',
            'lawyer.lawyerProfile',
            'consultation',
            'legalCase',
            'latestMessage.sender',
        ]);

        if ($user->role === 'klien') {
            $conversationsQuery->where('client_id', $user->id);
        } elseif ($user->role === 'advokat') {
            $conversationsQuery->where('lawyer_id', $user->id);
        }

        $conversations = $conversationsQuery
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC')
            ->orderByDesc('created_at')
            ->get();

        // Determine active conversation
        $activeConversation = null;
        $requestedId = $request->query('conversation_id');

        if ($requestedId) {
            $candidate = Conversation::with([
                'client.clientProfile',
                'lawyer.lawyerProfile',
                'consultation',
                'legalCase',
            ])->find($requestedId);

            if ($candidate) {
                Gate::authorize('view', $candidate);
                $activeConversation = $candidate;
            }
        }

        if (!$activeConversation && $conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
        }

        $messages = collect();
        if ($activeConversation) {
            Gate::authorize('view', $activeConversation);

            $messages = $activeConversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();

            // Mark unread messages sent by the other party as read
            $activeConversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        return view('percakapan.index', compact(
            'conversations',
            'activeConversation',
            'messages',
            'user',
            'isAdvokat'
        ));
    }

    /**
     * Send a new message in the conversation.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.min'      => 'Pesan tidak boleh kosong.',
        ]);

        $conversation = Conversation::findOrFail($id);
        Gate::authorize('sendMessage', $conversation);

        $user = Auth::user();
        $messageText = trim($request->input('message'));
        if ($messageText === '') {
            return response()->json([
                'success' => false,
                'message' => 'Pesan tidak boleh kosong.',
            ], 422);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body'      => $messageText,
            'is_read'   => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        // Broadcast to WebSocket channel gracefully
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            Log::info("Broadcast skipped or unavailable: " . $e->getMessage());
        }

        // Notify recipient via database notification
        $recipient = $conversation->getOtherParticipant($user->id);
        if ($recipient) {
            try {
                $recipient->notify(new NewChatMessageNotification($message));
            } catch (\Throwable $e) {
                Log::warning("Chat notification dispatch failed: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id'              => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id'       => $message->sender_id,
                'sender_name'     => $user->name,
                'sender_role'     => $user->role,
                'body'            => $message->body,
                'created_at'      => $message->created_at->toISOString(),
                'formatted_time'  => $message->created_at->format('H.i'),
                'formatted_date'  => $message->created_at->translatedFormat('l, d F Y'),
                'is_me'           => true,
            ],
        ]);
    }

    /**
     * Fetch new messages (for resilient polling fallback).
     */
    public function getMessages(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        Gate::authorize('view', $conversation);

        $user = Auth::user();
        $afterId = $request->query('after_id');

        $query = $conversation->messages()->with('sender')->orderBy('created_at', 'asc');

        if ($afterId) {
            $query->where('id', '>', (int) $afterId);
        }

        $newMessages = $query->get();

        // Mark incoming messages as read
        if ($newMessages->isNotEmpty()) {
            $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->whereIn('id', $newMessages->pluck('id'))
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        $mapped = $newMessages->map(function ($msg) use ($user) {
            return [
                'id'              => $msg->id,
                'conversation_id' => $msg->conversation_id,
                'sender_id'       => $msg->sender_id,
                'sender_name'     => $msg->sender?->name ?? 'Pengguna',
                'sender_role'     => $msg->sender?->role ?? '',
                'body'            => $msg->body,
                'created_at'      => $msg->created_at->toISOString(),
                'formatted_time'  => $msg->created_at->format('H.i'),
                'formatted_date'  => $msg->created_at->translatedFormat('l, d F Y'),
                'is_me'           => ((int) $msg->sender_id === (int) $user->id),
            ];
        });

        return response()->json([
            'success'  => true,
            'messages' => $mapped,
        ]);
    }
}
