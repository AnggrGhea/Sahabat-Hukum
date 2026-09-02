@extends('layouts.admin')

@section('title', 'Beranda')

@section('content')
<div class="greeting-section">
    <h1 class="greeting-title">Selamat Pagi, {{ Auth::check() ? explode(' ', trim(Auth::user()->name))[0] : 'Admin' }}.</h1>
    <p class="greeting-date">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
</div>

<!-- Stats Grid -->
<div class="premium-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper blue">
            <i data-lucide="users"></i>
        </div>
        <div class="premium-stat-number">{{ $total_clients ?? 0 }}</div>
        <div class="premium-stat-label">Total Klien</div>
    </div>
    
    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper green">
            <i data-lucide="briefcase"></i>
        </div>
        <div class="premium-stat-number">{{ $total_lawyers ?? 0 }}</div>
        <div class="premium-stat-label">Total Advokat</div>
    </div>
    
    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper orange">
            <i data-lucide="folder"></i>
        </div>
        <div class="premium-stat-number">{{ $active_cases ?? 0 }}</div>
        <div class="premium-stat-label">Perkara Aktif</div>
    </div>
    
    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper purple">
            <i data-lucide="message-square"></i>
        </div>
        <div class="premium-stat-number">{{ $pending_consultations ?? 0 }}</div>
        <div class="premium-stat-label">Konsultasi Menunggu</div>
    </div>

    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper orange" style="background-color: #fffbeb; color: #b45309;">
            <i data-lucide="file-warning"></i>
        </div>
        <div class="premium-stat-number">{{ $pending_documents ?? 0 }}</div>
        <div class="premium-stat-label">Dokumen Menunggu</div>
    </div>

    <div class="premium-stat-card">
        <div class="premium-stat-icon-wrapper purple" style="background-color: #faf5ff; color: #7c3aed;">
            <i data-lucide="calendar"></i>
        </div>
        <div class="premium-stat-number">{{ $today_schedules ?? 0 }}</div>
        <div class="premium-stat-label">Jadwal Hari Ini</div>
    </div>
</div>

<!-- Bottom Column Grid -->
<div class="dashboard-grid">
    <!-- Left Column: Ringkasan Perkara Terbaru -->
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <h2 class="dashboard-card-title">Ringkasan Perkara Terbaru</h2>
            <a href="{{ route('admin.cases') }}" class="dashboard-card-link">Lihat Semua Perkara <i data-lucide="chevron-right" style="width: 16px;"></i></a>
        </div>
        
        <div class="table-container">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr>
                        <th style="border-bottom: 2px solid var(--color-gray-border); padding: 0.75rem 0.5rem; font-weight: 600; color: var(--color-gray-text); font-size: 0.8rem; text-transform: uppercase;">No Perkara</th>
                        <th style="border-bottom: 2px solid var(--color-gray-border); padding: 0.75rem 0.5rem; font-weight: 600; color: var(--color-gray-text); font-size: 0.8rem; text-transform: uppercase;">Klien</th>
                        <th style="border-bottom: 2px solid var(--color-gray-border); padding: 0.75rem 0.5rem; font-weight: 600; color: var(--color-gray-text); font-size: 0.8rem; text-transform: uppercase;">Advokat</th>
                        <th style="border-bottom: 2px solid var(--color-gray-border); padding: 0.75rem 0.5rem; font-weight: 600; color: var(--color-gray-text); font-size: 0.8rem; text-transform: uppercase;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); font-weight: 600;">PRK-2026-001</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); color: var(--color-gray-dark);">Budi Santoso</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); color: var(--color-gray-dark);">Andi Wijaya, S.H.</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border);"><span class="badge badge-info">Berjalan</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); font-weight: 600;">PRK-2026-002</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); color: var(--color-gray-dark);">PT Maju Bersama</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); color: var(--color-gray-dark);">Siti Aminah, S.H.</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border);"><span class="badge badge-info">Berjalan</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); font-weight: 600;">PRK-2026-003</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); color: var(--color-gray-dark);">Iwan Setiawan</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border); color: var(--color-gray-dark);">Andi Wijaya, S.H.</td>
                        <td style="padding: 1rem 0.5rem; border-bottom: 1px solid var(--color-gray-border);"><span class="badge badge-success">Selesai</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Pemberitahuan Terbaru -->
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <h2 class="dashboard-card-title">Pemberitahuan Terbaru</h2>
            <span class="header-badge-red" style="background-color: #eff6ff; color: var(--color-stat-blue-icon);">2 info</span>
        </div>
        
        <div class="consultation-list">
            <!-- Notif 1 -->
            <div class="consultation-row">
                <div class="consultation-avatar" style="background-color: #eff6ff; color: var(--color-stat-blue-icon);">
                    <i data-lucide="bell" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="consultation-details">
                    <span class="consultation-name">Pengajuan Konsultasi Baru</span>
                    <span class="consultation-topic" style="white-space: normal;">Klien "Sari Lestari" mengajukan konsultasi baru terkait hukum keluarga.</span>
                    <span class="consultation-date">10 menit yang lalu</span>
                </div>
            </div>
            
            <!-- Notif 2 -->
            <div class="consultation-row">
                <div class="consultation-avatar" style="background-color: #fffbeb; color: var(--color-stat-orange-icon);">
                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="consultation-details">
                    <span class="consultation-name">Dokumen Diunggah</span>
                    <span class="consultation-topic" style="white-space: normal;">Dokumen "Bukti Transfer" untuk perkara PRK-2026-001 menunggu verifikasi.</span>
                    <span class="consultation-date">1 jam yang lalu</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
