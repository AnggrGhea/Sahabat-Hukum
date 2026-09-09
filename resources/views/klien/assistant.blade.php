@extends('layouts.klien')

@section('title', 'Asisten Hukum')
@section('header-title', 'Asisten Hukum')

@section('content')

{{-- Breadcrumb --}}
<div class="breadcrumb" style="margin-bottom:16px;">
    <a href="{{ route('klien.dashboard') }}">Beranda</a>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    <span class="current">Asisten Hukum</span>
</div>

{{-- Header Banner Card --}}
<div class="card" style="margin-bottom:16px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:42px;height:42px;background:#1a2744;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:#c9a84c;">
                <path d="M12 3L2 7l2 .8V18c0 .6.4 1 1 1h2v1h10v-1h2c.6 0 1-.4 1-1V7.8L22 7 12 3zm-2 14H6v-7.6l4 1.6V17zm8 0h-4v-6l4-1.6V17zM12 11.2L4.8 8.4 12 5.6l7.2 2.8L12 11.2z"/>
            </svg>
        </div>
        <div>
            <h1 style="font-size:1.15rem;font-weight:700;color:#1e293b;margin-bottom:2px;">Asisten Hukum</h1>
            <p style="font-size:.825rem;color:#64748b;margin:0;">Tanya jawab informasi hukum umum</p>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:10px;">
        <button id="btnClearChat" type="button" title="Bersihkan Percakapan"
                style="padding:8px 14px;border:1px solid #e2e8f0;background:#fff;color:#64748b;border-radius:8px;font-size:.8rem;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
            </svg>
            Percakapan Baru
        </button>
        <a href="{{ route('klien.consultations') }}" class="btn btn-primary"
           style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;font-size:.85rem;background:#1a2744;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Ajukan Konsultasi
        </a>
    </div>
</div>

@if(!$isApiConfigured)
<div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.825rem;display:flex;align-items:center;gap:10px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex-shrink:0;">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <div>
        <strong>Perhatian:</strong> Kunci API Gemini (<code>GEMINI_API_KEY</code>) belum diisi di file <code>.env</code>.
        Sistem pencarian RAG tetap aktif, namun respons AI membutuhkan konfigurasi API Key.
    </div>
</div>
@endif

{{-- Main Chat Area Card --}}
<div class="card" style="padding:24px;min-height:540px;display:flex;flex-direction:column;justify-content:space-between;">

    {{-- Chat Stream --}}
    <div id="chatMessages" style="flex:1;overflow-y:auto;max-height:550px;padding-right:8px;display:flex;flex-direction:column;gap:20px;">

        {{-- Initial Greeting Bubble --}}
        <div class="msg-assistant-wrap" style="display:flex;align-items:flex-start;gap:12px;">
            <div style="width:34px;height:34px;background:#1a2744;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#c9a84c;">
                    <path d="M12 3L2 7l2 .8V18c0 .6.4 1 1 1h2v1h10v-1h2c.6 0 1-.4 1-1V7.8L22 7 12 3zm-2 14H6v-7.6l4 1.6V17zm8 0h-4v-6l4-1.6V17zM12 11.2L4.8 8.4 12 5.6l7.2 2.8L12 11.2z"/>
                </svg>
            </div>
            <div style="flex:1;">
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:4px;">Asisten Hukum • 09.00</div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:16px 20px;color:#334155;line-height:1.6;font-size:.875rem;max-width:720px;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                    Selamat datang di Asisten Hukum Sahabat Hukum. Saya dapat membantu Anda memahami informasi hukum umum, persyaratan dokumen, dan prosedur layanan kami. Bagaimana saya dapat membantu Anda?
                </div>
            </div>
        </div>

        {{-- Render Session History if available --}}
        @if(isset($messages) && count($messages) > 0)
            @foreach($messages as $msg)
                @if($msg['role'] === 'user')
                <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                    <div style="max-width:620px;">
                        <div style="font-size:.72rem;color:#94a3b8;text-align:right;margin-bottom:3px;">Anda • {{ $msg['time'] ?? '' }}</div>
                        <div style="background:#1a2744;color:#fff;border-radius:12px 12px 2px 12px;padding:12px 18px;font-size:.875rem;line-height:1.5;">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                </div>
                @else
                <div class="msg-assistant-wrap" style="display:flex;align-items:flex-start;gap:12px;margin-top:4px;">
                    <div style="width:34px;height:34px;background:#1a2744;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#c9a84c;">
                            <path d="M12 3L2 7l2 .8V18c0 .6.4 1 1 1h2v1h10v-1h2c.6 0 1-.4 1-1V7.8L22 7 12 3zm-2 14H6v-7.6l4 1.6V17zm8 0h-4v-6l4-1.6V17zM12 11.2L4.8 8.4 12 5.6l7.2 2.8L12 11.2z"/>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:4px;">Asisten Hukum • {{ $msg['time'] ?? '' }}</div>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:16px 20px;color:#334155;line-height:1.65;font-size:.875rem;max-width:720px;box-shadow:0 1px 2px rgba(0,0,0,0.03);white-space:pre-wrap;">{!! nl2br(e($msg['content'])) !!}</div>
                        @if(!empty($msg['sources']))
                        <div style="margin-top:8px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <span style="font-size:.7rem;color:#64748b;font-weight:600;">Sumber Hukum:</span>
                            @foreach($msg['sources'] as $src)
                            <span style="font-size:.72rem;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:20px;padding:2px 10px;font-weight:500;">
                                {{ $src['title'] ?? $src }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        @endif

        {{-- FAQ Section (always accessible or when starting) --}}
        <div id="faqSection" style="margin:20px auto 10px;width:100%;max-width:760px;">
            <div style="font-size:.78rem;color:#94a3b8;font-weight:600;text-align:center;margin-bottom:14px;letter-spacing:.02em;">
                Pertanyaan yang sering diajukan:
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:10px;">
                @foreach($faqs as $faq)
                <button type="button" class="faq-chip" data-question="{{ $faq }}"
                        style="text-align:left;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;font-size:.825rem;color:#334155;cursor:pointer;transition:all .18s;box-shadow:0 1px 2px rgba(0,0,0,.02);">
                    {{ $faq }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Typing / Loading Indicator --}}
        <div id="loadingIndicator" style="display:none;align-items:flex-start;gap:12px;">
            <div style="width:34px;height:34px;background:#1a2744;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#c9a84c;">
                    <path d="M12 3L2 7l2 .8V18c0 .6.4 1 1 1h2v1h10v-1h2c.6 0 1-.4 1-1V7.8L22 7 12 3zm-2 14H6v-7.6l4 1.6V17zm8 0h-4v-6l4-1.6V17zM12 11.2L4.8 8.4 12 5.6l7.2 2.8L12 11.2z"/>
                </svg>
            </div>
            <div>
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:4px;">Asisten Hukum sedang menelaah...</div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px 20px;display:inline-flex;align-items:center;gap:8px;">
                    <span class="dot-typing"></span>
                    <span class="dot-typing"></span>
                    <span class="dot-typing"></span>
                </div>
            </div>
        </div>

    </div>

    {{-- Disclaimer & Input Bar --}}
    <div style="margin-top:20px;padding-top:12px;border-top:1px solid #f1f5f9;">

        {{-- Disclaimer Banner --}}
        <div style="background:#fefce8;border:1px solid #fef08a;color:#a16207;border-radius:8px;padding:8px 14px;text-align:center;font-size:.76rem;font-weight:500;margin-bottom:12px;">
            Informasi yang diberikan merupakan informasi umum dan bukan pengganti konsultasi langsung dengan Advokat.
        </div>

        {{-- Chat Input Form --}}
        <form id="chatForm" style="display:flex;align-items:center;gap:10px;background:#fff;border:1.5px solid #d1d5db;border-radius:10px;padding:6px 8px 6px 16px;transition:border-color .15s, box-shadow .15s;">
            @csrf
            <input id="chatInput" type="text" name="message" required
                   placeholder="Ketik pertanyaan Anda..." autocomplete="off"
                   style="flex:1;border:none;outline:none;font-size:.875rem;color:#1e293b;background:transparent;padding:6px 0;">
            <button id="btnSend" type="submit"
                    style="width:38px;height:38px;border-radius:8px;border:none;background:#e2e8f0;color:#94a3b8;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:17px;height:17px;transform:translateX(1px);">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>
    </div>

</div>

@push('styles')
<style>
.faq-chip:hover {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
    color: #1e3a5f !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.05) !important;
}
#chatForm:focus-within {
    border-color: #1e3a5f !important;
    box-shadow: 0 0 0 3px rgba(30,58,95,0.08) !important;
}
.dot-typing {
    width: 8px; height: 8px; background: #94a3b8; border-radius: 50%; display: inline-block;
    animation: waveDots 1.3s infinite ease-in-out both;
}
.dot-typing:nth-child(1) { animation-delay: -0.32s; }
.dot-typing:nth-child(2) { animation-delay: -0.16s; }
@keyframes waveDots {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40% { transform: scale(1); opacity: 1; background: #1e3a5f; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const btnSend = document.getElementById('btnSend');
    const chatMessages = document.getElementById('chatMessages');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const btnClearChat = document.getElementById('btnClearChat');
    const faqChips = document.querySelectorAll('.faq-chip');

    // Update send button style based on input content
    function updateSendButton() {
        if (chatInput.value.trim().length > 0) {
            btnSend.style.background = '#1a2744';
            btnSend.style.color = '#ffffff';
            btnSend.disabled = false;
        } else {
            btnSend.style.background = '#e2e8f0';
            btnSend.style.color = '#94a3b8';
        }
    }

    chatInput.addEventListener('input', updateSendButton);

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Append User Bubble
    function appendUserMessage(text, time) {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;justify-content:flex-end;margin-top:4px;';
        wrap.innerHTML = `
            <div style="max-width:620px;">
                <div style="font-size:.72rem;color:#94a3b8;text-align:right;margin-bottom:3px;">Anda • ${time}</div>
                <div style="background:#1a2744;color:#fff;border-radius:12px 12px 2px 12px;padding:12px 18px;font-size:.875rem;line-height:1.5;">
                    ${escapeHtml(text)}
                </div>
            </div>
        `;
        chatMessages.insertBefore(wrap, loadingIndicator);
        scrollToBottom();
    }

    // Append Assistant Bubble
    function appendAssistantMessage(text, sources, time) {
        const wrap = document.createElement('div');
        wrap.className = 'msg-assistant-wrap';
        wrap.style.cssText = 'display:flex;align-items:flex-start;gap:12px;margin-top:4px;';

        let sourcesHtml = '';
        if (sources && sources.length > 0) {
            sourcesHtml = `
                <div style="margin-top:8px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                    <span style="font-size:.7rem;color:#64748b;font-weight:600;">Sumber Hukum:</span>
                    ${sources.map(s => `
                        <span style="font-size:.72rem;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:20px;padding:2px 10px;font-weight:500;">
                            ${escapeHtml(s.title || s)}
                        </span>
                    `).join('')}
                </div>
            `;
        }

        // Format basic markdown bolding and linebreaks safely
        let formattedText = formatMarkdown(escapeHtml(text));

        wrap.innerHTML = `
            <div style="width:34px;height:34px;background:#1a2744;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#c9a84c;">
                    <path d="M12 3L2 7l2 .8V18c0 .6.4 1 1 1h2v1h10v-1h2c.6 0 1-.4 1-1V7.8L22 7 12 3zm-2 14H6v-7.6l4 1.6V17zm8 0h-4v-6l4-1.6V17zM12 11.2L4.8 8.4 12 5.6l7.2 2.8L12 11.2z"/>
                </svg>
            </div>
            <div style="flex:1;">
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:4px;">Asisten Hukum • ${time}</div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:16px 20px;color:#334155;line-height:1.65;font-size:.875rem;max-width:720px;box-shadow:0 1px 2px rgba(0,0,0,0.03);white-space:pre-wrap;">${formattedText}</div>
                ${sourcesHtml}
            </div>
        `;
        chatMessages.insertBefore(wrap, loadingIndicator);
        scrollToBottom();
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatMarkdown(str) {
        // Convert **bold** to <strong>bold</strong>
        return str.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    }

    // Send Question
    function sendQuestion(question) {
        if (!question) return;

        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + '.' + String(now.getMinutes()).padStart(2, '0');

        appendUserMessage(question, timeStr);
        chatInput.value = '';
        updateSendButton();

        // Show loading
        loadingIndicator.style.display = 'flex';
        scrollToBottom();

        // Disable input while processing
        chatInput.disabled = true;
        btnSend.disabled = true;

        fetch("{{ route('klien.assistant.chat') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ message: question })
        })
        .then(response => response.json())
        .then(data => {
            loadingIndicator.style.display = 'none';
            chatInput.disabled = false;
            btnSend.disabled = false;
            chatInput.focus();

            if (data.success) {
                appendAssistantMessage(data.answer, data.sources, data.time || timeStr);
            } else {
                appendAssistantMessage(data.answer || 'Mohon maaf, terjadi kendala saat memproses jawaban.', data.sources || [], timeStr);
            }
        })
        .catch(err => {
            loadingIndicator.style.display = 'none';
            chatInput.disabled = false;
            btnSend.disabled = false;
            chatInput.focus();
            appendAssistantMessage('Terjadi kesalahan jaringan. Silakan periksa koneksi Anda dan coba lagi.', [], timeStr);
        });
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const q = chatInput.value.trim();
        if (q) sendQuestion(q);
    });

    // FAQ Chips Click
    faqChips.forEach(chip => {
        chip.addEventListener('click', function() {
            const q = this.getAttribute('data-question');
            if (q) sendQuestion(q);
        });
    });

    // Clear Chat History
    btnClearChat.addEventListener('click', function() {
        if (!confirm('Apakah Anda ingin memulai percakapan baru?')) return;

        fetch("{{ route('klien.assistant.clear') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(res => res.json())
        .then(data => {
            window.location.reload();
        });
    });
});
</script>
@endpush

@endsection
