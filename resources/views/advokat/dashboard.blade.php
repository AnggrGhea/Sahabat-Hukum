@extends('layouts.advokat')

@section('title', 'Beranda')

@section('content')
<div class="greeting-section">
    <h1 class="greeting-title">Selamat Pagi, {{ Auth::check() ? explode(' ', trim(str_replace('Adv.', '', Auth::user()->name)))[0] : 'Advokat' }}.</h1>
    <p class="greeting-date">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
</div>

{{-- Stats Grid --}}
<div class="premium-stats-grid">
    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper blue">
            <i data-lucide="message-square"></i>
        </div>
        <div class="premium-stat-number">{{ $stats['new_consultations'] }}</div>
        <div class="premium-stat-label">Konsultasi Baru</div>
    </div>

    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper green">
            <i data-lucide="briefcase"></i>
        </div>
        <div class="premium-stat-number">{{ $stats['active_cases'] }}</div>
        <div class="premium-stat-label">Perkara Aktif</div>
    </div>

    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper orange">
            <i data-lucide="file-text"></i>
        </div>
        <div class="premium-stat-number">{{ $stats['pending_documents'] }}</div>
        <div class="premium-stat-label">Dokumen Diperiksa</div>
    </div>

    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper purple">
            <i data-lucide="calendar"></i>
        </div>
        <div class="premium-stat-number">{{ $stats['today_schedules'] }}</div>
        <div class="premium-stat-label">Jadwal Hari Ini</div>
    </div>
</div>

{{-- Bottom Column Grid --}}
<div class="dashboard-grid">
    {{-- Left: Agenda Hari Ini --}}
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <h2 class="dashboard-card-title">Agenda Hari Ini</h2>
            <a href="#" class="dashboard-card-link">Kelola Jadwal <i data-lucide="chevron-right" style="width: 16px;"></i></a>
        </div>

        <div class="agenda-list">
            @forelse($todaySchedules as $schedule)
            @php
                $color = str_contains(strtolower($schedule->title), 'sidang') ? 'gold' : 'blue';
                $badge = str_contains(strtolower($schedule->title), 'sidang') ? 'Sidang' : 'Konsultasi';
            @endphp
            <div class="agenda-row">
                <div class="agenda-left-bar {{ $color }}"></div>
                <div class="agenda-time-box">
                    <span class="agenda-time {{ $color }}">{{ $schedule->start_at->format('H.i') }}</span>
                    <span class="agenda-tz">WIB</span>
                </div>
                <div class="agenda-info-box">
                    <div class="agenda-details-wrapper">
                        <span class="agenda-title">{{ $schedule->title }}</span>
                        <span class="agenda-subtitle">{{ $schedule->location }}</span>
                    </div>
                    <span class="agenda-badge {{ $color }}">{{ $badge }}</span>
                </div>
            </div>
            @empty
            <div style="text-align:center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;">
                <i data-lucide="calendar-x" style="width:32px;height:32px;margin-bottom:8px;display:block;margin-inline:auto;"></i>
                Tidak ada agenda hari ini.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Right: Konsultasi Baru --}}
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <h2 class="dashboard-card-title">Konsultasi Baru</h2>
            @if($stats['new_consultations'] > 0)
            <span class="header-badge-red">{{ $stats['new_consultations'] }} baru</span>
            @endif
        </div>

        <div class="consultation-list">
            @forelse($newConsultations as $konsultasi)
            <a href="{{ route('advokat.consultations.show', $konsultasi->id) }}" class="consultation-row" style="text-decoration:none;display:flex;align-items:center;padding:12px 0;border-bottom:1px solid #f1f5f9;gap:12px;">
                <div class="consultation-avatar">{{ strtoupper(substr($konsultasi->client->name, 0, 1)) }}</div>
                <div class="consultation-details">
                    <span class="consultation-name">{{ $konsultasi->client->name }}</span>
                    <span class="consultation-topic">{{ $konsultasi->title }}</span>
                    <span class="consultation-date">{{ \Carbon\Carbon::parse($konsultasi->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</span>
                </div>
                <span class="consultation-status-badge">Menunggu</span>
            </a>
            @empty
            <div style="text-align:center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;">
                <i data-lucide="check-circle" style="width:32px;height:32px;margin-bottom:8px;display:block;margin-inline:auto;color:#22c55e;"></i>
                Tidak ada konsultasi baru.
            </div>
            @endforelse
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <a href="{{ route('advokat.consultations') }}" class="dashboard-card-link" style="justify-content: flex-end; color: var(--color-stat-blue-icon);">
                Kelola Semua Konsultasi <i data-lucide="arrow-right" style="width: 16px;"></i>
            </a>
        </div>
    </div>
</div>
@endsection
