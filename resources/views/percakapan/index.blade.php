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

    /* 2-Pane Chat Card */
    .chat-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: grid;
        grid-template-columns: 360px 1fr;
        height: calc(100vh - 190px);
        min-height: 580px;
        max-height: 860px;
        position: relative;
        overflow: hidden;
    }

    @media (max-width: 900px) {
        .chat-main-card {
            grid-template-columns: 1fr;
        }
        .chat-left-pane {
            display: var(--mobile-left-display, flex);
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
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .chat-search-form {
        margin: 0;
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
        padding: 9px 34px 9px 36px;
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

    .chat-search-clear {
        position: absolute;
        right: 10px;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 4px;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .chat-search-clear:hover {
        color: #475569;
        background: #e2e8f0;
    }

    .chat-search-hint {
        margin-top: 6px;
        font-size: 0.72rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chat-conversations-list {
        flex: 1;
        overflow-y: auto;
        padding: 4px 0;
    }

    .conversation-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid #f8fafc;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s, border-left-color 0.15s;
        position: relative;
        border-left: 3px solid transparent;
    }

    .conversation-item:hover {
        background: #f8fafc;
    }

    .conversation-item.active {
        background: #f1f5f9;
        border-left-color: #0b1a30;
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
        gap: 6px;
        margin-bottom: 3px;
    }

    .conv-title-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
        flex: 1;
    }

    .conv-name {
        font-size: 0.88rem;
        font-weight: 600;
        color: #0b1a30;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .conv-role-pill {
        font-size: 0.68rem;
        padding: 1px 6px;
        border-radius: 4px;
        font-weight: 600;
        white-space: nowrap;
        background: #f1f5f9;
        color: #475569;
        flex-shrink: 0;
    }

    .conv-role-pill.advokat {
        background: #e0f2fe;
        color: #0369a1;
    }

    .conv-role-pill.klien {
        background: #f3e8ff;
        color: #7e22ce;
    }

    .conv-time {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 500;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Context Badges */
    .conv-context-row {
        margin-bottom: 4px;
    }

    .conv-context-badge {
        font-size: 0.72rem;
        padding: 2px 7px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 250px;
    }

    .conv-context-badge.perkara {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    .conv-context-badge.konsultasi {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .conv-context-badge.umum {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
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

    /* Empty state inside left pane */
    .conv-empty-list {
        padding: 44px 20px;
        text-align: center;
        color: #64748b;
    }

    .conv-empty-list h4 {
        margin: 10px 0 4px 0;
        font-size: 0.94rem;
        font-weight: 600;
        color: #0b1a30;
    }

    .conv-empty-list p {
        margin: 0;
        font-size: 0.78rem;
        color: #94a3b8;
        line-height: 1.5;
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
        padding: 12px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        z-index: 10;
        gap: 12px;
    }

    .contact-profile-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1;
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

    .contact-details {
        min-width: 0;
        flex: 1;
    }

    .contact-name-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .contact-details h3 {
        font-size: 0.98rem;
        font-weight: 700;
        color: #0b1a30;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .contact-role-badge {
        font-size: 0.72rem;
        padding: 2px 7px;
        border-radius: 4px;
        font-weight: 600;
        background: #e0f2fe;
        color: #0369a1;
        white-space: nowrap;
    }

    .contact-role-badge.klien {
        background: #f3e8ff;
        color: #7e22ce;
    }

    .contact-context-sub {
        font-size: 0.78rem;
        color: #64748b;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Action buttons in header (Lihat Perkara / Lihat Konsultasi) */
    .contact-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-header-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .btn-header-action.btn-case {
        background: #0b1a30;
        color: #ffffff;
    }
    .btn-header-action.btn-case:hover {
        background: #1e3a5f;
    }

    .btn-header-action.btn-consult {
        background: #f8fafc;
        color: #0b1a30;
        border-color: #cbd5e1;
    }
    .btn-header-action.btn-consult:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    /* Context Banner */
    .chat-case-banner {
        background: #f1f5f9;
        border-bottom: 1px solid #e2e8f0;
        padding: 8px 20px;
        font-size: 0.81rem;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .chat-case-banner-left {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        flex: 1;
    }

    .case-label {
        font-weight: 600;
        color: #1e3a5f;
        flex-shrink: 0;
    }

    .case-title {
        font-weight: 600;
        color: #0b1a30;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
        padding: 12px 20px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        position: relative;
    }

    .chat-input-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 6px 10px 6px 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .chat-input-wrapper:focus-within {
        border-color: #0b1a30;
        box-shadow: 0 0 0 3px rgba(11, 26, 48, 0.1);
    }

    .btn-attachment {
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.15s ease;
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
        background: #1e3a5f;
        transform: translateY(-1px);
    }

    .btn-send-message:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* Attachment Popover Modal */
    .attachment-popover {
        position: absolute;
        bottom: 74px;
        left: 20px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 14px;
        width: 300px;
        z-index: 100;
        display: none;
    }

    .attachment-popover-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0b1a30;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .attachment-popover-desc {
        font-size: 0.74rem;
        color: #64748b;
        line-height: 1.4;
        margin-bottom: 12px;
    }

    .attachment-popover-btn {
        display: block;
        width: 100%;
        text-align: center;
        padding: 7px 10px;
        background: #0b1a30;
        color: #ffffff;
        font-size: 0.76rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
    }

    .attachment-popover-btn:hover {
        background: #1e3a5f;
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

    .empty-state-icon-wrap {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        color: #94a3b8;
    }

    .empty-state-title {
        color: #0b1a30;
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0 0 6px 0;
    }

    .empty-state-desc {
        font-size: 0.85rem;
        color: #64748b;
        max-width: 360px;
        line-height: 1.5;
        margin: 0;
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
                <form id="searchForm" class="chat-search-form" onsubmit="handleSearchSubmit(event)">
                    <div class="chat-search-wrapper">
                        <svg class="chat-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text"
                               id="searchConversationInput"
                               name="q"
                               class="chat-search-input"
                               placeholder="Cari percakapan, nama, perkara..."
                               value="{{ $search ?? '' }}"
                               autocomplete="off"
                               oninput="handleSearchInput(this.value)">
                        <button type="button"
                                id="clearSearchBtn"
                                class="chat-search-clear"
                                title="Hapus pencarian"
                                onclick="clearSearch()">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="chat-conversations-list" id="conversationListContainer">
                @forelse($conversations as $conv)
                    @php
                        $otherUser = $conv->getOtherParticipant($user->id);
                        $otherName = $otherUser?->name ?? 'Pengguna';
                        $otherInitial = strtoupper(substr($otherName, 0, 1));
                        $isActive = ($activeConversation && $activeConversation->id === $conv->id);

                        // Counterparty role/status
                        if ($otherUser?->role === 'advokat') {
                            $spec = $otherUser->lawyerProfile?->specialization;
                            $otherRoleBadge = $spec ? 'Advokat • ' . $spec : 'Advokat';
                            $roleClass = 'advokat';
                        } else {
                            $otherRoleBadge = 'Klien';
                            $roleClass = 'klien';
                        }

                        // Context details
                        if ($conv->legalCase) {
                            $contextType = 'perkara';
                            $caseNum = $conv->legalCase->case_number;
                            $caseTitle = $conv->legalCase->title;
                            $contextText = $caseNum ? "Perkara: {$caseNum} — {$caseTitle}" : "Perkara: {$caseTitle}";
                        } elseif ($conv->consultation) {
                            $contextType = 'konsultasi';
                            $contextText = "Konsultasi: " . $conv->consultation->title;
                        } else {
                            $contextType = 'umum';
                            $contextText = $conv->title ?? 'Percakapan';
                        }

                        $lastMsg = $conv->latestMessage;
                        $snippet = $lastMsg ? \Illuminate\Support\Str::limit($lastMsg->body, 36) : 'Belum ada pesan.';
                        $msgTime = $lastMsg ? $lastMsg->created_at->format('H.i') : '';
                        $unreadCount = $conv->unreadCountFor($user->id);
                        $chatUrl = $isAdvokat
                            ? route('advokat.chat', ['conversation_id' => $conv->id])
                            : route('klien.chat', ['conversation_id' => $conv->id]);

                        // Search index corpus (client, lawyer, role, case number, case title, consultation title, snippet)
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $otherName,
                            $otherRoleBadge,
                            $conv->client?->name,
                            $conv->lawyer?->name,
                            $conv->legalCase?->case_number,
                            str_replace(['/', '.'], ' ', (string)$conv->legalCase?->case_number),
                            $conv->legalCase?->title,
                            $conv->consultation?->title,
                            $snippet,
                        ])));
                    @endphp

                    <a href="{{ $chatUrl }}"
                       class="conversation-item {{ $isActive ? 'active' : '' }}"
                       data-id="{{ $conv->id }}"
                       data-search="{{ $searchCorpus }}">
                        <div class="conv-avatar">
                            {{ $otherInitial }}
                        </div>
                        <div class="conv-info">
                            <div class="conv-top-row">
                                <div class="conv-title-wrap">
                                    <span class="conv-name" title="{{ $otherName }}">{{ $otherName }}</span>
                                    <span class="conv-role-pill {{ $roleClass }}">{{ $otherRoleBadge }}</span>
                                </div>
                                <span class="conv-time">{{ $msgTime }}</span>
                            </div>

                            <div class="conv-context-row">
                                <span class="conv-context-badge {{ $contextType }}" title="{{ $contextText }}">
                                    @if($contextType === 'perkara')
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                        </svg>
                                    @elseif($contextType === 'konsultasi')
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                    @endif
                                    <span>{{ $contextText }}</span>
                                </span>
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
                    @if(!empty($search))
                        <div class="conv-empty-list" id="convEmptyList">
                            <div class="empty-state-icon-wrap" style="width:50px;height:50px;margin:0 auto 10px auto;">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <h4>Tidak ada hasil pencarian</h4>
                            <p>Tidak ditemukan percakapan yang cocok dengan <strong>"{{ $search }}"</strong>.</p>
                            <button type="button" onclick="clearSearch()" style="margin-top:10px;padding:5px 12px;font-size:0.75rem;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;">
                                Hapus Pencarian
                            </button>
                        </div>
                    @else
                        {{-- Empty state resmi saat belum ada percakapan sama sekali --}}
                        <div class="conv-empty-list" id="convEmptyList">
                            <div class="empty-state-icon-wrap" style="width:50px;height:50px;margin:0 auto 10px auto;">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </div>
                            <h4>Belum ada percakapan</h4>
                            <p>Percakapan tersedia setelah konsultasi ditangani atau perkara ditetapkan.</p>
                        </div>
                    @endif
                @endforelse

                {{-- Client-side search empty message (hidden by default) --}}
                <div class="conv-empty-list" id="clientSearchEmptyNotice" style="display:none;">
                    <div class="empty-state-icon-wrap" style="width:48px;height:48px;margin:0 auto 8px auto;">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <h4>Tidak ditemukan</h4>
                    <p id="clientSearchEmptyText">Tidak ada percakapan yang cocok.</p>
                    <button type="button" onclick="clearSearch()" style="margin-top:8px;padding:4px 10px;font-size:0.75rem;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        {{-- ══════════ RIGHT PANE: DETAIL PERCAKAPAN ══════════ --}}
        <div class="chat-right-pane">
            @if($activeConversation)
                @php
                    $otherParticipant = $activeConversation->getOtherParticipant($user->id);
                    $otherName = $otherParticipant?->name ?? 'Pengguna';
                    $otherRole = ($otherParticipant?->role === 'advokat')
                        ? ($otherParticipant->lawyerProfile?->specialization ? 'Advokat • ' . $otherParticipant->lawyerProfile->specialization : 'Advokat')
                        : 'Klien';
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

                    $caseObj = $activeConversation->legalCase;
                    $consultObj = $activeConversation->consultation;
                @endphp

                {{-- Top Contact Bar --}}
                <div class="chat-contact-bar">
                    <div class="contact-profile-info">
                        <div class="contact-avatar">
                            {{ $otherInitial }}
                        </div>
                        <div class="contact-details">
                            <div class="contact-name-row">
                                <h3>{{ $otherName }}</h3>
                                <span class="contact-role-badge {{ $otherParticipant?->role === 'advokat' ? '' : 'klien' }}">
                                    {{ $otherRole }}
                                </span>
                            </div>
                            <div class="contact-context-sub">
                                {{ $fullContext }}
                            </div>
                        </div>
                    </div>

                    {{-- Action buttons: Lihat Perkara atau Lihat Konsultasi --}}
                    <div class="contact-actions">
                        @if($caseObj)
                            @php
                                $caseUrl = $isAdvokat
                                    ? route('advokat.cases.show', $caseObj->id)
                                    : route('klien.cases.show', $caseObj->id);
                            @endphp
                            <a href="{{ $caseUrl }}" class="btn-header-action btn-case" title="Lihat detail berkas dan progres perkara ini">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                </svg>
                                <span>Lihat Perkara</span>
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        @elseif($consultObj)
                            @php
                                $consultUrl = $isAdvokat
                                    ? route('advokat.consultations.show', $consultObj->id)
                                    : route('klien.consultations.show', $consultObj->id);
                            @endphp
                            <a href="{{ $consultUrl }}" class="btn-header-action btn-consult" title="Lihat jadwal dan ringkasan konsultasi">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                <span>Lihat Konsultasi</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Context Banner --}}
                <div class="chat-case-banner">
                    <div class="chat-case-banner-left">
                        <span class="case-label">
                            @if($caseObj)
                                Perkara:
                            @elseif($consultObj)
                                Konsultasi:
                            @else
                                Konteks:
                            @endif
                        </span>
                        <span class="case-title" title="{{ $fullContext }}">{{ $fullContext }}</span>
                    </div>

                    @if($caseObj)
                        <div style="flex-shrink:0;">
                            <span style="font-size:0.72rem;background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px;font-weight:600;">
                                Terhubung dengan Perkara
                            </span>
                        </div>
                    @endif
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
                            <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #94a3b8;">Kirim pesan di bawah untuk memulai obrolan dengan {{ $otherName }}.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Input Toolbar --}}
                <div class="chat-input-footer">
                    {{-- Attachment helper popover --}}
                    <div id="attachmentPopover" class="attachment-popover">
                        <div class="attachment-popover-title">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                            </svg>
                            Berkas Dokumen Perkara
                        </div>
                        <p class="attachment-popover-desc">
                            Seluruh dokumen hukum dan alat bukti perkara dikelola, dipindai, dan diverifikasi secara terpusat pada modul <strong>Dokumen Perkara</strong>.
                        </p>
                        @if($caseObj)
                            @php
                                $docUrl = $isAdvokat
                                    ? route('advokat.documents', ['case_id' => $caseObj->id])
                                    : route('klien.documents', ['case_id' => $caseObj->id]);
                            @endphp
                            <a href="{{ $docUrl }}" class="attachment-popover-btn" target="_blank">
                                Buka Dokumen Perkara Ini &rarr;
                            </a>
                        @else
                            @php
                                $generalDocUrl = $isAdvokat ? route('advokat.documents') : route('klien.documents');
                            @endphp
                            <a href="{{ $generalDocUrl }}" class="attachment-popover-btn" target="_blank">
                                Buka Menu Dokumen &rarr;
                            </a>
                        @endif
                    </div>

                    <form id="chatForm" onsubmit="handleSendMessage(event)">
                        <div class="chat-input-wrapper">
                            {{-- Attachment action button --}}
                            <button type="button"
                                    class="btn-attachment"
                                    id="btnAttachment"
                                    title="Kelola dokumen atau bukti perkara"
                                    onclick="toggleAttachmentPopover()">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                </svg>
                            </button>

                            <input type="text"
                                   id="chatInput"
                                   class="chat-text-input"
                                   placeholder="Tulis pesan untuk {{ $otherName }}..."
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
                {{-- Empty State jika belum ada atau belum memilih percakapan --}}
                <div class="chat-empty-state">
                    <div class="empty-state-icon-wrap">
                        <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>

                    @if($conversations->isEmpty())
                        <h3 class="empty-state-title">Belum ada percakapan</h3>
                        <p class="empty-state-desc">
                            Percakapan tersedia setelah konsultasi ditangani atau perkara ditetapkan.
                        </p>
                    @else
                        <h3 class="empty-state-title">Pilih Percakapan</h3>
                        <p class="empty-state-desc">
                            Pilih percakapan dari daftar di sebelah kiri untuk melihat riwayat pesan atau mengirim pesan.
                        </p>
                    @endif
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
    const chatBaseUrl = "{{ $isAdvokat ? route('advokat.chat') : route('klien.chat') }}";

    // ── Live Instant Search & Filter ──
    let searchDebounceTimer = null;

    function handleSearchInput(val) {
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) {
            clearBtn.style.display = val && val.trim().length > 0 ? 'flex' : 'none';
        }

        // Instant local filter on conversation items in DOM
        filterConversationList(val);

        // Debounced deep search (checks message body on server if no local match or multi-word)
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            const query = (val || '').trim();
            if (query.length >= 2) {
                performDeepSearch(query);
            }
        }, 500);
    }

    function filterConversationList(val) {
        const query = (val || '').toLowerCase().trim();
        const items = document.querySelectorAll('.conversation-item');
        const emptyNotice = document.getElementById('clientSearchEmptyNotice');
        const emptyText = document.getElementById('clientSearchEmptyText');

        if (!items || items.length === 0) return;

        let visibleCount = 0;
        items.forEach(item => {
            const searchData = (item.dataset.search || '').toLowerCase();
            const matches = !query || searchData.includes(query);
            item.style.display = matches ? 'flex' : 'none';
            if (matches) visibleCount++;
        });

        if (emptyNotice) {
            if (visibleCount === 0 && query.length > 0) {
                if (emptyText) emptyText.textContent = `Tidak ada percakapan yang cocok dengan "${val}".`;
                emptyNotice.style.display = 'block';
            } else {
                emptyNotice.style.display = 'none';
            }
        }
    }

    async function performDeepSearch(query) {
        // Query backend database to search client, lawyer, case, consultation, and message bodies
        try {
            const url = `${chatBaseUrl}?q=${encodeURIComponent(query)}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && Array.isArray(data.conversations)) {
                    const matchedIds = new Set(data.conversations.map(c => String(c.id)));
                    const items = document.querySelectorAll('.conversation-item');
                    let count = 0;

                    items.forEach(item => {
                        const id = String(item.dataset.id);
                        const localMatches = (item.dataset.search || '').toLowerCase().includes(query.toLowerCase());
                        if (matchedIds.has(id) || localMatches) {
                            item.style.display = 'flex';
                            count++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    const emptyNotice = document.getElementById('clientSearchEmptyNotice');
                    const emptyText = document.getElementById('clientSearchEmptyText');
                    if (emptyNotice) {
                        if (count === 0 && query.length > 0) {
                            if (emptyText) emptyText.textContent = `Tidak ada percakapan atau isi pesan yang cocok dengan "${query}".`;
                            emptyNotice.style.display = 'block';
                        } else {
                            emptyNotice.style.display = 'none';
                        }
                    }
                }
            }
        } catch (e) {
            // Silently fall back to local filtering
        }
    }

    function handleSearchSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('searchConversationInput');
        if (input) {
            const query = input.value.trim();
            filterConversationList(query);
            performDeepSearch(query);
        }
    }

    function clearSearch() {
        const input = document.getElementById('searchConversationInput');
        if (input) {
            input.value = '';
            handleSearchInput('');
            input.focus();
        }
    }

    // ── Attachment Popover ──
    function toggleAttachmentPopover() {
        const popover = document.getElementById('attachmentPopover');
        if (popover) {
            const isVisible = popover.style.display === 'block';
            popover.style.display = isVisible ? 'none' : 'block';
        }
    }

    // Close attachment popover on click outside
    document.addEventListener('click', (e) => {
        const popover = document.getElementById('attachmentPopover');
        const btn = document.getElementById('btnAttachment');
        if (popover && popover.style.display === 'block') {
            if (!popover.contains(e.target) && !btn.contains(e.target)) {
                popover.style.display = 'none';
            }
        }
    });

    // ── Chat Scrolling ──
    function scrollToBottom() {
        const thread = document.getElementById('chatThread');
        if (thread) {
            thread.scrollTop = thread.scrollHeight;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        scrollToBottom();
        setupRealtimeOrPolling();

        const searchInput = document.getElementById('searchConversationInput');
        if (searchInput && searchInput.value) {
            handleSearchInput(searchInput.value);
        }
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
