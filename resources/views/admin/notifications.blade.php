@extends('layouts.admin')

@section('title', 'Pemberitahuan Sistem')

@section('content')

{{-- Flash Message --}}
@if(session('success'))
<div style="background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:.875rem; font-weight:500; display:flex; align-items:center; gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px;color:#15803d;"></i>
    {{ session('success') }}
</div>
@endif

<div class="card" style="padding:24px;">
    {{-- Header & Title Bar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;border-bottom:1px solid var(--color-border,#e2e8f0);padding-bottom:16px;">
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;color:var(--color-navy,#0b1a30);margin:0 0 4px 0;display:flex;align-items:center;gap:8px;">
                <i data-lucide="bell" style="width:20px;height:20px;color:var(--color-gold,#c5a059);"></i>
                Pemberitahuan Aktivitas Sistem
            </h2>
            <p style="font-size:0.875rem;color:var(--color-gray-text,#64748b);margin:0;">
                Pantau pendaftaran klien baru, pengajuan konsultasi, pembentukan perkara, dan dokumen penting sistem.
            </p>
        </div>

        @if($unreadCount > 0)
        <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn" style="background:#fff;border:1px solid #cbd5e1;color:#334155;font-weight:600;font-size:0.825rem;padding:8px 16px;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s;">
                <i data-lucide="check-check" style="width:16px;height:16px;color:#2563eb;"></i>
                Tandai Semua Sudah Dibaca
            </button>
        </form>
        @endif
    </div>

    {{-- Filter Tabs --}}
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
        <a href="{{ route('admin.notifications', ['filter' => 'all']) }}"
           style="text-decoration:none;font-size:0.875rem;font-weight:600;padding:6px 14px;border-radius:20px;transition:all .15s;{{ $filter === 'all' ? 'background:var(--color-navy,#0b1a30);color:#fff;' : 'background:#f1f5f9;color:#64748b;' }}">
            Semua ({{ $totalCount }})
        </a>
        <a href="{{ route('admin.notifications', ['filter' => 'unread']) }}"
           style="text-decoration:none;font-size:0.875rem;font-weight:600;padding:6px 14px;border-radius:20px;transition:all .15s;display:inline-flex;align-items:center;gap:6px;{{ $filter === 'unread' ? 'background:var(--color-navy,#0b1a30);color:#fff;' : 'background:#f1f5f9;color:#64748b;' }}">
            <span>Belum Dibaca</span>
            @if($unreadCount > 0)
            <span style="background:var(--color-red,#ef4444);color:#fff;font-size:0.7rem;padding:1px 6px;border-radius:10px;font-weight:700;">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>

    {{-- Notification List --}}
    <div style="display:flex;flex-direction:column;gap:12px;">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $actionUrl = $data['action_url'] ?? route('admin.dashboard');
                $icon = $data['icon'] ?? 'bell';
                $color = $data['color'] ?? 'blue';

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

            <div style="background:#fff;border-radius:10px;border:1px solid {{ $isUnread ? '#bfdbfe' : '#e2e8f0' }};border-left:5px solid {{ $isUnread ? 'var(--color-gold,#c5a059)' : '#94a3b8' }};box-shadow:0 1px 3px rgba(0,0,0,0.03);padding:16px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;transition:all 0.15s ease;"
                 onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.06)';"
                 onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">

                <div style="display:flex;align-items:flex-start;gap:16px;flex:1;">
                    {{-- Category Icon --}}
                    <div style="width:42px;height:42px;border-radius:10px;background:{{ $iconBg }};color:{{ $iconColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        @if($icon === 'user-plus')
                            <i data-lucide="user-plus" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'message-square')
                            <i data-lucide="message-square" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'folder-open' || $icon === 'folder-plus')
                            <i data-lucide="folder-open" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'file-text' || $icon === 'file-plus')
                            <i data-lucide="file-text" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'activity')
                            <i data-lucide="activity" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'briefcase')
                            <i data-lucide="briefcase" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'check-circle')
                            <i data-lucide="check-circle" style="width:20px;height:20px;"></i>
                        @else
                            <i data-lucide="bell" style="width:20px;height:20px;"></i>
                        @endif
                    </div>

                    {{-- Text Info --}}
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                            <h4 style="margin:0;font-size:0.95rem;font-weight:700;color:var(--color-navy,#0b1a30);">
                                {{ $data['title'] ?? 'Pemberitahuan Sistem' }}
                            </h4>
                            @if($isUnread)
                            <span style="background:#fee2e2;color:#dc2626;font-size:0.685rem;font-weight:700;padding:2px 8px;border-radius:12px;">Baru</span>
                            @endif
                            <span style="font-size:0.75rem;color:#94a3b8;margin-left:auto;">
                                {{ $notification->created_at->locale('id')->diffForHumans() }}
                            </span>
                        </div>

                        <p style="margin:0 0 10px 0;font-size:0.875rem;color:var(--color-gray-dark,#334155);line-height:1.5;">
                            {{ $data['message'] ?? '-' }}
                        </p>

                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <a href="{{ route('admin.notifications.open', $notification->id) }}"
                               style="display:inline-flex;align-items:center;gap:5px;font-size:0.825rem;font-weight:600;color:var(--color-navy,#0b1a30);text-decoration:none;padding:5px 12px;border-radius:6px;background:#f1f5f9;transition:all .15s;"
                               onmouseover="this.style.background='#e2e8f0';"
                               onmouseout="this.style.background='#f1f5f9';">
                                Buka Data Terkait
                                <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                            </a>

                            @if($isUnread)
                            <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit"
                                        style="background:none;border:none;color:#64748b;font-size:0.8rem;cursor:pointer;padding:4px 8px;text-decoration:underline;">
                                    Tandai sudah dibaca
                                </button>
                            </form>
                            @else
                            <span style="font-size:0.75rem;color:#94a3b8;display:inline-flex;align-items:center;gap:4px;">
                                <i data-lucide="check" style="width:12px;height:12px;"></i>
                                Sudah dibaca
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div style="border-radius:12px;border:1px dashed #cbd5e1;padding:48px 24px;text-align:center;background:#fafbfc;">
                <div style="width:56px;height:56px;background:#f1f5f9;color:#64748b;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i data-lucide="bell" style="width:28px;height:28px;"></i>
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;color:var(--color-navy,#0b1a30);margin:0 0 6px 0;">
                    @if($filter === 'unread')
                        Tidak Ada Pemberitahuan Belum Dibaca
                    @else
                        Belum Ada Pemberitahuan Masuk
                    @endif
                </h3>
                <p style="font-size:0.875rem;color:var(--color-gray-text,#64748b);margin:0 0 16px 0;max-width:440px;margin-left:auto;margin-right:auto;line-height:1.5;">
                    @if($filter === 'unread')
                        Seluruh notifikasi aktivitas sistem telah Anda tinjau dan tandai sebagai sudah dibaca.
                    @else
                        Setiap pendaftaran klien baru, pengajuan konsultasi, penambahan perkara, dan unggahan berkas akan dicatat di sini.
                    @endif
                </p>
                @if($filter === 'unread')
                    <a href="{{ route('admin.notifications', ['filter' => 'all']) }}" class="btn" style="display:inline-flex;background:var(--color-navy,#0b1a30);color:#fff;padding:8px 18px;font-size:0.85rem;border-radius:8px;text-decoration:none;">
                        Lihat Semua Riwayat
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
</div>

@endsection
