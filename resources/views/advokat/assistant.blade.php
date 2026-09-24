@extends('layouts.advokat')

@section('title', 'Asisten Hukum')

@section('content')

{{-- Header Banner Card --}}
<div class="card" style="margin-bottom:16px;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;background:#0b1a30;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(11,26,48,0.15);">
            <i data-lucide="scale" style="width:24px;height:24px;color:#c3a167;"></i>
        </div>
        <div>
            <h1 style="font-size:1.2rem;font-weight:700;color:#0b1a30;margin-bottom:2px;letter-spacing:-0.01em;">Asisten Hukum</h1>
            <p style="font-size:.85rem;color:#64748b;margin:0;">Temukan informasi dan dasar hukum berdasarkan sumber hukum yang tersedia.</p>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:10px;">
        <button id="btnClearChat" type="button" title="Bersihkan Percakapan"
                style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;color:#475569;border-radius:8px;font-size:.825rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s;">
            <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
            Percakapan Baru
        </button>
    </div>
</div>

@if(!$isApiConfigured)
<div style="background:#fffbeb;border:1px solid #fef3c7;color:#b45309;border-radius:10px;padding:14px 18px;margin-bottom:16px;font-size:.85rem;display:flex;align-items:center;gap:12px;">
    <i data-lucide="alert-triangle" style="width:20px;height:20px;flex-shrink:0;color:#d97706;"></i>
    <div>
        <strong>Perhatian Konfigurasi:</strong> Kunci API Gemini (<code>GEMINI_API_KEY</code>) belum terpasang di file <code>.env</code>.
        Pencarian regulasi JDIH BPK tetap berjalan, namun generasi sintesis telaah hukum membutuhkan API Key.
    </div>
</div>
@endif

{{-- Main Chat Area Card --}}
<div class="card" style="padding:24px;min-height:560px;display:flex;flex-direction:column;justify-content:space-between;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">

    {{-- Chat Stream --}}
    <div id="chatMessages" style="flex:1;overflow-y:auto;max-height:560px;padding-right:8px;display:flex;flex-direction:column;gap:20px;">

        {{-- Initial Greeting Bubble for Advokat --}}
        <div class="msg-assistant-wrap" style="display:flex;align-items:flex-start;gap:12px;">
            <div style="width:36px;height:36px;background:#0b1a30;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="scale" style="width:18px;height:18px;color:#c3a167;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:4px;">Asisten Hukum • 09.00</div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:16px 20px;color:#1e293b;line-height:1.65;font-size:.875rem;max-width:760px;box-shadow:0 1px 2px rgba(0,0,0,0.02);">
                    Selamat datang di <strong>Asisten Riset Hukum Sahabat Hukum</strong>. Saya dapat membantu Rekan Advokat menemukan referensi dasar hukum, peraturan perundang-undangan resmi terverifikasi (JDIH BPK), status keberlakuan norma, dan pedoman perkara. Silakan ajukan topik hukum atau regulasi yang ingin ditelusuri.
                </div>
            </div>
        </div>

        {{-- Render Session History if available --}}
        @if(isset($messages) && count($messages) > 0)
            @foreach($messages as $msg)
                @if($msg['role'] === 'user')
                <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                    <div style="max-width:640px;">
                        <div style="font-size:.72rem;color:#94a3b8;text-align:right;margin-bottom:3px;">Anda • {{ $msg['time'] ?? '' }}</div>
                        <div style="background:#0b1a30;color:#fff;border-radius:12px 12px 2px 12px;padding:12px 18px;font-size:.875rem;line-height:1.5;box-shadow:0 2px 4px rgba(11,26,48,0.1);">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                </div>
                @else
                <div class="msg-assistant-wrap" style="display:flex;align-items:flex-start;gap:12px;margin-top:4px;">
                    <div style="width:36px;height:36px;background:#0b1a30;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="scale" style="width:18px;height:18px;color:#c3a167;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:4px;">Asisten Hukum • {{ $msg['time'] ?? '' }}</div>
                        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:18px 22px;color:#1e293b;line-height:1.65;font-size:.875rem;max-width:760px;box-shadow:0 1px 3px rgba(0,0,0,0.03);white-space:pre-wrap;">{!! nl2br(e($msg['content'])) !!}</div>
                        
                        @if(!empty($msg['sources']))
                        <div style="margin-top:12px;padding-top:10px;border-top:1px dashed #e2e8f0;">
                            <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">Rujukan / Sumber Hukum:</div>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                            @foreach($msg['sources'] as $src)
                                @php
                                    $title = is_array($src) ? ($src['title'] ?? 'Sumber Hukum') : $src;
                                    $url = is_array($src) ? ($src['url'] ?? null) : null;
                                    $institution = is_array($src) ? ($src['institution'] ?? null) : null;
                                    $status = is_array($src) ? ($src['status'] ?? null) : null;

                                    $badgeBg = '#f1f5f9';
                                    $badgeColor = '#334155';
                                    $badgeBorder = '#cbd5e1';

                                    if ($status === 'Terverifikasi Resmi') {
                                        $badgeBg = '#ecfdf5';
                                        $badgeColor = '#065f46';
                                        $badgeBorder = '#a7f3d0';
                                    } elseif ($status === 'Discovery / Perlu Verifikasi') {
                                        $badgeBg = '#fffbeb';
                                        $badgeColor = '#92400e';
                                        $badgeBorder = '#fde68a';
                                    } elseif ($status === 'Sumber Pemerintah Pendukung') {
                                        $badgeBg = '#eff6ff';
                                        $badgeColor = '#1e40af';
                                        $badgeBorder = '#bfdbfe';
                                    }
                                @endphp
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;background:#f8fafc;border:1px solid {{ $badgeBorder }};border-radius:8px;padding:6px 12px;font-size:.75rem;">
                                    <div style="display:flex;align-items:center;gap:6px;min-width:0;">
                                        @if($status)
                                        <span style="font-size:.65rem;background:{{ $badgeBg }};color:{{ $badgeColor }};border:1px solid {{ $badgeBorder }};padding:2px 6px;border-radius:4px;font-weight:700;flex-shrink:0;">{{ $status }}</span>
                                        @endif
                                        <span style="font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $title }}">{{ $title }}</span>
                                        @if($institution)
                                        <span style="font-size:.68rem;color:#64748b;flex-shrink:0;">({{ $institution }})</span>
                                        @endif
                                    </div>
                                    @if($url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="font-size:.72rem;background:#0b1a30;color:#fff;border-radius:4px;padding:3px 8px;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px;flex-shrink:0;">
                                        <i data-lucide="external-link" style="width:11px;height:11px;"></i> Lihat Sumber
                                    </a>
                                    @endif
                                </div>
                            @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        @endif

        {{-- FAQ / Quick Prompts for Advokat --}}
        <div id="faqSection" style="margin:20px auto 10px;width:100%;max-width:780px;">
            <div style="font-size:.78rem;color:#94a3b8;font-weight:700;text-align:center;margin-bottom:14px;letter-spacing:.04em;text-transform:uppercase;">
                Pencarian Cepat Dasar Hukum:
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(340px, 1fr));gap:10px;">
                @foreach($faqs as $faq)
                <button type="button" class="faq-chip" data-question="{{ $faq }}"
                        style="text-align:left;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;font-size:.825rem;color:#334155;cursor:pointer;transition:all .18s;box-shadow:0 1px 2px rgba(0,0,0,.02);display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <span>{{ $faq }}</span>
                    <i data-lucide="arrow-right" style="width:14px;height:14px;color:#94a3b8;flex-shrink:0;"></i>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Typing / Loading Indicator --}}
        <div id="loadingIndicator" style="display:none;align-items:flex-start;gap:12px;">
            <div style="width:36px;height:36px;background:#0b1a30;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="scale" style="width:18px;height:18px;color:#c3a167;"></i>
            </div>
            <div>
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:4px;">Asisten Hukum sedang menelaah sumber hukum...</div>
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:14px 20px;display:inline-flex;align-items:center;gap:8px;">
                    <span class="dot-typing"></span>
                    <span class="dot-typing"></span>
                    <span class="dot-typing"></span>
                </div>
            </div>
        </div>

    </div>

    {{-- Disclaimer & Input Bar --}}
    <div style="margin-top:20px;padding-top:14px;border-top:1px solid #f1f5f9;">

        {{-- Professional Advokat Disclaimer --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;border-radius:8px;padding:9px 16px;text-align:center;font-size:.76rem;font-weight:500;margin-bottom:12px;display:flex;align-items:center;justify-content:center;gap:8px;">
            <i data-lucide="shield-alert" style="width:14px;height:14px;color:#94a3b8;"></i>
            <span>Asisten Hukum memberikan informasi dan referensi berdasarkan sumber yang tersedia dan tidak menggantikan pertimbangan hukum profesional.</span>
        </div>

        {{-- Chat Input Form --}}
        <form id="chatForm" style="display:flex;align-items:center;gap:10px;background:#fff;border:1.5px solid #cbd5e1;border-radius:10px;padding:6px 8px 6px 16px;transition:border-color .15s, box-shadow .15s;">
            @csrf
            <input id="chatInput" type="text" name="message" required
                   placeholder="Ketik topik hukum, nomor regulasi, atau pasal yang ingin ditelusuri..." autocomplete="off"
                   style="flex:1;border:none;outline:none;font-size:.875rem;color:#0b1a30;background:transparent;padding:8px 0;">
            <button id="btnSend" type="submit"
                    style="width:40px;height:40px;border-radius:8px;border:none;background:#e2e8f0;color:#94a3b8;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;">
                <i data-lucide="send" style="width:18px;height:18px;"></i>
            </button>
        </form>
    </div>

</div>

@push('styles')
<style>
.faq-chip:hover {
    background: #f8fafc !important;
    border-color: #0b1a30 !important;
    color: #0b1a30 !important;
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.05) !important;
}
.faq-chip:hover i {
    color: #0b1a30 !important;
    transform: translateX(2px);
}
#chatForm:focus-within {
    border-color: #0b1a30 !important;
    box-shadow: 0 0 0 3px rgba(11,26,48,0.08) !important;
}
.dot-typing {
    width: 8px; height: 8px; background: #94a3b8; border-radius: 50%; display: inline-block;
    animation: waveDots 1.3s infinite ease-in-out both;
}
.dot-typing:nth-child(1) { animation-delay: -0.32s; }
.dot-typing:nth-child(2) { animation-delay: -0.16s; }
@keyframes waveDots {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40% { transform: scale(1); opacity: 1; background: #0b1a30; }
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

    if (window.lucide) {
        lucide.createIcons();
    }

    function updateSendButton() {
        if (chatInput.value.trim().length > 0) {
            btnSend.style.background = '#0b1a30';
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

    function appendUserMessage(text, time) {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;justify-content:flex-end;margin-top:4px;';
        wrap.innerHTML = `
            <div style="max-width:640px;">
                <div style="font-size:.72rem;color:#94a3b8;text-align:right;margin-bottom:3px;">Anda • ${time}</div>
                <div style="background:#0b1a30;color:#fff;border-radius:12px 12px 2px 12px;padding:12px 18px;font-size:.875rem;line-height:1.5;box-shadow:0 2px 4px rgba(11,26,48,0.1);">
                    ${escapeHtml(text)}
                </div>
            </div>
        `;
        chatMessages.insertBefore(wrap, loadingIndicator);
        scrollToBottom();
    }

    function appendAssistantMessage(text, sources, time) {
        const wrap = document.createElement('div');
        wrap.className = 'msg-assistant-wrap';
        wrap.style.cssText = 'display:flex;align-items:flex-start;gap:12px;margin-top:4px;';

        let sourcesHtml = '';
        if (sources && sources.length > 0) {
            sourcesHtml = `
                <div style="margin-top:12px;padding-top:10px;border-top:1px dashed #e2e8f0;">
                    <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">Rujukan / Sumber Hukum:</div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        ${sources.map(s => {
                            const title = escapeHtml(s.title || (typeof s === 'string' ? s : 'Sumber Hukum'));
                            const url = s.url || null;
                            const institution = s.institution ? escapeHtml(s.institution) : '';
                            const status = s.status || null;

                            let badgeBg = '#f1f5f9';
                            let badgeColor = '#334155';
                            let badgeBorder = '#cbd5e1';

                            if (status === 'Terverifikasi Resmi') {
                                badgeBg = '#ecfdf5';
                                badgeColor = '#065f46';
                                badgeBorder = '#a7f3d0';
                            } else if (status === 'Discovery / Perlu Verifikasi') {
                                badgeBg = '#fffbeb';
                                badgeColor = '#92400e';
                                badgeBorder = '#fde68a';
                            } else if (status === 'Sumber Pemerintah Pendukung') {
                                badgeBg = '#eff6ff';
                                badgeColor = '#1e40af';
                                badgeBorder = '#bfdbfe';
                            }

                            const statusHtml = status
                                ? `<span style="font-size:.65rem;background:${badgeBg};color:${badgeColor};border:1px solid ${badgeBorder};padding:2px 6px;border-radius:4px;font-weight:700;flex-shrink:0;">${escapeHtml(status)}</span>`
                                : '';

                            const instHtml = institution ? `<span style="font-size:.68rem;color:#64748b;flex-shrink:0;">(${institution})</span>` : '';

                            const actionBtn = url
                                ? `<a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" style="font-size:.72rem;background:#0b1a30;color:#fff;border-radius:4px;padding:3px 8px;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px;flex-shrink:0;">
                                     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                     Lihat Sumber
                                   </a>`
                                : '';

                            return `
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;background:#f8fafc;border:1px solid ${badgeBorder};border-radius:8px;padding:6px 12px;font-size:.75rem;">
                                    <div style="display:flex;align-items:center;gap:6px;min-width:0;">
                                        ${statusHtml}
                                        <span style="font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${title}">${title}</span>
                                        ${instHtml}
                                    </div>
                                    ${actionBtn}
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }

        let formattedText = formatMarkdown(escapeHtml(text));

        wrap.innerHTML = `
            <div style="width:36px;height:36px;background:#0b1a30;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c3a167" stroke-width="2" style="width:18px;height:18px;">
                    <path d="M12 3L2 7l2 .8V18c0 .6.4 1 1 1h2v1h10v-1h2c.6 0 1-.4 1-1V7.8L22 7 12 3zm-2 14H6v-7.6l4 1.6V17zm8 0h-4v-6l4-1.6V17zM12 11.2L4.8 8.4 12 5.6l7.2 2.8L12 11.2z"/>
                </svg>
            </div>
            <div style="flex:1;">
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:4px;">Asisten Hukum • ${time}</div>
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:18px 22px;color:#1e293b;line-height:1.65;font-size:.875rem;max-width:760px;box-shadow:0 1px 3px rgba(0,0,0,0.03);white-space:pre-wrap;">${formattedText}</div>
                ${sourcesHtml}
            </div>
        `;
        chatMessages.insertBefore(wrap, loadingIndicator);
        scrollToBottom();
    }

    function escapeHtml(str) {
        return (str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatMarkdown(str) {
        if (!str) return '';
        // If unclosed ** exists, balance it
        const boldCount = (str.match(/\*\*/g) || []).length;
        if (boldCount % 2 !== 0) {
            str += '**';
        }
        // Bold: **text** with dotAll support
        let formatted = str.replace(/\*\*(.+?)\*\*/gs, '<strong>$1</strong>');
        // Italic: *text*
        formatted = formatted.replace(/(?<!\*)\*(?!\*)([^\*\n]+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
        return formatted;
    }

    function sendQuestion(question) {
        if (!question) return;

        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + '.' + String(now.getMinutes()).padStart(2, '0');

        appendUserMessage(question, timeStr);
        chatInput.value = '';
        updateSendButton();

        loadingIndicator.style.display = 'flex';
        scrollToBottom();

        chatInput.disabled = true;
        btnSend.disabled = true;

        fetch("{{ route('advokat.assistant.chat') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ message: question })
        })
        .then(async res => {
            const data = await res.json().catch(() => null);
            if (!res.ok) {
                const msg = data && data.answer ? data.answer : 'Mohon maaf Rekan Advokat, penelusuran memerlukan waktu lebih lama dari perkiraan atau server sedang sibuk. Silakan coba ajukan pertanyaan Anda kembali sesaat lagi.';
                return { success: false, answer: msg, sources: [] };
            }
            return data || { success: false, answer: 'Mohon maaf, terjadi kendala saat memproses respons.', sources: [] };
        })
        .then(data => {
            loadingIndicator.style.display = 'none';
            chatInput.disabled = false;
            btnSend.disabled = false;
            chatInput.focus();

            appendAssistantMessage(data.answer, data.sources || [], data.time || timeStr);
        })
        .catch(err => {
            loadingIndicator.style.display = 'none';
            chatInput.disabled = false;
            btnSend.disabled = false;
            chatInput.focus();
            console.error('Chat error:', err);
            appendAssistantMessage('Mohon maaf Rekan Advokat, penelusuran memerlukan waktu lebih lama dari perkiraan atau server sedang sibuk. Silakan coba ajukan pertanyaan Anda kembali sesaat lagi.', [], timeStr);
        });
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const q = chatInput.value.trim();
        if (q) sendQuestion(q);
    });

    faqChips.forEach(chip => {
        chip.addEventListener('click', function() {
            const q = this.getAttribute('data-question');
            if (q) sendQuestion(q);
        });
    });

    btnClearChat.addEventListener('click', function() {
        if (!confirm('Apakah Anda ingin memulai sesi percakapan baru?')) return;

        fetch("{{ route('advokat.assistant.clear') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(res => res.json())
        .then(() => {
            window.location.reload();
        });
    });
});
</script>
@endpush

@endsection
