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
{{-- Permintaan Dokumen dari Advokat (Document Requests) --}}
@if($case->documentRequests && $case->documentRequests->count() > 0)
<div class="card" style="margin-top:16px;border-left:4px solid #f59e0b;">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;background:#fef3c7;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#b45309;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
            </div>
            <div>
                <div class="card-title" style="margin-bottom:2px;">Permintaan Dokumen dari Advokat</div>
                <div style="font-size:.78rem;color:#64748b;">Advokat Anda memerlukan dokumen berikut untuk melanjutkan penanganan perkara.</div>
            </div>
        </div>
    </div>
    <div class="table-wrapper" style="padding:0 16px 16px;">
        <table>
            <thead>
                <tr>
                    <th>Dokumen Diminta</th>
                    <th>Instruksi Advokat</th>
                    <th>Batas Waktu</th>
                    <th>Prioritas</th>
                    <th>Status Permintaan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($case->documentRequests as $req)
                @php
                    $reqBadgeMap = [
                        'Menunggu Upload' => 'badge-yellow',
                        'Sudah Diupload'  => 'badge-blue',
                        'Selesai'         => 'badge-green',
                        'Dibatalkan'      => 'badge-gray',
                    ];
                    $reqBadge = $reqBadgeMap[$req->status ?? ''] ?? 'badge-yellow';
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:600;color:#1e293b;">{{ $req->title }}</div>
                        <div style="font-size:.75rem;color:#64748b;">Jenis: {{ $req->document_type ?? 'Dokumen Pendukung' }}</div>
                    </td>
                    <td>{{ $req->description ?: '—' }}</td>
                    <td>{{ $req->due_date ? \Carbon\Carbon::parse($req->due_date)->locale('id')->isoFormat('D MMM YYYY') : '—' }}</td>
                    <td>
                        @if($req->priority === 'Tinggi')
                        <span style="color:#dc2626;font-weight:700;font-size:.75rem;background:#fee2e2;padding:3px 8px;border-radius:4px;">⚠ Tinggi</span>
                        @else
                        <span style="color:#475569;font-size:.75rem;background:#f1f5f9;padding:3px 8px;border-radius:4px;">Normal</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $reqBadge }}">{{ $req->status }}</span></td>
                    <td>
                        @if($req->status === 'Menunggu Upload')
                        <button type="button" class="btn btn-primary" style="padding:5px 12px;font-size:.75rem;display:inline-flex;align-items:center;gap:4px;"
                                onclick="openUploadModalForRequest({{ $req->id }}, '{{ addslashes($req->title) }}', '{{ addslashes($req->document_type ?? 'Dokumen Pendukung') }}')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Unggah Sekarang
                        </button>
                        @elseif($req->status === 'Sudah Diupload')
                        <span style="font-size:.75rem;color:#2563eb;font-weight:500;">✓ Berkas Terkirim</span>
                        @elseif($req->status === 'Selesai')
                        <span style="font-size:.75rem;color:#16a34a;font-weight:500;">✓ Terverifikasi</span>
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

{{-- Bagian Utama: Dokumen Perkara --}}
<div class="card" style="margin-top:16px;">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;background:#1e3a5f;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <div>
                <div class="card-title" style="margin-bottom:2px;">Dokumen Perkara</div>
                <div style="font-size:.78rem;color:#64748b;">Kelola berkas bukti, identitas, dan dokumen hukum perkara Anda.</div>
            </div>
        </div>
        <button type="button" class="btn btn-primary" onclick="openUploadModal()"
                style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:.825rem;background:#1e3a5f;border-radius:8px;color:#fff;font-weight:600;cursor:pointer;border:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Unggah Dokumen
        </button>
    </div>

    @if(!$case->documents || $case->documents->count() === 0)
    <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin:0 auto 12px;display:block;opacity:.6;">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
        </svg>
        <p style="font-size:.875rem;margin-bottom:12px;color:#64748b;">Belum ada dokumen yang diunggah untuk perkara ini.</p>
        <button type="button" class="btn btn-outline" onclick="openUploadModal()" style="font-size:.825rem;">Unggah Dokumen Pertama</button>
    </div>
    @else
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama Dokumen</th>
                    <th>Jenis</th>
                    <th>Ukuran</th>
                    <th>Pengunggah</th>
                    <th>Tanggal Upload</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($case->documents as $doc)
                @php
                    $isRejected = in_array($doc->status, ['Ditolak', 'Perlu Diperbaiki']);
                    $isVerified = ($doc->status === 'Terverifikasi' || $doc->status === 'Sudah Diterima');
                    $isPending  = in_array($doc->status, ['Menunggu Verifikasi', 'Menunggu Pemeriksaan', 'Belum Diunggah']);

                    if ($isVerified) {
                        $docBadgeClass = 'badge-green';
                        $statusText    = 'Terverifikasi';
                    } elseif ($isRejected) {
                        $docBadgeClass = 'badge-red';
                        $statusText    = 'Ditolak';
                    } else {
                        $docBadgeClass = 'badge-yellow';
                        $statusText    = 'Menunggu Verifikasi';
                    }
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:flex-start;gap:8px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" style="width:16px;height:16px;margin-top:2px;flex-shrink:0;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <div>
                                <div style="font-weight:600;color:#1e293b;line-height:1.3;">{{ $doc->name }}</div>
                                @if($doc->description)
                                <div style="font-size:.75rem;color:#64748b;margin-top:2px;">{{ $doc->description }}</div>
                                @endif
                                @if($doc->version > 1)
                                <span style="font-size:.7rem;background:#e0e7ff;color:#3730a3;padding:1px 6px;border-radius:4px;font-weight:600;">Revisi Ke-{{ $doc->version }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Alert Box jika Ditolak --}}
                        @if($isRejected && $doc->rejection_reason)
                        <div style="margin-top:8px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:8px 12px;font-size:.78rem;color:#991b1b;display:flex;align-items:flex-start;gap:6px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0;margin-top:2px;">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <div>
                                <strong>Alasan Penolakan dari Advokat:</strong><br>
                                {{ $doc->rejection_reason }}
                            </div>
                        </div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:.78rem;background:#f1f5f9;color:#475569;padding:3px 8px;border-radius:4px;font-weight:500;">
                            {{ $doc->document_type ?? 'Dokumen Lainnya' }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;color:#64748b;">
                        {{ $doc->formatted_file_size }}
                    </td>
                    <td>
                        @if($doc->is_from_lawyer)
                        <span style="font-size:.72rem;background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:4px;font-weight:600;display:inline-flex;align-items:center;gap:3px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Dokumen dari Advokat
                        </span>
                        @else
                        <span style="font-size:.78rem;color:#334155;">{{ $doc->uploader?->name ?? 'Klien' }}</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem;color:#64748b;">
                        {{ $doc->created_at ? $doc->created_at->locale('id')->isoFormat('D MMM YYYY') : '—' }}
                    </td>
                    <td>
                        <span class="badge {{ $docBadgeClass }}">{{ $statusText }}</span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            @if($doc->file_path)
                            <a href="{{ route('documents.view', $doc->id) }}" target="_blank"
                               class="btn btn-outline" style="padding:4px 9px;font-size:.75rem;text-decoration:none;display:inline-flex;align-items:center;gap:4px;" title="Lihat Pratinjau">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                Lihat
                            </a>
                            <a href="{{ route('documents.download', $doc->id) }}"
                               class="btn btn-outline" style="padding:4px 9px;font-size:.75rem;text-decoration:none;display:inline-flex;align-items:center;gap:4px;" title="Unduh File">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Unduh
                            </a>
                            @endif

                            @if($isRejected)
                            <button type="button" class="btn btn-primary" style="padding:4px 9px;font-size:.75rem;background:#dc2626;border:none;display:inline-flex;align-items:center;gap:4px;"
                                    onclick="openReuploadModal({{ $doc->id }}, '{{ addslashes($doc->name) }}', '{{ addslashes($doc->rejection_reason ?? '') }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                Unggah Ulang
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Modal: Unggah Dokumen Perkara --}}
<div id="modalUploadDoc" class="hidden" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px 28px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 25px rgba(0,0,0,0.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <div style="font-size:1.05rem;font-weight:700;color:#1e293b;">Unggah Dokumen Perkara</div>
            <button type="button" onclick="closeUploadModal()" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;line-height:1;">✕</button>
        </div>

        <form method="POST" action="{{ route('klien.cases.document.upload', $case->id) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="document_request_id" id="formRequestId" value="">

            <div id="requestBannerInfo" style="display:none;background:#fef3c7;border:1px solid #fde68a;padding:10px 14px;border-radius:8px;font-size:.78rem;color:#92400e;margin-bottom:14px;">
                Memenuhi permintaan dokumen: <strong id="requestBannerTitle"></strong>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Nama / Judul Dokumen <span style="color:#dc2626;">*</span></label>
                <input type="text" name="name" id="formDocName" required placeholder="Contoh: Bukti Transfer Pembayaran Tahap 1"
                       style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.85rem;outline:none;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Jenis Dokumen <span style="color:#dc2626;">*</span></label>
                <select name="document_type" id="formDocType" required
                        style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.85rem;outline:none;background:#fff;">
                    <option value="KTP">KTP (Kartu Tanda Penduduk)</option>
                    <option value="KK">KK (Kartu Keluarga)</option>
                    <option value="Surat Kuasa">Surat Kuasa Khusus</option>
                    <option value="Bukti Transaksi">Bukti Transaksi / Resi Pembayaran</option>
                    <option value="Kontrak/Perjanjian">Kontrak / Perjanjian Kerja Sama</option>
                    <option value="Surat/Dokumen Resmi">Surat / Dokumen Resmi Instansi</option>
                    <option value="Bukti Pendukung">Bukti Pendukung (Foto/Pesan/Rekening)</option>
                    <option value="Dokumen Lainnya" selected>Dokumen Lainnya</option>
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Pilih File Dokumen <span style="color:#dc2626;">*</span></label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       style="width:100%;padding:8px 10px;border:1px dashed #94a3b8;border-radius:8px;font-size:.825rem;background:#f8fafc;">
                <div style="font-size:.72rem;color:#64748b;margin-top:4px;">Format diizinkan: PDF, JPG, JPEG, PNG, DOC, DOCX. Maksimal 10 MB.</div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Keterangan / Catatan Tambahan</label>
                <textarea name="description" rows="2" placeholder="Catatan singkat mengenai berkas ini (opsional)..."
                          style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.85rem;outline:none;resize:vertical;"></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closeUploadModal()" class="btn btn-outline" style="padding:8px 16px;font-size:.825rem;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 20px;font-size:.825rem;background:#1e3a5f;color:#fff;border-radius:8px;font-weight:600;">Unggah Dokumen</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Unggah Ulang Dokumen (Re-upload) --}}
<div id="modalReuploadDoc" class="hidden" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px 28px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 25px rgba(0,0,0,0.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <div style="font-size:1.05rem;font-weight:700;color:#991b1b;">Unggah Ulang Dokumen Revisi</div>
            <button type="button" onclick="closeReuploadModal()" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;line-height:1;">✕</button>
        </div>

        <form id="formReupload" method="POST" action="" enctype="multipart/form-data">
            @csrf

            <div style="background:#fef2f2;border:1px solid #fecaca;padding:12px;border-radius:8px;font-size:.78rem;color:#991b1b;margin-bottom:14px;">
                <div>Dokumen: <strong id="reuploadDocName"></strong></div>
                <div style="margin-top:4px;"><strong>Catatan Penolakan Advokat:</strong> <span id="reuploadReason"></span></div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Pilih File Dokumen Pengganti <span style="color:#dc2626;">*</span></label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       style="width:100%;padding:8px 10px;border:1px dashed #94a3b8;border-radius:8px;font-size:.825rem;background:#f8fafc;">
                <div style="font-size:.72rem;color:#64748b;margin-top:4px;">Pastikan kualitas berkas terbaca jelas. Format: PDF, JPG, PNG, DOC, DOCX (Maks 10 MB).</div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Catatan Perbaikan / Klarifikasi</label>
                <textarea name="description" rows="2" placeholder="Jelaskan perbaikan yang telah dilakukan pada berkas ini..."
                          style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.85rem;outline:none;resize:vertical;"></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closeReuploadModal()" class="btn btn-outline" style="padding:8px 16px;font-size:.825rem;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 20px;font-size:.825rem;background:#dc2626;color:#fff;border-radius:8px;font-weight:600;border:none;">Unggah Ulang Revisi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() {
    document.getElementById('formRequestId').value = '';
    document.getElementById('requestBannerInfo').style.display = 'none';
    document.getElementById('formDocName').value = '';
    document.getElementById('modalUploadDoc').style.display = 'flex';
}

function openUploadModalForRequest(reqId, reqTitle, reqType) {
    document.getElementById('formRequestId').value = reqId;
    document.getElementById('requestBannerTitle').innerText = reqTitle;
    document.getElementById('requestBannerInfo').style.display = 'block';
    document.getElementById('formDocName').value = reqTitle;
    if (reqType) {
        const select = document.getElementById('formDocType');
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === reqType) {
                select.selectedIndex = i;
                break;
            }
        }
    }
    document.getElementById('modalUploadDoc').style.display = 'flex';
}

function closeUploadModal() {
    document.getElementById('modalUploadDoc').style.display = 'none';
}

function openReuploadModal(docId, docName, reason) {
    document.getElementById('reuploadDocName').innerText = docName;
    document.getElementById('reuploadReason').innerText = reason || 'Tidak ada catatan khusus.';
    document.getElementById('formReupload').action = '{{ url("/klien/dokumen") }}/' + docId + '/reupload';
    document.getElementById('modalReuploadDoc').style.display = 'flex';
}

function closeReuploadModal() {
    document.getElementById('modalReuploadDoc').style.display = 'none';
}
</script>

@else
<div class="card" style="text-align:center;padding:3rem 1.5rem;color:#94a3b8;margin-top:16px;">
    <p style="font-size:.875rem;">Perkara tidak ditemukan atau belum dipilih.</p>
</div>
@endif {{-- end $case --}}
@endif {{-- end $cases->isEmpty() --}}

@endsection
