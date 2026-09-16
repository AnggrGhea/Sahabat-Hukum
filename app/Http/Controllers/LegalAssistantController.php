<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LegalRagService;
use App\Services\GeminiService;

class LegalAssistantController extends Controller
{
    protected LegalRagService $rag;
    protected GeminiService $gemini;

    public function __construct(LegalRagService $rag, GeminiService $gemini)
    {
        $this->rag = $rag;
        $this->gemini = $gemini;
    }

    /**
     * Display the Legal Assistant interface tailored by user role
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user?->role ?? 'klien';
        $sessionKey = 'legal_assistant_messages_' . ($user?->id ?? 'guest');
        $messages = session($sessionKey, []);
        $isApiConfigured = $this->gemini->isConfigured();

        if ($role === 'advokat') {
            $faqs = [
                'Pasal apa yang mengatur tindak pidana penganiayaan?',
                'Dasar hukum mengenai kekerasan dalam rumah tangga apa?',
                'Apa dasar hukum perceraian di Indonesia?',
                'UU apa yang mengatur perlindungan konsumen?',
                'Carikan dasar hukum mengenai wanprestasi.',
            ];

            return view('advokat.assistant', compact('faqs', 'messages', 'isApiConfigured'));
        }

        // Default: Klien
        $faqs = [
            'Apa dokumen yang diperlukan untuk konsultasi?',
            'Bagaimana proses pengajuan gugatan perdata?',
            'Apa yang dimaksud dengan surat kuasa?',
            'Berapa lama proses persidangan berlangsung?',
        ];

        return view('klien.assistant', compact('faqs', 'messages', 'isApiConfigured'));
    }

    /**
     * Process chat message via RAG (Internal KB + JDIH BPK) and Gemini API
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        @set_time_limit(60);

        $user = auth()->user();
        $role = $user?->role ?? 'klien';
        $question = trim($request->input('message'));

        // Retrieve current conversation history from session
        $sessionKey = 'legal_assistant_messages_' . ($user?->id ?? 'guest');
        $history = session($sessionKey, []);

        try {
            // High-level tiered retrieval & generation with conversation history
            $response = $this->rag->ask($question, $role, $history);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("LegalAssistant Exception: " . $e->getMessage());
            $response = [
                'success' => false,
                'answer'  => "Mohon maaf, penelusuran memerlukan waktu lebih lama dari perkiraan. Silakan ulangi pertanyaan Anda atau konsultasikan langsung dengan tim Advokat kami melalui tombol **Ajukan Konsultasi**.",
                'sources' => [],
            ];
        }

        $time = now()->format('H.i');

        // Save conversation history to role/user specific session
        $history[] = [
            'role'    => 'user',
            'content' => $question,
            'time'    => $time,
        ];
        $history[] = [
            'role'    => 'assistant',
            'content' => $response['answer'],
            'sources' => $response['sources'] ?? [],
            'time'    => $time,
        ];

        // Limit history to 30 messages
        if (count($history) > 30) {
            $history = array_slice($history, -30);
        }
        session([$sessionKey => $history]);

        return response()->json([
            'success' => $response['success'],
            'answer'  => $response['answer'],
            'sources' => $response['sources'] ?? [],
            'time'    => $time,
        ]);
    }

    /**
     * Clear chat history session for the current user
     */
    public function clearHistory(Request $request)
    {
        $user = auth()->user();
        $sessionKey = 'legal_assistant_messages_' . ($user?->id ?? 'guest');
        session()->forget($sessionKey);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat percakapan berhasil dibersihkan.',
        ]);
    }
}
