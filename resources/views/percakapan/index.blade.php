@extends($isAdvokat ? 'layouts.advokat' : 'layouts.klien')

@section('title', 'Percakapan')
@section('header-title', 'Percakapan')

@push('styles')
<style>
    /* Chat Layout Container */
    .chat-page-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
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
        font-size: 1.4rem;
        font-weight: 700;
        color: #0b1a30;
        margin-bottom: 4px;
        letter-spacing: -0.01em;
    }

    /* 2-Pane WhatsApp Layout Card */
    .chat-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: grid;
        grid-template-columns: 340px 1fr;
        height: calc(100vh - 190px);
        min-height: 580px;
        max-height: 840px;
        position: relative;
        overflow: hidden;
    }

    @media (max-width: 900px) {
        .chat-main-card {
            grid-template-columns: 1fr;
        }
        .chat-left-pane {
            display: var(--mobile-left-display, block);
        }
        .chat-right-pane {
            display: var(--mobile-right-display, none);
        }
    }

    /* ══════════ LEFT PANE: DAFTAR PERCAKAPAN ══════════ */
    .chat-left-pane {
        border-right: 1px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .chat-left-header {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .chat-search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .chat-search-icon {
        position: absolute;
        left: 12px;
        width: 16px;
        height: 16px;
        color: #94a3b8;
        pointer-events: none;
    }

    .chat-search-input {
        width: 100%;
        padding: 9px 12px 9px 36px;
        font-size: 0.84rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .chat-search-input:focus {
        border-color: #0b1a30;
        box-shadow: 0 0 0 2px rgba(11, 26, 48, 0.1);
    }

    .chat-conversations-list {
        flex: 1;
        overflow-y: auto;
        padding: 6px 0;
    }

    .conversation-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid #f8fafc;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s;
        position: relative;
    }

    .conversation-item:hover {
        background: #f8fafc;
    }

    .conversation-item.active {
        background: #f1f5f9;
        border-left: 3px solid #0b1a30;
    }

    .conv-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #1e3a5f;
        color: #ffffff;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .conv-info {
        flex: 1;
        min-width: 0;
    }

    .conv-top-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2px;
    }

    .conv-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: #0b1a30;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 160px;
    }

    .conv-time {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 500;
        white-space: nowrap;
    }

    .conv-context-tag {
        font-size: 0.72rem;
        color: #0369a1;
        background: #e0f2fe;
        padding: 1px 7px;
        border-radius: 4px;
        display: inline-block;
        font-weight: 600;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px;
    }

    .conv-bottom-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .conv-snippet {
        font-size: 0.8rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
    }

    .conv-unread-badge {
        background: #ef4444;
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
        flex-shrink: 0;
    }

    .conv-empty-list {
        padding: 40px 20px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.85rem;
    }

    /* ══════════ RIGHT PANE: DETAIL PERCAKAPAN ══════════ */
    .chat-right-pane {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #f8fafc;
        overflow: hidden;
        position: relative;
    }

    /* Top Contact Bar */
    .chat-contact-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 24px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        z-index: 10;
    }

    .contact-profile-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .contact-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #0b1a30;
        color: #ffffff;
        font-weight: 700;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .contact-details h3 {
        font-size: 0.98rem;
        font-weight: 700;
        color: #0b1a30;
        margin: 0 0 2px 0;
    }

    .contact-role-badge {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Context Banner */
    .chat-case-banner {
        background: #f1f5f9;
        border-bottom: 1px solid #e2e8f0;
        padding: 9px 24px;
        font-size: 0.82rem;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .case-label {
        font-weight: 600;
        color: #1e3a5f;
    }

    .case-title {
        font-weight: 600;
        color: #0b1a30;
    }

    /* Message Thread Area */
    .chat-messages-thread {
        flex: 1;
        overflow-y: auto;
        padding: 20px 24px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        background: #fdfdfd;
    }

    /* Date Separator */
    .chat-date-separator {
        text-align: center;
        position: relative;
        margin: 10px 0 14px 0;
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
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 500;
        z-index: 2;
        border: 1px solid #e2e8f0;
    }

    /* Message Bubbles */
    .message-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        width: 100%;
    }

    .message-row.incoming {
        justify-content: flex-start;
    }

    .message-row.outgoing {
        justify-content: flex-end;
    }

    .msg-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #1e3a5f;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-bottom: 4px;
    }

    .message-content-box {
        display: flex;
        flex-direction: column;
        max-width: 72%;
    }

    .message-row.incoming .message-content-box {
        align-items: flex-start;
    }

    .message-row.outgoing .message-content-box {
        align-items: flex-end;
    }

    .message-bubble {
        padding: 10px 16px;
        font-size: 0.88rem;
        line-height: 1.5;
        word-break: break-word;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .message-row.incoming .message-bubble {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        border-bottom-left-radius: 2px;
    }

    .message-row.outgoing .message-bubble {
        background: #0b1a30;
        color: #ffffff;
        border: 1px solid #0b1a30;
        border-radius: 14px;
        border-bottom-right-radius: 2px;
    }

    .message-time {
        font-size: 0.7rem;
        color: #94a3b8;
        margin-top: 3px;
        padding: 0 4px;
    }

    /* Input Footer */
    .chat-input-footer {
        padding: 14px 20px;
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
        border-color: #0b1a30;
        box-shadow: 0 0 0 3px rgba(11, 26, 48, 0.1);
    }

    .btn-attachment-disabled {
        background: none;
        border: none;
        color: #cbd5e1;
        cursor: not-allowed;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
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
        width: 36px;
        height: 36px;
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

    .btn-send-message:hover:not(:disabled) {
        background: #1e293b;
        transform: translateY(-1px);
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

    {{-- Main 2-Pane Card --}}
    <div class="chat-main-card">

        {{-- ══════════ LEFT PANE: DAFTAR PERCAKAPAN ══════════ --}}
        <div class="chat-left-pane">
            <div class="chat-left-header">
                <div class="chat-search-wrapper">
                    <svg class="chat-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text"
                           id="searchConversationInput"
                           class="chat-search-input"
                           placeholder="Cari percakapan..."
                           oninput="filterConversationList(this.value)">
                </div>
            </div>

            <div class="chat-conversations-list" id="conversationListContainer">
                @forelse($conversations as $conv)
                    @php
                        $otherUser = $conv->getOtherParticipant($user->id);
                        $otherName = $otherUser?->name ?? 'Pengguna';
                        $otherInitial = strtoupper(substr($otherName, 0, 1));
                        $isActive = ($activeConversation && $activeConversation->id === $conv->id);

                        $contextText = $conv->legalCase
                            ? ($conv->legalCase->case_number ? "Perkara: {$conv->legalCase->case_number}" : "Perkara: {$conv->legalCase->title}")
                            : ($conv->consultation ? "Konsultasi: {$conv->consultation->title}" : ($conv->title ?? 'Konsultasi'));

                        $lastMsg = $conv->latestMessage;
                        $snippet = $lastMsg ? \Illuminate\Support\Str::limit($lastMsg->body, 34) : 'Belum ada pesan.';
                        $msgTime = $lastMsg ? $lastMsg->created_at->format('H.i') : '';
                        $unreadCount = $conv->unreadCountFor($user->id);
                        $chatUrl = $isAdvokat
                            ? route('advokat.chat', ['conversation_id' => $conv->id])
                            : route('klien.chat', ['conversation_id' => $conv->id]);
                    @endphp

                    <a href="{{ $chatUrl }}"
                       class="conversation-item {{ $isActive ? 'active' : '' }}"
                       data-search="{{ strtolower($otherName . ' ' . $contextText . ' ' . $snippet) }}">
                        <div class="conv-avatar">
                            {{ $otherInitial }}
                        </div>
                        <div class="conv-info">
                            <div class="conv-top-row">
                                <span class="conv-name">{{ $otherName }}</span>
                                <span class="conv-time">{{ $msgTime }}</span>
                            </div>
                            <div class="conv-context-tag">
                                {{ $contextText }}
                            </div>
                            <div class="conv-bottom-row">
                                <span class="conv-snippet">{{ $snippet }}</span>
                                @if($unreadCount > 0)
                                    <span class="conv-unread-badge">{{ $unreadCount }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="conv-empty-list">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom:8px;">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <p style="margin:0; font-weight:500; color:#64748b;">Belum ada percakapan</p>
                        <p style="margin:4px 0 0 0; font-size:0.75rem; color:#94a3b8;">Percakapan aktif setelah konsultasi atau perkara ditetapkan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ══════════ RIGHT PANE: DETAIL PERCAKAPAN ══════════ --}}
        <div class="chat-right-pane">
            @if($activeConversation)
                @php
                    $otherParticipant = $activeConversation->getOtherParticipant($user->id);
                    $otherName = $otherParticipant?->name ?? 'Pengguna';
                    $otherRole = ($otherParticipant?->role === 'advokat') ? 'Advokat' : 'Klien';
                    $otherInitial = strtoupper(substr($otherName, 0, 1));

                    if ($activeConversation->legalCase) {
                        $caseNum = $activeConversation->legalCase->case_number;
                        $caseTitleText = $activeConversation->legalCase->title;
                        $fullContext = $caseNum ? "Perkara: {$caseNum} — {$caseTitleText}" : "Perkara: {$caseTitleText}";
                    } elseif ($activeConversation->consultation) {
                        $fullContext = "Konsultasi: " . $activeConversation->consultation->title;
                    } else {
                        $fullContext = $activeConversation->title ?? 'Konsultasi Hukum';
                    }
                @endphp

                {{-- Top Contact Bar (Tanpa fake online status) --}}
                <div class="chat-contact-bar">
                    <div class="contact-profile-info">
                        <div class="contact-avatar">
                            {{ $otherInitial }}
                        </div>
                        <div class="contact-details">
                            <h3>{{ $otherName }}</h3>
                            <div class="contact-role-badge">
                                {{ $otherRole }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Context Banner --}}
                <div class="chat-case-banner">
                    <span class="case-label">Konteks:</span>
                    <span class="case-title">{{ $fullContext }}</span>
                </div>

                {{-- Message Thread --}}
                <div class="chat-messages-thread" id="chatThread">
                    @php $lastDate = null; @endphp

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
                            <p style="margin: 0; font-size: 0.9rem; font-weight:500;">Belum ada riwayat pesan dalam percakapan ini.</p>
                            <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #94a3b8;">Kirim pesan di bawah untuk memulai obrolan.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Input Toolbar --}}
                <div class="chat-input-footer">
                    <form id="chatForm" onsubmit="handleSendMessage(event)">
                        <div class="chat-input-wrapper">
                            {{-- Attachment button disabled per scope agreement --}}
                            <button type="button" class="btn-attachment-disabled" title="Lampiran berkas dapat diunggah melalui menu Dokumen" disabled>
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                {{-- Empty State jika belum memilih percakapan --}}
                <div class="chat-empty-state">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <h3 style="color:#0b1a30; font-size:1.05rem; margin:0 0 6px 0;">Pilih Percakapan</h3>
                    <p style="font-size:0.85rem; color:#64748b; max-width:320px; line-height:1.5; margin:0;">
                        Pilih percakapan dari daftar di sebelah kiri untuk melihat pesan atau mengirim pesan.
                    </p>
                </div>
            @endif
        </div>
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

    function filterConversationList(val) {
        const query = (val || '').toLowerCase().trim();
        const items = document.querySelectorAll('.conversation-item');
        items.forEach(item => {
            const data = (item.dataset.search || '').toLowerCase();
            item.style.display = data.includes(query) ? 'flex' : 'none';
        });
    }

    function scrollToBottom() {
        const thread = document.getElementById('chatThread');
        if (thread) {
            thread.scrollTop = thread.scrollHeight;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        scrollToBottom();
        setupRealtimeOrPolling();
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function appendMessageRow(msg) {
        if (!msg || !msg.id) return;
        if (document.getElementById('msg-' + msg.id)) return; // Prevent duplicate

        const thread = document.getElementById('chatThread');
        if (!thread) return;

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
                console.error('Failed to send message, HTTP status:', response.status);
            }
        } catch (err) {
            console.error('Error sending message:', err);
        } finally {
            btnSend.disabled = false;
        }
    }

    /**
     * Requirement 2: Polling fallback ONLY if WebSocket is not connected
     * Check real WebSocket connection state: window.Echo?.connector?.pusher?.connection?.state === 'connected'
     */
    function isWebSocketConnected() {
        try {
            return Boolean(
                window.Echo &&
                window.Echo.connector &&
                window.Echo.connector.pusher &&
                window.Echo.connector.pusher.connection &&
                window.Echo.connector.pusher.connection.state === 'connected'
            );
        } catch (e) {
            return false;
        }
    }

    function setupRealtimeOrPolling() {
        if (!conversationId || !fetchUrl) return;

        // 1. Attempt Echo broadcast listener if Echo exists
        try {
            if (window.Echo) {
                window.Echo.private(`conversation.${conversationId}`)
                    .listen('.message.sent', (data) => {
                        if (data && data.id) {
                            appendMessageRow(data);
                        }
                    });
            }
        } catch (err) {
            console.info('Echo listener skipped:', err);
        }

        // 2. Setup Polling Fallback (every 3.5s, only if WS not connected and tab is active)
        setInterval(async () => {
            if (document.hidden) return; // Don't poll when inactive tab
            if (isWebSocketConnected()) return; // Don't poll if WebSocket is actively connected

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
        }, 3500);
    }
</script>
@endpush
