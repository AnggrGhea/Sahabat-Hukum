@extends('layouts.klien')

@section('title', 'Perkara Saya')
@section('header-title', 'Perkara Saya')

@section('content')

{{-- Flash Message --}}
@if(session('success'))
<div style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.875rem;font-weight:500;">
    ✓ {{ session('success') }}
</div>
@endif

@if(isset($errors) && $errors->any())
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#b91c1c;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.875rem;font-weight:500;">
    <ul style="margin:0;padding-left:18px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Breadcrumb --}}
<div class="breadcrumb" style="margin-bottom:16px;">
    <a href="{{ route('klien.dashboard') }}">Beranda</a>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    <span class="current">Perkara Saya</span>
</div>

{{-- Page Title --}}
<div class="page-title-row" style="margin-bottom:16px;">
    <div class="page-heading">
        <h1>Perkara Saya</h1>
        <p>Pantau perkembangan perkara yang sedang ditangani oleh kantor hukum kami.</p>
    </div>
</div>

{{-- Info Banner --}}
<div class="info-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <div>
        Halaman ini menampilkan perkara yang sedang ditangani oleh tim advokat kami.
        Untuk informasi lebih lanjut, silakan hubungi advokat Anda melalui fitur <a href="{{ route('klien.consultations') }}">Konsultasi</a>.
    </div>
</div>

@if($cases->isEmpty())
<div style="text-align:center;padding:4rem;color:#94a3b8;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:64px;height:64px;margin:0 auto 16px;display:block;">
        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
    </svg>
    <p style="font-size:.875rem;">Belum ada perkara yang ditangani.</p>
    <a href="{{ route('klien.consultations') }}" class="btn btn-primary" style="margin-top:12px;display:inline-flex;">Ajukan Konsultasi</a>
</div>
@else

{{-- Case tabs if more than one --}}
@if($cases->count() > 1)
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
    @foreach($cases as $c)
    @php $isActive = $case && $case->id === $c->id; @endphp
    <a href="{{ route('klien.cases.show', $c->id) }}"
       style="padding:6px 16px;border-radius:20px;font-size:.8rem;font-weight:600;text-decoration:none;border:1px solid {{ $isActive ? '#1e3a5f' : '#e2e8f0' }};background:{{ $isActive ? '#1e3a5f' : '#fff' }};color:{{ $isActive ? '#fff' : '#374151' }};">
        {{ $c->case_number }}
    </a>
    @endforeach
</div>
@endif

@if($case)
@php
    $statusMap = [
        'Persidangan' => 'badge-blue',
        'Penyidikan'  => 'badge-blue',
        'Selesai'     => 'badge-gray',
        'Dibatalkan'  => 'badge-red',
    ];
    $statusBadge = $statusMap[$case->status ?? ''] ?? 'badge-green';
@endphp

{{-- Perkara Header Card --}}
<div class="perkara-card">
    <div class="perkara-card-header">
        <div>
            <div class="perkara-num-label">Nomor Perkara</div>
            <div class="perkara-num">{{ $case->case_number ?? '-' }}</div>
        </div>
        <span class="badge {{ $statusBadge }}">{{ $case->status ?? 'Aktif' }}</span>
    </div>

    <div class="perkara-meta-grid">
        <div class="perkara-meta-item">
            <div class="meta-label">Jenis Perkara</div>
            <div class="meta-value">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
                {{ $case->case_type ?? '-' }}
            </div>
        </div>
        <div class="perkara-meta-item">
            <div class="meta-label">Advokat Pendamping</div>
            <div class="meta-value">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                {{ $case->lawyer?->name ?? 'Belum ditentukan' }}
            </div>
        </div>
        <div class="perkara-meta-item">
            <div class="meta-label">Tanggal Mulai</div>
            <div class="meta-value">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                {{ $case->started_at ? \Carbon\Carbon::parse($case->started_at)->locale('id')->isoFormat('D MMMM YYYY') : '—' }}
            </div>
        </div>
        <div class="perkara-meta-item">
            <div class="meta-label">Status Saat Ini</div>
            <div class="meta-value meta-value-orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                {{ $case->status ?? '—' }}
            </div>
        </div>
    </div>
</div>

{{-- Progress Timeline --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Perkembangan Terakhir</div>
    </div>
    <div class="card-body" style="padding:0 20px;">
        <div class="timeline">
            @forelse($case->progress as $prog)
            <div class="timeline-item">
                <div class="tl-dot-wrap">
                    <div class="tl-dot tl-dot-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    @if(!$loop->last)<div class="tl-line"></div>@endif
                </div>
                <div style="flex:1;padding-top:4px;">
                    <div class="tl-date">{{ $prog->progress_date ? \Carbon\Carbon::parse($prog->progress_date)->locale('id')->isoFormat('D MMM YYYY') : '—' }}</div>
                    <div class="tl-title">{{ $prog->title }}</div>
                    @if($prog->description)
                    <div class="tl-desc">{{ $prog->description }}</div>
                    @endif
                </div>
            </div>
            @empty
            <div style="color:#94a3b8;font-size:.875rem;padding:1rem 0;">Belum ada perkembangan.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Documents --}}
@if($case->documents && $case->documents->count() > 0)
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <div class="card-title">Dokumen Perkara</div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama Dokumen</th>
                    <th>Keterangan</th>
                    <th>Batas Waktu</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($case->documents as $doc)
                @php
                    $docBadgeMap = [
                        'Sudah Diterima'       => 'badge-green',
                        'Menunggu Pemeriksaan' => 'badge-yellow',
                        'Perlu Diperbaiki'     => 'badge-red',
                    ];
                    $docBadge = $docBadgeMap[$doc->status ?? ''] ?? 'badge-gray';
                @endphp
                <tr>
                    <td class="td-bold">{{ $doc->name }}</td>
                    <td>{{ $doc->description ?? '—' }}</td>
                    <td>{{ $doc->due_date ? \Carbon\Carbon::parse($doc->due_date)->locale('id')->isoFormat('D MMM YYYY') : '—' }}</td>
                    <td>
                        @if($doc->priority === 'Tinggi')
                        <span style="color:#dc2626;font-weight:600;font-size:.8rem;">⚠ Tinggi</span>
                        @else
                        <span style="color:#64748b;font-size:.8rem;">Normal</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $docBadge }}">{{ $doc->status }}</span></td>
                    <td>
                        @if(in_array($doc->status, ['Belum Diunggah', 'Perlu Diperbaiki']))
                        <form method="POST" action="{{ route('klien.documents.upload', $doc->id) }}" enctype="multipart/form-data" style="display:flex;align-items:center;gap:6px;">
                            @csrf
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required
                                   style="font-size:.75rem;max-width:160px;"
                                   onchange="this.form.submit()">
                        </form>
                        @elseif($doc->file_path)
                        <span style="font-size:.75rem;color:#16a34a;">✓ Terunggah</span>
                        @else
                        <span style="font-size:.75rem;color:#94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@else
<div class="card" style="text-align:center;padding:3rem 1.5rem;color:#94a3b8;margin-top:16px;">
    <p style="font-size:.875rem;">Perkara tidak ditemukan atau belum dipilih.</p>
</div>
@endif {{-- end $case --}}
@endif {{-- end $cases->isEmpty() --}}

@endsection
