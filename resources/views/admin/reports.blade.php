@extends('layouts.admin')

@section('title', 'Laporan & Rekapitulasi')

@section('content')
<div class="card mb-4">
    <div class="action-toolbar" style="margin-bottom: 0;">
        <h2 class="card-title" style="margin-bottom: 0;">Filter Laporan</h2>
        
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label style="font-size: 0.875rem; color: var(--color-gray-text);">Periode:</label>
                <input type="date" class="search-box" style="width: auto;" value="2026-08-01">
                <span>-</span>
                <input type="date" class="search-box" style="width: auto;" value="2026-08-31">
            </div>
            <button class="btn btn-primary">
                <i data-lucide="filter"></i> Terapkan
            </button>
            <button class="btn btn-outline">
                <i data-lucide="download"></i> Ekspor PDF
            </button>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="message-square"></i></div>
        <div class="stat-info">
            <div class="stat-number">{{ $data['total_consultations'] }}</div>
            <div class="stat-label">Total Konsultasi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-number">{{ $data['selesai_consultations'] }}</div>
            <div class="stat-label">Konsultasi Selesai</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="briefcase"></i></div>
        <div class="stat-info">
            <div class="stat-number">{{ $data['total_cases'] }}</div>
            <div class="stat-label">Total Perkara</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="users"></i></div>
        <div class="stat-info">
            <div class="stat-number">{{ $data['total_clients'] }}</div>
            <div class="stat-label">Total Klien</div>
        </div>
    </div>
</div>
            <h3>Total Konsultasi</h3>
            <p>42</p>
            <span style="font-size: 0.75rem; color: var(--color-green);">+12% bulan ini</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="folder-open"></i></div>
        <div class="stat-info">
            <h3>Total Perkara Baru</h3>
            <p>15</p>
            <span style="font-size: 0.75rem; color: var(--color-green);">+5% bulan ini</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="users"></i></div>
        <div class="stat-info">
            <h3>Klien Baru</h3>
            <p>28</p>
            <span style="font-size: 0.75rem; color: var(--color-gray-text);">Sama seperti sebelumnya</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="bot"></i></div>
        <div class="stat-info">
            <h3>Penggunaan Asisten</h3>
            <p>156</p>
            <span style="font-size: 0.75rem; color: var(--color-green);">+25% bulan ini</span>
        </div>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Rekapitulasi Dokumen Diproses</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Dokumen Diunggah</th>
                    <th>Dokumen Disetujui</th>
                    <th>Perlu Perbaikan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Agustus 2026</td>
                    <td>45</td>
                    <td>38</td>
                    <td>7</td>
                </tr>
                <tr>
                    <td>Juli 2026</td>
                    <td>52</td>
                    <td>50</td>
                    <td>2</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
