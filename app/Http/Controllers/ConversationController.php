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
    /**
     * Display the conversation interface.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdvokat = ($user->role === 'advokat');
        $isAdmin = ($user->role === 'admin');

        // Safely ensure conversations exist ONLY for legitimate handled consultations and cases
        $this->syncLegitimateConversations($user);

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

        // Multi-criteria search (client name, lawyer name, case number, case title, consultation title, message body)
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $conversationsQuery->where(function ($q) use ($search) {
                $q->whereHas('client', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('lawyer', function ($lq) use ($search) {
                    $lq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('legalCase', function ($caseQ) use ($search) {
                    $caseQ->where('title', 'like', "%{$search}%")
                          ->orWhere('case_number', 'like', "%{$search}%");
                })
                ->orWhereHas('consultation', function ($conQ) use ($search) {
                    $conQ->where('title', 'like', "%{$search}%");
                })
                ->orWhereHas('messages', function ($msgQ) use ($search) {
                    $msgQ->where('body', 'like', "%{$search}%");
                });
            });
        }

        $conversations = $conversationsQuery
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC')
            ->orderByDesc('created_at')
            ->get();

        // Support AJAX/JSON live search
        if ($request->wantsJson() && $request->has('q')) {
            $mapped = $conversations->map(function ($conv) use ($user, $isAdvokat) {
                $otherUser = $conv->getOtherParticipant($user->id);
                $otherName = $otherUser?->name ?? 'Pengguna';
                $otherRole = ($otherUser?->role === 'advokat')
                    ? ($otherUser->lawyerProfile?->specialization ? 'Advokat • ' . $otherUser->lawyerProfile->specialization : 'Advokat')
                    : 'Klien';

                $contextText = $conv->legalCase
                    ? ($conv->legalCase->case_number ? "Perkara: {$conv->legalCase->case_number} — {$conv->legalCase->title}" : "Perkara: {$conv->legalCase->title}")
                    : ($conv->consultation ? "Konsultasi: {$conv->consultation->title}" : ($conv->title ?? 'Konsultasi'));

                $lastMsg = $conv->latestMessage;
                $snippet = $lastMsg ? \Illuminate\Support\Str::limit($lastMsg->body, 50) : 'Belum ada pesan.';
                $msgTime = $lastMsg ? $lastMsg->created_at->format('H.i') : '';
                $unreadCount = $conv->unreadCountFor($user->id);
                $chatUrl = $isAdvokat
                    ? route('advokat.chat', ['conversation_id' => $conv->id])
                    : route('klien.chat', ['conversation_id' => $conv->id]);

                return [
                    'id'            => $conv->id,
                    'other_name'    => $otherName,
                    'other_role'    => $otherRole,
                    'context_title' => $contextText,
                    'snippet'       => $snippet,
                    'msg_time'      => $msgTime,
                    'unread_count'  => $unreadCount,
                    'url'           => $chatUrl,
                    'is_case'       => (bool) $conv->legalCase,
                    'case_id'       => $conv->case_id,
                    'consult_id'    => $conv->consultation_id,
                ];
            });

            return response()->json([
                'success'       => true,
                'conversations' => $mapped,
            ]);
        }

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
            'isAdvokat',
            'search'
        ));
    }

    /**
     * Safely ensure conversations exist ONLY for legitimate handled consultations and cases.
     * No dummy data, strictly adhering to verified client-lawyer relationships.
     */
    protected function syncLegitimateConversations($user): void
    {
        if ($user->role === 'klien') {
            // 1. Handled consultations for this client (must have assigned lawyer)
            $consultations = \App\Models\Consultation::where('client_id', $user->id)
                ->whereNotNull('lawyer_id')
                ->whereHas('lawyer', fn($q) => $q->where('role', 'advokat'))
                ->doesntHave('conversation')
                ->get();

            foreach ($consultations as $consult) {
                Conversation::create([
                    'consultation_id' => $consult->id,
                    'client_id'       => $consult->client_id,
                    'lawyer_id'       => $consult->lawyer_id,
                    'title'           => 'Konsultasi: ' . $consult->title,
                    'status'          => 'active',
                    'last_message_at' => null,
                ]);
            }

            // 2. Cases for this client
            $cases = \App\Models\LegalCase::where('client_id', $user->id)
                ->whereNotNull('lawyer_id')
                ->whereHas('lawyer', fn($q) => $q->where('role', 'advokat'))
                ->doesntHave('conversations')
                ->get();

            foreach ($cases as $case) {
                $existing = Conversation::where('consultation_id', $case->consultation_id)
                    ->where('client_id', $case->client_id)
                    ->where('lawyer_id', $case->lawyer_id)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'case_id' => $case->id,
                        'title'   => 'Perkara: ' . ($case->case_number ? $case->case_number . ' — ' : '') . $case->title,
                    ]);
                } else {
                    Conversation::create([
                        'consultation_id' => $case->consultation_id,
                        'case_id'         => $case->id,
                        'client_id'       => $case->client_id,
                        'lawyer_id'       => $case->lawyer_id,
                        'title'           => 'Perkara: ' . ($case->case_number ? $case->case_number . ' — ' : '') . $case->title,
                        'status'          => 'active',
                        'last_message_at' => null,
                    ]);
                }
            }
        } elseif ($user->role === 'advokat') {
            // Handled consultations assigned to this lawyer
            $consultations = \App\Models\Consultation::where('lawyer_id', $user->id)
                ->whereHas('client', fn($q) => $q->where('role', 'klien'))
                ->doesntHave('conversation')
                ->get();

            foreach ($consultations as $consult) {
                Conversation::create([
                    'consultation_id' => $consult->id,
                    'client_id'       => $consult->client_id,
                    'lawyer_id'       => $consult->lawyer_id,
                    'title'           => 'Konsultasi: ' . $consult->title,
                    'status'          => 'active',
                    'last_message_at' => null,
                ]);
            }

            // Cases for this lawyer
            $cases = \App\Models\LegalCase::where('lawyer_id', $user->id)
                ->whereHas('client', fn($q) => $q->where('role', 'klien'))
                ->doesntHave('conversations')
                ->get();

            foreach ($cases as $case) {
                $existing = Conversation::where('consultation_id', $case->consultation_id)
                    ->where('client_id', $case->client_id)
                    ->where('lawyer_id', $case->lawyer_id)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'case_id' => $case->id,
                        'title'   => 'Perkara: ' . ($case->case_number ? $case->case_number . ' — ' : '') . $case->title,
                    ]);
                } else {
                    Conversation::create([
                        'consultation_id' => $case->consultation_id,
                        'case_id'         => $case->id,
                        'client_id'       => $case->client_id,
                        'lawyer_id'       => $case->lawyer_id,
                        'title'           => 'Perkara: ' . ($case->case_number ? $case->case_number . ' — ' : '') . $case->title,
                        'status'          => 'active',
                        'last_message_at' => null,
                    ]);
                }
            }
        }
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
