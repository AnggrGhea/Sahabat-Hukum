@extends($isAdvokat ? 'layouts.advokat' : 'layouts.klien')

@section('title', 'Percakapan')
@section('header-title', 'Percakapan')

@push('styles')
<style>
    /* Chat Container Styles */
    .chat-page-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 4px;
    }

    .chat-breadcrumb {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 2px;
    }
    .chat-breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }
    .chat-breadcrumb a:hover {
        color: #0b1a30;
    }

    .chat-title-header {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0b1a30;
        margin-bottom: 16px;
        letter-spacing: -0.01em;
    }

    .chat-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
        min-height: 560px;
        height: calc(100vh - 200px);
        max-height: 820px;
        position: relative;
        overflow: hidden;
    }

    /* Top Contact Bar */
    .chat-contact-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        z-index: 10;
    }

    .contact-profile-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .contact-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: #0b1a30;
        color: #ffffff;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(11, 26, 48, 0.15);
    }

    .contact-details h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #0b1a30;
        margin: 0 0 2px 0;
    }

    .contact-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        color: #10b981;
        font-weight: 500;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
    }

    .contact-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-icon-circle {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-icon-circle:hover {
        background: #f8fafc;
        color: #0b1a30;
        border-color: #cbd5e1;
    }

    /* Case Banner */
    .chat-case-banner {
        background: #f8fafc;
        border-bottom: 1px solid #edf2f7;
        padding: 10px 24px;
        font-size: 0.84rem;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .case-label {
        font-weight: 600;
        color: #64748b;
    }

    .case-title {
        font-weight: 600;
        color: #0b1a30;
    }

    /* Message Thread Area */
    .chat-messages-thread {
        flex: 1;
        overflow-y: auto;
        padding: 24px 28px;
        display: flex;
        flex-direction: column;
        gap: 18px;
        background: #fafafa;
    }

    /* Date Separator */
    .chat-date-separator {
        text-align: center;
        position: relative;
        margin: 10px 0 16px 0;
    }

    .chat-date-separator::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        height: 1px;
        background: #e2e8f0;
        z-index: 1;
    }

    .chat-date-pill {
        position: relative;
        display: inline-block;
        background: #f8fafc;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 0.76rem;
        color: #64748b;
        font-weight: 500;
        z-index: 2;
        border: 1px solid #e2e8f0;
    }

    /* Message Bubbles */
    .message-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        width: 100%;
    }

    .message-row.incoming {
        justify-content: flex-start;
    }

    .message-row.outgoing {
        justify-content: flex-end;
    }

    .msg-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #0b1a30;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .message-content-box {
        display: flex;
        flex-direction: column;
        max-width: 68%;
    }

    .message-row.incoming .message-content-box {
        align-items: flex-start;
    }

    .message-row.outgoing .message-content-box {
        align-items: flex-end;
    }

    .message-bubble {
        padding: 13px 18px;
        font-size: 0.9rem;
        line-height: 1.55;
        word-break: break-word;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .message-row.incoming .message-bubble {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        border-top-left-radius: 4px;
    }

    .message-row.outgoing .message-bubble {
        background: #0b1a30;
        color: #ffffff;
        border: 1px solid #0b1a30;
        border-radius: 16px;
        border-top-right-radius: 4px;
    }

    .message-time {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-top: 4px;
        padding: 0 4px;
    }

    /* Input Footer */
    .chat-input-footer {
        padding: 16px 24px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
    }

    .chat-input-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 6px 10px 6px 14px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .chat-input-wrapper:focus-within {
        border-color: #c3a167;
        box-shadow: 0 0 0 3px rgba(195, 161, 103, 0.15);
    }

    .btn-attachment {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s;
        border-radius: 6px;
    }

    .btn-attachment:hover {
        color: #0b1a30;
        background: #f1f5f9;
    }

    .chat-text-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 0.9rem;
        color: #1e293b;
        background: transparent;
        padding: 6px 4px;
    }

    .chat-text-input::placeholder {
        color: #94a3b8;
    }

    .btn-send-message {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: none;
        background: #0b1a30;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }

    .btn-send-message:hover {
        background: #1e293b;
        transform: translateY(-1px);
    }

    .btn-send-message:active {
        transform: translateY(0);
    }

    .btn-send-message:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* Empty state */
    .chat-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #64748b;
        padding: 40px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="chat-page-container">
    {{-- Breadcrumb --}}
    <div class="chat-breadcrumb">
        <a href="{{ $isAdvokat ? route('advokat.dashboard') : route('klien.dashboard') }}">Beranda</a> &rsaquo; Percakapan
    </div>

    {{-- Title Header --}}
    <div class="chat-title-header">
        Percakapan
    </div>

    {{-- Main Chat Card --}}
    <div class="chat-main-card">
        @if($activeConversation)
            @php
                $otherParticipant = $activeConversation->getOtherParticipant($user->id);
                $otherName = $otherParticipant?->name ?? 'Pengguna';
                $otherInitial = strtoupper(substr($otherName, 0, 1));
                $caseTitle = $activeConversation->legalCase?->case_number 
                    ? ($activeConversation->legalCase->case_number . ' — ' . $activeConversation->legalCase->title)
                    : ($activeConversation->title ?? 'Konsultasi Hukum');
            @endphp

            {{-- Top Contact Bar --}}
            <div class="chat-contact-bar">
                <div class="contact-profile-info">
                    <div class="contact-avatar">
                        {{ $otherInitial }}
                    </div>
                    <div class="contact-details">
                        <h3>{{ $otherName }}</h3>
                        <div class="contact-status">
                            <span class="status-dot"></span> Online
                        </div>
                    </div>
                </div>

                <div class="contact-actions">
                    <button class="btn-icon-circle" type="button" title="Panggil">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </button>
                    <button class="btn-icon-circle" type="button" title="Opsi Percakapan">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="19" cy="12" r="1"></circle>
                            <circle cx="5" cy="12" r="1"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Perkara Banner --}}
            <div class="chat-case-banner">
                <span class="case-label">Perkara:</span>
                <span class="case-title">{{ $caseTitle }}</span>
            </div>

            {{-- Message Thread --}}
            <div class="chat-messages-thread" id="chatThread">
                @php
                    $lastDate = null;
                @endphp

                @forelse($messages as $msg)
                    @php
                        $msgDate = $msg->created_at->translatedFormat('l, d F Y');
                        $isMe = ((int)$msg->sender_id === (int)$user->id);
                        $senderInitial = strtoupper(substr($msg->sender?->name ?? 'P', 0, 1));
                    @endphp

                    @if($lastDate !== $msgDate)
                        <div class="chat-date-separator">
                            <span class="chat-date-pill">{{ $msgDate }}</span>
                        </div>
                        @php $lastDate = $msgDate; @endphp
                    @endif

                    <div class="message-row {{ $isMe ? 'outgoing' : 'incoming' }}" id="msg-{{ $msg->id }}" data-id="{{ $msg->id }}">
                        @if(!$isMe)
                            <div class="msg-avatar">{{ $senderInitial }}</div>
                        @endif

                        <div class="message-content-box">
                            <div class="message-bubble">
                                {{ $msg->body }}
                            </div>
                            <span class="message-time">{{ $msg->created_at->format('H.i') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="chat-empty-state" id="emptyNotice">
                        <p style="margin: 0; font-size: 0.9rem;">Belum ada riwayat pesan dalam percakapan ini.</p>
                        <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #94a3b8;">Kirim pesan di bawah untuk memulai obrolan.</p>
                    </div>
                @endforelse
            </div>

            {{-- Input Toolbar --}}
            <div class="chat-input-footer">
                <form id="chatForm" onsubmit="handleSendMessage(event)">
                    <div class="chat-input-wrapper">
                        <button type="button" class="btn-attachment" title="Lampirkan berkas">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                            </svg>
                        </button>

                        <input type="text"
                               id="chatInput"
                               class="chat-text-input"
                               placeholder="Tulis pesan..."
                               autocomplete="off"
                               required>

                        <button type="submit" id="btnSend" class="btn-send-message" title="Kirim Pesan">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="chat-empty-state">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3 style="color:#0b1a30; font-size:1.1rem; margin-bottom:4px;">Belum Ada Percakapan Aktif</h3>
                <p style="font-size:0.875rem; color:#64748b; max-width:380px;">Percakapan akan aktif secara otomatis saat Anda memiliki perkara atau konsultasi yang sedang ditangani.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const conversationId = {{ $activeConversation ? $activeConversation->id : 'null' }};
    const currentUserId = {{ $user->id }};
    let lastMessageId = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};
    const sendUrl = "{{ $activeConversation ? ($isAdvokat ? route('advokat.chat.send', $activeConversation->id) : route('klien.chat.send', $activeConversation->id)) : '' }}";
    const fetchUrl = "{{ $activeConversation ? ($isAdvokat ? route('advokat.chat.messages', $activeConversation->id) : route('klien.chat.messages', $activeConversation->id)) : '' }}";
    const csrfToken = "{{ csrf_token() }}";

    function scrollToBottom() {
        const thread = document.getElementById('chatThread');
        if (thread) {
            thread.scrollTop = thread.scrollHeight;
        }
    }

    // Scroll to bottom on initial page load
    document.addEventListener('DOMContentLoaded', () => {
        scrollToBottom();
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function appendMessageRow(msg) {
        if (!msg || !msg.id) return;
        if (document.getElementById('msg-' + msg.id)) return; // prevent duplicate

        const thread = document.getElementById('chatThread');
        if (!thread) return;

        // Hide empty notice if present
        const emptyNotice = document.getElementById('emptyNotice');
        if (emptyNotice) {
            emptyNotice.remove();
        }

        const isMe = (msg.is_me === true || parseInt(msg.sender_id) === currentUserId);
        const senderInitial = (msg.sender_name || 'P').charAt(0).toUpperCase();

        const row = document.createElement('div');
        row.className = `message-row ${isMe ? 'outgoing' : 'incoming'}`;
        row.id = `msg-${msg.id}`;
        row.dataset.id = msg.id;

        let avatarHtml = '';
        if (!isMe) {
            avatarHtml = `<div class="msg-avatar">${senderInitial}</div>`;
        }

        row.innerHTML = `
            ${avatarHtml}
            <div class="message-content-box">
                <div class="message-bubble">${escapeHtml(msg.body)}</div>
                <span class="message-time">${escapeHtml(msg.formatted_time || '')}</span>
            </div>
        `;

        thread.appendChild(row);
        lastMessageId = Math.max(lastMessageId, parseInt(msg.id));
        scrollToBottom();
    }

    async function handleSendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('chatInput');
        const btnSend = document.getElementById('btnSend');
        if (!input || !sendUrl) return;

        const text = input.value.trim();
        if (!text) return;

        btnSend.disabled = true;

        try {
            const response = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: text })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.message) {
                    appendMessageRow(data.message);
                    input.value = '';
                    input.focus();
                }
            } else {
                console.error('Failed to send message:', response.status);
            }
        } catch (err) {
            console.error('Error sending message:', err);
        } finally {
            btnSend.disabled = false;
        }
    }

    // Resilient Polling Fallback (every 3 seconds)
    if (conversationId && fetchUrl) {
        setInterval(async () => {
            if (document.hidden) return; // Don't poll aggressively when tab is inactive

            try {
                const url = `${fetchUrl}?after_id=${lastMessageId}`;
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && Array.isArray(data.messages)) {
                        data.messages.forEach(msg => {
                            appendMessageRow(msg);
                        });
                    }
                }
            } catch (err) {
                // Silently ignore network hiccup during background polling
            }
        }, 3000);
    }
</script>
@endpush
