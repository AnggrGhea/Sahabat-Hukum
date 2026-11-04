@extends('layouts.advokat')

@section('title', 'Pemberitahuan Advokat')

@section('content')

{{-- Flash Message --}}
@if(session('success'))
<div style="background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:.875rem; font-weight:500; display:flex; align-items:center; gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px;color:#15803d;"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

<div class="card" style="padding:24px; background:#fff; border-radius:12px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
    {{-- Header & Title Bar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:16px;">
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin:0 0 4px 0;display:flex;align-items:center;gap:8px;">
                <i data-lucide="bell" style="width:20px;height:20px;color:#c5a059;"></i>
                Pemberitahuan Aktivitas Advokat
            </h2>
            <p style="font-size:0.875rem;color:#64748b;margin:0;">
                Pantau penugasan konsultasi baru, unggahan dokumen dari klien, pembaruan perkara, dan pesan klien.
            </p>
        </div>

        @if($unreadCount > 0)
        <form action="{{ route('advokat.notifications.mark-all-read') }}" method="POST">
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
        <a href="{{ route('advokat.notifications', ['filter' => 'all']) }}"
           style="text-decoration:none;font-size:0.875rem;font-weight:600;padding:6px 14px;border-radius:20px;transition:all .15s;{{ $filter === 'all' ? 'background:#1e3a5f;color:#fff;' : 'background:#f1f5f9;color:#64748b;' }}">
            Semua ({{ $totalCount }})
        </a>
        <a href="{{ route('advokat.notifications', ['filter' => 'unread']) }}"
           style="text-decoration:none;font-size:0.875rem;font-weight:600;padding:6px 14px;border-radius:20px;transition:all .15s;display:inline-flex;align-items:center;gap:6px;{{ $filter === 'unread' ? 'background:#1e3a5f;color:#fff;' : 'background:#f1f5f9;color:#64748b;' }}">
            <span>Belum Dibaca</span>
            @if($unreadCount > 0)
            <span style="background:#ef4444;color:#fff;font-size:0.7rem;padding:1px 6px;border-radius:10px;font-weight:700;">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>

    {{-- Notification List --}}
    <div style="display:flex;flex-direction:column;gap:12px;">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $actionUrl = $data['action_url'] ?? route('advokat.dashboard');
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

            <div style="background:#fff;border-radius:10px;border:1px solid {{ $isUnread ? '#bfdbfe' : '#e2e8f0' }};border-left:5px solid {{ $isUnread ? '#1e3a5f' : '#94a3b8' }};box-shadow:0 1px 3px rgba(0,0,0,0.03);padding:16px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;transition:all 0.15s ease;"
                 onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.06)';"
                 onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">

                <div style="display:flex;align-items:flex-start;gap:16px;flex:1;">
                    {{-- Category Icon --}}
                    <div style="width:42px;height:42px;border-radius:10px;background:{{ $iconBg }};color:{{ $iconColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        @if($icon === 'briefcase')
                            <i data-lucide="briefcase" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'message-circle' || $icon === 'message-square')
                            <i data-lucide="message-square" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'file-text' || $icon === 'file-plus')
                            <i data-lucide="file-text" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'calendar')
                            <i data-lucide="calendar" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'folder-open')
                            <i data-lucide="folder-open" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'check-circle')
                            <i data-lucide="check-circle" style="width:20px;height:20px;"></i>
                        @elseif($icon === 'alert-triangle')
                            <i data-lucide="alert-triangle" style="width:20px;height:20px;"></i>
                        @else
                            <i data-lucide="bell" style="width:20px;height:20px;"></i>
                        @endif
                    </div>

                    {{-- Text Info --}}
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                            <h4 style="font-size:0.95rem;font-weight:700;color:#1e293b;margin:0;">
                                {{ $data['title'] ?? 'Pemberitahuan Advokat' }}
                            </h4>
                            @if($isUnread)
                                <span style="background:#ef4444;color:#fff;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:10px;text-transform:uppercase;letter-spacing:0.04em;">
                                    Baru
                                </span>
                            @endif
                        </div>

                        <p style="font-size:0.875rem;color:#475569;margin:0 0 8px 0;line-height:1.5;">
                            {{ $data['message'] ?? '-' }}
                        </p>

                        <div style="display:flex;align-items:center;gap:12px;font-size:0.75rem;color:#94a3b8;">
                            <span style="display:inline-flex;align-items:center;gap:4px;">
                                <i data-lucide="clock" style="width:13px;height:13px;"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                            <span>•</span>
                            <span>{{ $notification->created_at->isoFormat('D MMMM YYYY, HH:mm') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    <a href="{{ route('advokat.notifications.open', $notification->id) }}"
                       class="btn"
                       style="background:#1e3a5f;color:#fff;font-size:0.8rem;font-weight:600;padding:6px 12px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .15s;"
                       title="Buka dan lihat rincian">
                        <span>Buka</span>
                        <i data-lucide="external-link" style="width:14px;height:14px;"></i>
                    </a>

                    @if($isUnread)
                    <form action="{{ route('advokat.notifications.read', $notification->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit"
                                class="btn"
                                style="background:#f1f5f9;border:1px solid #cbd5e1;color:#475569;font-size:0.8rem;font-weight:600;padding:6px 10px;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;transition:all .15s;"
                                title="Tandai Sudah Dibaca">
                            <i data-lucide="check" style="width:14px;height:14px;color:#16a34a;"></i>
                        </button>
                    </form>
                    @endif
                </div>

            </div>
        @empty
            <div style="text-align:center;padding:48px 24px;background:#f8fafc;border-radius:12px;border:1px dashed #cbd5e1;">
                <div style="width:56px;height:56px;background:#e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px auto;color:#64748b;">
                    <i data-lucide="bell-off" style="width:28px;height:28px;"></i>
                </div>
                <h3 style="font-size:1.05rem;font-weight:700;color:#1e293b;margin:0 0 6px 0;">
                    {{ $filter === 'unread' ? 'Tidak Ada Pemberitahuan Belum Dibaca' : 'Belum Ada Pemberitahuan' }}
                </h3>
                <p style="font-size:0.85rem;color:#64748b;margin:0 auto;max-width:440px;">
                    {{ $filter === 'unread' ? 'Semua pemberitahuan sudah Anda baca. Bagus!' : 'Saat ada penugasan konsultasi baru, dokumen yang diunggah klien, atau pesan baru, pemberitahuan akan tampil di sini.' }}
                </p>
                @if($filter === 'unread')
                <div style="margin-top:16px;">
                    <a href="{{ route('advokat.notifications', ['filter' => 'all']) }}"
                       style="font-size:0.825rem;color:#1e3a5f;font-weight:600;text-decoration:none;">
                        Tampilkan Semua Pemberitahuan &rarr;
                    </a>
                </div>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination Links --}}
    @if($notifications->hasPages())
    <div style="margin-top:24px;display:flex;justify-content:center;">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush

@endsection
