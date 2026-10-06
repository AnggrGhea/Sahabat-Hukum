@extends('layouts.klien')

@section('title', 'Pemberitahuan')
@section('header-title', 'Pemberitahuan')

@section('content')

{{-- Flash Message --}}
@if(session('success'))
<div style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.875rem;font-weight:500;display:flex;align-items:center;gap:8px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px;flex-shrink:0;">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    <span>{{ session('success') }}</span>
</div>
@endif

{{-- Breadcrumb --}}
<div class="breadcrumb" style="margin-bottom:16px;">
    <a href="{{ route('klien.dashboard') }}">Beranda</a>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    <span class="current">Pemberitahuan</span>
</div>

{{-- Page Title & Actions --}}
<div class="page-title-row" style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div class="page-heading">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--gray-900,#0f172a);margin:0 0 4px 0;">Pemberitahuan</h1>
        <p style="font-size:0.875rem;color:var(--gray-500,#64748b);margin:0;">Pantau pembaruan permohonan konsultasi, jadwal, perkara, dokumen, dan percakapan Anda.</p>
    </div>
    @if($unreadCount > 0)
    <form action="{{ route('klien.notifications.mark-all-read') }}" method="POST">
        @csrf
        <button type="submit" class="btn" style="background:#fff;border:1px solid #cbd5e1;color:#334155;font-weight:600;font-size:0.825rem;padding:8px 16px;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            Tandai Semua Sudah Dibaca
        </button>
    </form>
    @endif
</div>

{{-- Filter Tabs --}}
<div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
    <a href="{{ route('klien.notifications', ['filter' => 'all']) }}"
       style="text-decoration:none;font-size:0.875rem;font-weight:600;padding:6px 14px;border-radius:20px;transition:all .15s;{{ $filter === 'all' ? 'background:#0b1a30;color:#fff;' : 'background:#f1f5f9;color:#64748b;' }}">
        Semua ({{ $totalCount }})
    </a>
    <a href="{{ route('klien.notifications', ['filter' => 'unread']) }}"
       style="text-decoration:none;font-size:0.875rem;font-weight:600;padding:6px 14px;border-radius:20px;transition:all .15s;display:inline-flex;align-items:center;gap:6px;{{ $filter === 'unread' ? 'background:#0b1a30;color:#fff;' : 'background:#f1f5f9;color:#64748b;' }}">
        <span>Belum Dibaca</span>
        @if($unreadCount > 0)
        <span style="background:#ef4444;color:#fff;font-size:0.7rem;padding:1px 6px;border-radius:10px;font-weight:700;">{{ $unreadCount }}</span>
        @endif
    </a>
</div>

{{-- Notification Items List --}}
<div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px;">
    @forelse($notifications as $notification)
        @php
            $data = $notification->data;
            $isUnread = is_null($notification->read_at);
            $actionUrl = $data['action_url'] ?? route('klien.dashboard');
            $icon = $data['icon'] ?? 'bell';
            $color = $data['color'] ?? 'blue';

            // Background & text accent colors
            $iconBg = match($color) {
                'green' => '#dcfce7',
                'gold', 'amber', 'orange' => '#fef3c7',
                'red' => '#fee2e2',
                'navy' => '#e0e7ff',
                default => '#e0f2fe'
            };
            $iconColor = match($color) {
                'green' => '#16a34a',
                'gold', 'amber', 'orange' => '#d97706',
                'red' => '#dc2626',
                'navy' => '#1e3a5f',
                default => '#0284c7'
            };
        @endphp

        <div style="background:#fff;border-radius:10px;border:1px solid {{ $isUnread ? '#bfdbfe' : '#e2e8f0' }};border-left:5px solid {{ $isUnread ? '#2563eb' : '#94a3b8' }};box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:16px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;transition:all 0.15s ease;"
             onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.08)';"
             onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.04)';">

            {{-- Icon + Content --}}
            <div style="display:flex;align-items:flex-start;gap:16px;flex:1;">
                {{-- Category Icon Avatar --}}
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $iconBg }};color:{{ $iconColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    @if($icon === 'message-square')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    @elseif($icon === 'calendar')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    @elseif($icon === 'briefcase')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    @elseif($icon === 'check-circle')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    @elseif($icon === 'alert-triangle')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    @elseif($icon === 'file-plus' || $icon === 'file-text')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    @elseif($icon === 'message-circle')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    @elseif($icon === 'user-check')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7.5" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    @endif
                </div>

                {{-- Text Information --}}
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                        <h4 style="margin:0;font-size:0.95rem;font-weight:700;color:var(--gray-900,#0f172a);">
                            {{ $data['title'] ?? 'Pemberitahuan' }}
                        </h4>
                        @if($isUnread)
                        <span style="background:#fee2e2;color:#dc2626;font-size:0.685rem;font-weight:700;padding:2px 8px;border-radius:12px;">Baru</span>
                        @endif
                        <span style="font-size:0.75rem;color:#94a3b8;margin-left:auto;">
                            {{ $notification->created_at->locale('id')->diffForHumans() }}
                        </span>
                    </div>

                    <p style="margin:0 0 10px 0;font-size:0.875rem;color:var(--gray-600,#475569);line-height:1.5;">
                        {{ $data['message'] ?? '-' }}
                    </p>

                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <a href="{{ route('klien.notifications.open', $notification->id) }}"
                           style="display:inline-flex;align-items:center;gap:5px;font-size:0.825rem;font-weight:600;color:#0b1a30;text-decoration:none;padding:4px 10px;border-radius:6px;background:#f1f5f9;transition:all .15s;"
                           onmouseover="this.style.background='#e2e8f0';"
                           onmouseout="this.style.background='#f1f5f9';">
                            Lihat Rincian
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>

                        @if($isUnread)
                        <form action="{{ route('klien.notifications.read', $notification->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit"
                                    style="background:none;border:none;color:#64748b;font-size:0.8rem;cursor:pointer;padding:4px 8px;text-decoration:underline;">
                                Tandai sudah dibaca
                            </button>
                        </form>
                        @else
                        <span style="font-size:0.75rem;color:#94a3b8;display:inline-flex;align-items:center;gap:4px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><polyline points="20 6 9 17 4 12"/></svg>
                            Sudah dibaca
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        {{-- Empty State --}}
        <div style="background:#fff;border-radius:12px;border:1px dashed #cbd5e1;padding:48px 24px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
            <div style="width:56px;height:56px;background:#f1f5f9;color:#64748b;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:28px;height:28px;">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--gray-800,#1e293b);margin:0 0 6px 0;">
                @if($filter === 'unread')
                    Tidak Ada Notifikasi Belum Dibaca
                @else
                    Belum Ada Pemberitahuan
                @endif
            </h3>
            <p style="font-size:0.875rem;color:var(--gray-500,#64748b);margin:0 0 16px 0;max-width:440px;margin-left:auto;margin-right:auto;line-height:1.5;">
                @if($filter === 'unread')
                    Semua notifikasi Anda telah ditandai sebagai sudah dibaca. Anda dapat melihat kembali riwayat di tab "Semua".
                @else
                    Setiap pembaruan terkait permohonan konsultasi, jadwal sidang/pertemuan, perkara, dokumen, dan pesan percakapan akan otomatis muncul di sini.
                @endif
            </p>
            @if($filter === 'unread')
                <a href="{{ route('klien.notifications', ['filter' => 'all']) }}" class="btn btn-primary" style="display:inline-flex;padding:8px 18px;font-size:0.85rem;">
                    Lihat Semua Riwayat
                </a>
            @else
                <a href="{{ route('klien.consultations') }}" class="btn btn-primary" style="display:inline-flex;padding:8px 18px;font-size:0.85rem;">
                    Ajukan Konsultasi
                </a>
            @endif
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($notifications->hasPages())
<div style="margin-top:20px;">
    {{ $notifications->links() }}
</div>
@endif

@endsection
