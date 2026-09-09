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
     * Display the Legal Assistant interface
     */
    public function index()
    {
        $faqs = [
            'Apa dokumen yang diperlukan untuk konsultasi?',
            'Bagaimana proses pengajuan gugatan perdata?',
            'Apa yang dimaksud dengan surat kuasa?',
            'Berapa lama proses persidangan berlangsung?',
        ];

        $messages = session('legal_assistant_messages', []);
        $isApiConfigured = $this->gemini->isConfigured();

        return view('klien.assistant', compact('faqs', 'messages', 'isApiConfigured'));
    }

    /**
     * Process chat message via RAG and Gemini API
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $question = trim($request->input('message'));

        // 1. Retrieval: Cari konteks hukum dari Knowledge Base
        $retrieval = $this->rag->retrieve($question);

        // 2. Generation: Kirim context + pertanyaan ke Gemini API
        $response = $this->gemini->generateAnswer(
            $question,
            $retrieval['context'],
            $retrieval['sources']
        );

        $time = now()->format('H.i');

        // 3. Simpan riwayat percakapan ke session
        $history = session('legal_assistant_messages', []);
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

        // Batasi riwayat maksimal 30 pesan terakhir per sesi
        if (count($history) > 30) {
            $history = array_slice($history, -30);
        }
        session(['legal_assistant_messages' => $history]);

        return response()->json([
            'success' => $response['success'],
            'answer'  => $response['answer'],
            'sources' => $response['sources'] ?? [],
            'time'    => $time,
        ]);
    }

    /**
     * Clear chat history session
     */
    public function clearHistory()
    {
        session()->forget('legal_assistant_messages');

        return response()->json([
            'success' => true,
            'message' => 'Riwayat percakapan berhasil dibersihkan.',
        ]);
    }
}
