@extends('layouts.klien')

@section('title', 'Dokumen Saya')

@section('content')
<div style="display:flex;flex-direction:column;gap:20px;">

    {{-- Page Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0 0 4px 0;">Manajemen Dokumen Perkara</h1>
            <p style="font-size:.85rem;color:#64748b;margin:0;">Kelola, unggah, dan pantau seluruh berkas hukum perkara Anda bersama Advokat pendamping.</p>
        </div>
        <button type="button" onclick="openUploadModal()"
                style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;font-size:.85rem;background:#1e3a5f;color:#fff;font-weight:600;border:none;border-radius:8px;cursor:pointer;box-shadow:0 2px 4px rgba(30,58,95,.2);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Unggah Dokumen Baru
        </button>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:8px;font-size:.85rem;display:flex;align-items:center;gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:.85rem;display:flex;align-items:center;gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:.85rem;">
        <strong>Perhatian:</strong>
        <ul style="margin:4px 0 0 16px;padding:0;">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));gap:12px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Total Dokumen</div>
                <div style="font-size:1.25rem;font-weight:700;color:#0f172a;">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;color:#10b981;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Terverifikasi</div>
                <div style="font-size:1.25rem;font-weight:700;color:#10b981;">{{ $stats['verified'] }}</div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;color:#f59e0b;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Menunggu</div>
                <div style="font-size:1.25rem;font-weight:700;color:#f59e0b;">{{ $stats['pending'] }}</div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#fef2f2;display:flex;align-items:center;justify-content:center;color:#ef4444;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Ditolak (Perlu Revisi)</div>
                <div style="font-size:1.25rem;font-weight:700;color:#ef4444;">{{ $stats['rejected'] }}</div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;color:#8b5cf6;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
            </div>
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Permintaan Dokumen</div>
                <div style="font-size:1.25rem;font-weight:700;color:#8b5cf6;">{{ $stats['requests'] }}</div>
            </div>
        </div>
    </div>

    {{-- Banner: Permintaan Dokumen dari Advokat (jika ada) --}}
    @if($pendingRequests->count() > 0)
    <div style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:16px;box-shadow:0 1px 3px rgba(234,88,12,.08);">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
            <div style="width:28px;height:28px;background:#ea580c;color:#fff;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <strong style="color:#9a3412;font-size:.9rem;">Advokat Memerlukan Dokumen Tambahan ({{ $pendingRequests->count() }} Permintaan)</strong>
                <div style="color:#c2410c;font-size:.78rem;">Silakan unggah dokumen di bawah ini agar perkara dapat segera diproses lebih lanjut.</div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach($pendingRequests as $req)
            <div style="background:#fff;border:1px solid #ffedd5;border-radius:8px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div style="flex:1;min-width:240px;">
                    <div style="font-weight:600;font-size:.85rem;color:#1e293b;">
                        {{ $req->title }}
                        <span style="font-size:.7rem;background:#fee2e2;color:#991b1b;padding:1px 6px;border-radius:4px;font-weight:600;margin-left:4px;">Prioritas: {{ $req->priority }}</span>
                    </div>
                    <div style="font-size:.75rem;color:#64748b;margin-top:2px;">
                        Perkara: <strong>{{ $req->case?->case_number ?? 'Perkara' }}</strong> • Diminta oleh: <strong>{{ $req->requester?->name ?? 'Advokat' }}</strong>
                        @if($req->notes)
                        — <em>"{{ $req->notes }}"</em>
                        @endif
                    </div>
                </div>
                <button type="button" onclick="openUploadModalForRequest({{ $req->case_id }}, {{ $req->id }}, '{{ addslashes($req->title) }}', '{{ addslashes($req->document_type ?? 'Dokumen Pendukung') }}')"
                        style="padding:6px 14px;font-size:.8rem;font-weight:600;background:#ea580c;color:#fff;border:none;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Unggah Sekarang
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filter & Search Toolbar --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            {{-- Filter Status Pills --}}
            <a href="{{ route('klien.documents', ['status' => 'all', 'case_id' => $selectedCaseId]) }}"
               style="padding:5px 12px;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;{{ $statusFilter === 'all' || !$statusFilter ? 'background:#1e3a5f;color:#fff;' : 'background:#f1f5f9;color:#475569;' }}">
                Semua ({{ $stats['total'] }})
            </a>
            <a href="{{ route('klien.documents', ['status' => 'Terverifikasi', 'case_id' => $selectedCaseId]) }}"
               style="padding:5px 12px;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;{{ $statusFilter === 'Terverifikasi' ? 'background:#10b981;color:#fff;' : 'background:#f1f5f9;color:#475569;' }}">
                Terverifikasi ({{ $stats['verified'] }})
            </a>
            <a href="{{ route('klien.documents', ['status' => 'Menunggu', 'case_id' => $selectedCaseId]) }}"
               style="padding:5px 12px;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;{{ $statusFilter === 'Menunggu' ? 'background:#f59e0b;color:#fff;' : 'background:#f1f5f9;color:#475569;' }}">
                Menunggu ({{ $stats['pending'] }})
            </a>
            <a href="{{ route('klien.documents', ['status' => 'Ditolak', 'case_id' => $selectedCaseId]) }}"
               style="padding:5px 12px;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;{{ $statusFilter === 'Ditolak' ? 'background:#ef4444;color:#fff;' : 'background:#f1f5f9;color:#475569;' }}">
                Ditolak ({{ $stats['rejected'] }})
            </a>
        </div>

        <form method="GET" action="{{ route('klien.documents') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <input type="hidden" name="status" value="{{ $statusFilter }}">

            {{-- Filter Perkara --}}
            @if($cases->count() > 1)
            <select name="case_id" onchange="this.form.submit()"
                    style="padding:6px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:.8rem;color:#334155;background:#fff;outline:none;">
                <option value="">Semua Perkara</option>
                @foreach($cases as $c)
                <option value="{{ $c->id }}" {{ $selectedCaseId == $c->id ? 'selected' : '' }}>
                    {{ $c->case_number }}
                </option>
                @endforeach
            </select>
            @endif

            {{-- Search Box --}}
            <div style="display:flex;align-items:center;background:#f8fafc;border:1px solid #cbd5e1;border-radius:6px;padding:4px 10px;width:200px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="width:14px;height:14px;margin-right:6px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari berkas..." style="border:none;background:transparent;outline:none;font-size:.8rem;width:100%;">
            </div>
            @if($search || $selectedCaseId)
            <a href="{{ route('klien.documents') }}" style="font-size:.75rem;color:#64748b;text-decoration:none;padding:5px;">Reset</a>
            @endif
        </form>
    </div>

    {{-- Tabel Dokumen Utama --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        @if($documents->count() === 0)
        <div style="text-align:center;padding:3.5rem 1rem;color:#94a3b8;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:52px;height:52px;margin:0 auto 12px;display:block;opacity:.5;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
            </svg>
            <p style="font-size:.9rem;color:#475569;margin-bottom:8px;font-weight:600;">Tidak ada dokumen ditemukan</p>
            <p style="font-size:.8rem;color:#94a3b8;margin-bottom:16px;">Belum ada dokumen yang sesuai dengan filter atau pencarian Anda.</p>
            <button type="button" onclick="openUploadModal()"
                    style="padding:8px 18px;font-size:.825rem;background:#1e3a5f;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                Unggah Dokumen Sekarang
            </button>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.825rem;text-align:left;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:600;text-transform:uppercase;font-size:.72rem;letter-spacing:.5px;">
                        <th style="padding:12px 16px;">Nama Dokumen</th>
                        <th style="padding:12px 16px;">Jenis</th>
                        <th style="padding:12px 16px;">Perkara</th>
                        <th style="padding:12px 16px;">Ukuran</th>
                        <th style="padding:12px 16px;">Pengunggah</th>
                        <th style="padding:12px 16px;">Tanggal</th>
                        <th style="padding:12px 16px;">Status</th>
                        <th style="padding:12px 16px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody style="divide-y:1px solid #f1f5f9;">
                    @foreach($documents as $doc)
                    @php
                        $isRejected = in_array($doc->status, ['Ditolak', 'Perlu Diperbaiki']);
                        $isVerified = ($doc->status === 'Terverifikasi' || $doc->status === 'Sudah Diterima');
                        $isPending  = in_array($doc->status, ['Menunggu Verifikasi', 'Menunggu Pemeriksaan', 'Belum Diunggah']);

                        if ($isVerified) {
                            $badgeBg = '#ecfdf5'; $badgeColor = '#065f46'; $statusLabel = 'Terverifikasi';
                        } elseif ($isRejected) {
                            $badgeBg = '#fef2f2'; $badgeColor = '#991b1b'; $statusLabel = 'Ditolak';
                        } else {
                            $badgeBg = '#fffbeb'; $badgeColor = '#92400e'; $statusLabel = 'Menunggu Verifikasi';
                        }
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:12px 16px;vertical-align:top;max-width:280px;">
                            <div style="font-weight:600;color:#1e293b;line-height:1.3;">{{ $doc->name }}</div>
                            @if($doc->description)
                            <div style="font-size:.75rem;color:#64748b;margin-top:2px;">{{ $doc->description }}</div>
                            @endif
                            @if($doc->version > 1)
                            <span style="display:inline-block;margin-top:3px;font-size:.68rem;background:#e0e7ff;color:#3730a3;padding:1px 6px;border-radius:4px;font-weight:600;">Revisi v{{ $doc->version }}</span>
                            @endif

                            {{-- Alert Box jika Ditolak --}}
                            @if($isRejected && $doc->rejection_reason)
                            <div style="margin-top:8px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:6px 10px;font-size:.75rem;color:#991b1b;">
                                <strong>Alasan Penolakan:</strong> {{ $doc->rejection_reason }}
                            </div>
                            @endif
                        </td>
                        <td style="padding:12px 16px;vertical-align:top;">
                            <span style="font-size:.75rem;background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:4px;font-weight:500;">
                                {{ $doc->document_type ?? 'Dokumen' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;vertical-align:top;color:#334155;">
                            @if($doc->case)
                            <div style="font-weight:500;color:#1e3a5f;">{{ $doc->case->case_number }}</div>
                            <div style="font-size:.72rem;color:#64748b;">{{ $doc->case->case_type }}</div>
                            @else
                            <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;vertical-align:top;color:#64748b;font-size:.78rem;">
                            {{ $doc->formatted_file_size }}
                        </td>
                        <td style="padding:12px 16px;vertical-align:top;">
                            @if($doc->is_from_lawyer)
                            <span style="font-size:.7rem;background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;font-weight:600;">
                                Dokumen Advokat
                            </span>
                            @else
                            <span style="font-size:.75rem;color:#475569;">{{ $doc->uploader?->name ?? 'Klien' }}</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;vertical-align:top;font-size:.78rem;color:#64748b;">
                            {{ $doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->locale('id')->isoFormat('D MMM YYYY') : '—' }}
                        </td>
                        <td style="padding:12px 16px;vertical-align:top;">
                            <span style="display:inline-block;padding:3px 8px;border-radius:12px;font-size:.72rem;font-weight:600;background:{{ $badgeBg }};color:{{ $badgeColor }};">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;vertical-align:top;text-align:right;">
                            <div style="display:inline-flex;align-items:center;gap:4px;justify-content:flex-end;flex-wrap:wrap;">
                                @if($doc->file_path)
                                <a href="{{ route('documents.view', $doc->id) }}" target="_blank"
                                   style="display:inline-flex;align-items:center;gap:4px;padding:5px 9px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#334155;text-decoration:none;font-size:.75rem;font-weight:500;"
                                   title="Lihat Pratinjau">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Lihat
                                </a>
                                <a href="{{ route('documents.download', $doc->id) }}"
                                   style="display:inline-flex;align-items:center;gap:4px;padding:5px 9px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#334155;text-decoration:none;font-size:.75rem;font-weight:500;"
                                   title="Unduh Berkas">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Unduh
                                </a>
                                @endif

                                @if($isRejected)
                                <button type="button"
                                        onclick="openReuploadModalById({{ $doc->id }})"
                                        style="display:inline-flex;align-items:center;gap:4px;padding:5px 9px;border:none;border-radius:6px;background:#dc2626;color:#fff;font-size:.75rem;font-weight:600;cursor:pointer;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    Unggah Ulang
                                </button>
                                @endif

                                <script id="klien-doc-json-{{ $doc->id }}" type="application/json">{!! json_encode([
                                    'id' => $doc->id,
                                    'name' => $doc->name,
                                    'type' => $doc->document_type ?? 'Dokumen',
                                    'case' => $doc->case ? $doc->case->case_number . ' - ' . $doc->case->title : '—',
                                    'lawyer' => $doc->case?->lawyer?->name ?? '—',
                                    'uploader' => $doc->is_from_lawyer ? 'Dokumen dari Advokat' : ($doc->uploader?->name ?? 'Klien'),
                                    'size' => $doc->formatted_file_size,
                                    'date' => $doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '—',
                                    'status' => $statusLabel,
                                    'desc' => $doc->description ?? 'Tidak ada keterangan.',
                                    'reason' => $doc->rejection_reason,
                                    'hasFile' => (bool)$doc->file_path,
                                    'viewUrl' => $doc->file_path ? route('documents.view', $doc->id) : '',
                                    'dlUrl' => $doc->file_path ? route('documents.download', $doc->id) : '',
                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>

                                <button type="button"
                                        onclick="openDetailModalById({{ $doc->id }})"
                                        style="display:inline-flex;align-items:center;gap:3px;padding:5px 8px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;color:#64748b;font-size:.75rem;cursor:pointer;"
                                        title="Informasi Lengkap">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- MODAL 1: Unggah Dokumen Klien --}}
<div id="modalUpload" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:500px;box-shadow:0 10px 25px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0;">Unggah Dokumen Perkara</h3>
            <button type="button" onclick="closeUploadModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>

        <form id="formUpload" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="document_request_id" id="uploadRequestId" value="">

            @if($cases->count() > 1)
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Pilih Perkara *</label>
                <select id="uploadCaseSelect" name="case_id_picker" onchange="updateFormAction(this.value)" required
                        style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;background:#fff;">
                    @foreach($cases as $c)
                    <option value="{{ $c->id }}" {{ $selectedCaseId == $c->id ? 'selected' : '' }}>
                        {{ $c->case_number }} — {{ $c->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" id="uploadCaseIdSingle" value="{{ $cases->first()?->id }}">
            @endif

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Nama / Judul Dokumen *</label>
                <input type="text" name="name" id="uploadDocName" required placeholder="Contoh: KTP Pemohon, Sertifikat Tanah, Bukti Transfer..."
                       style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Jenis Dokumen *</label>
                <select name="document_type" id="uploadDocType" required
                        style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;background:#fff;">
                    <option value="Identitas Diri (KTP/SIM/Paspor)">Identitas Diri (KTP/SIM/Paspor)</option>
                    <option value="Surat Kuasa">Surat Kuasa</option>
                    <option value="Alat Bukti Surat/Dokumen">Alat Bukti Surat / Dokumen</option>
                    <option value="Bukti Pembayaran/Transfer">Bukti Pembayaran / Transfer</option>
                    <option value="Perjanjian/Kontrak">Perjanjian / Kontrak</option>
                    <option value="Dokumen Lainnya">Dokumen Lainnya</option>
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Catatan Tambahan (Opsional)</label>
                <textarea name="description" rows="2" placeholder="Tuliskan keterangan mengenai dokumen ini jika ada..."
                          style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Pilih Berkas * (Maks. 10 MB)</label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       style="width:100%;padding:8px;border:1px dashed #cbd5e1;border-radius:6px;font-size:.8rem;background:#f8fafc;box-sizing:border-box;">
                <div style="font-size:.72rem;color:#64748b;margin-top:4px;">Format yang didukung: PDF, JPG, PNG, DOC, DOCX.</div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;">
                <button type="button" onclick="closeUploadModal()"
                        style="padding:8px 16px;background:#f1f5f9;color:#475569;border:none;border-radius:6px;font-size:.825rem;cursor:pointer;font-weight:600;">
                    Batal
                </button>
                <button type="submit"
                        style="padding:8px 20px;background:#1e3a5f;color:#fff;border:none;border-radius:6px;font-size:.825rem;cursor:pointer;font-weight:600;">
                    Unggah Berkas
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Unggah Ulang Dokumen (Re-upload) --}}
<div id="modalReupload" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 10px 25px rgba(0,0,0,.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.05rem;font-weight:700;color:#991b1b;margin:0;">Unggah Ulang Dokumen Revisi</h3>
            <button type="button" onclick="closeReuploadModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>

        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 12px;margin-bottom:14px;">
            <div style="font-size:.75rem;color:#991b1b;font-weight:600;">Dokumen Sebelumnya Ditolak:</div>
            <div id="reuploadDocTitle" style="font-size:.85rem;font-weight:700;color:#1e293b;margin-top:2px;"></div>
            <div id="reuploadReasonBox" style="font-size:.75rem;color:#b91c1c;margin-top:4px;"></div>
        </div>

        <form id="formReupload" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Catatan Perbaikan (Opsional)</label>
                <textarea name="description" rows="2" placeholder="Jelaskan perbaikan yang telah dilakukan pada berkas baru..."
                          style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Pilih Berkas Baru * (Maks. 10 MB)</label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       style="width:100%;padding:8px;border:1px dashed #cbd5e1;border-radius:6px;font-size:.8rem;background:#f8fafc;box-sizing:border-box;">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;">
                <button type="button" onclick="closeReuploadModal()"
                        style="padding:8px 16px;background:#f1f5f9;color:#475569;border:none;border-radius:6px;font-size:.825rem;cursor:pointer;font-weight:600;">
                    Batal
                </button>
                <button type="submit"
                        style="padding:8px 20px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:.825rem;cursor:pointer;font-weight:600;">
                    Kirim Berkas Revisi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: Detail Dokumen Info --}}
<div id="modalDetail" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 10px 25px rgba(0,0,0,.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0;">Detail Dokumen</h3>
            <button type="button" onclick="closeDetailModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;font-size:.825rem;">
            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Nama Dokumen</span>
                <strong id="detName" style="color:#1e293b;font-size:.95rem;"></strong>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Jenis Dokumen</span>
                    <span id="detType" style="color:#334155;font-weight:500;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Status</span>
                    <span id="detStatus" style="font-weight:600;"></span>
                </div>
            </div>
            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Perkara Terkait</span>
                <span id="detCase" style="color:#1e293b;font-weight:500;"></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Pengunggah</span>
                    <span id="detUploader" style="color:#334155;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Advokat Pendamping</span>
                    <span id="detLawyer" style="color:#334155;"></span>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Ukuran Berkas</span>
                    <span id="detSize" style="color:#334155;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Waktu Unggah</span>
                    <span id="detDate" style="color:#334155;"></span>
                </div>
            </div>
            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Catatan / Deskripsi</span>
                <p id="detDesc" style="margin:2px 0 0 0;color:#475569;background:#f8fafc;padding:8px 10px;border-radius:6px;border:1px solid #f1f5f9;"></p>
            </div>
            <div id="detReasonContainer" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:8px 10px;">
                <span style="color:#991b1b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:700;">Catatan Penolakan dari Advokat</span>
                <p id="detReason" style="margin:2px 0 0 0;color:#b91c1c;"></p>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;margin-top:16px;">
            <div id="detActionButtons" style="display:flex;gap:6px;"></div>
            <button type="button" onclick="closeDetailModal()"
                    style="padding:8px 16px;background:#f1f5f9;color:#475569;border:none;border-radius:6px;font-size:.825rem;cursor:pointer;font-weight:600;">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function getActiveCaseId() {
    const picker = document.getElementById('uploadCaseSelect');
    if (picker) return picker.value;
    const single = document.getElementById('uploadCaseIdSingle');
    if (single) return single.value;
    return '{{ $cases->first()?->id ?? 1 }}';
}

function updateFormAction(caseId) {
    document.getElementById('formUpload').action = `/klien/perkara/${caseId}/dokumen`;
}

function openUploadModal() {
    const caseId = getActiveCaseId();
    updateFormAction(caseId);
    document.getElementById('uploadRequestId').value = '';
    document.getElementById('uploadDocName').value = '';
    document.getElementById('modalUpload').style.display = 'flex';
}

function openUploadModalForRequest(caseId, reqId, title, type) {
    updateFormAction(caseId);
    const picker = document.getElementById('uploadCaseSelect');
    if (picker) picker.value = caseId;
    document.getElementById('uploadRequestId').value = reqId;
    document.getElementById('uploadDocName').value = title;
    const typeSelect = document.getElementById('uploadDocType');
    if (typeSelect) {
        for (let i = 0; i < typeSelect.options.length; i++) {
            if (typeSelect.options[i].value === type) {
                typeSelect.selectedIndex = i;
                break;
            }
        }
    }
    document.getElementById('modalUpload').style.display = 'flex';
}

function closeUploadModal() {
    document.getElementById('modalUpload').style.display = 'none';
}

function openReuploadModalById(docId) {
    const el = document.getElementById('klien-doc-json-' + docId);
    if (!el) return;
    try {
        const data = JSON.parse(el.textContent);
        openReuploadModal(data.id, data.name, data.reason || '');
    } catch(e) {
        console.error("Gagal membuka modal reupload:", e);
    }
}

function openReuploadModal(docId, title, reason) {
    document.getElementById('formReupload').action = `/klien/dokumen/${docId}/reupload`;
    document.getElementById('reuploadDocTitle').textContent = title || '—';
    document.getElementById('reuploadReasonBox').textContent = reason ? `Alasan: "${reason}"` : '';
    document.getElementById('modalReupload').style.display = 'flex';
}

function closeReuploadModal() {
    document.getElementById('modalReupload').style.display = 'none';
}

function openDetailModalById(docId) {
    const el = document.getElementById('klien-doc-json-' + docId);
    if (!el) return;
    try {
        const data = JSON.parse(el.textContent);
        openDetailModal(data);
    } catch(e) {
        console.error("Gagal membuka detail dokumen:", e);
    }
}

function openDetailModal(data) {
    document.getElementById('detName').textContent = data.name;
    document.getElementById('detType').textContent = data.type;
    document.getElementById('detStatus').textContent = data.status;
    document.getElementById('detCase').textContent = data.case;
    document.getElementById('detUploader').textContent = data.uploader;
    document.getElementById('detLawyer').textContent = data.lawyer;
    document.getElementById('detSize').textContent = data.size;
    document.getElementById('detDate').textContent = data.date;
    document.getElementById('detDesc').textContent = data.desc;

    const reasonContainer = document.getElementById('detReasonContainer');
    if (data.reason) {
        reasonContainer.style.display = 'block';
        document.getElementById('detReason').textContent = data.reason;
    } else {
        reasonContainer.style.display = 'none';
    }

    const btnContainer = document.getElementById('detActionButtons');
    btnContainer.innerHTML = '';
    if (data.hasFile) {
        btnContainer.innerHTML = `
            <a href="${data.viewUrl}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;padding:7px 12px;background:#1e3a5f;color:#fff;border-radius:6px;font-size:.8rem;font-weight:600;text-decoration:none;">
                Lihat Berkas
            </a>
            <a href="${data.dlUrl}" style="display:inline-flex;align-items:center;gap:4px;padding:7px 12px;background:#059669;color:#fff;border-radius:6px;font-size:.8rem;font-weight:600;text-decoration:none;">
                Unduh Berkas
            </a>
        `;
    } else {
        btnContainer.innerHTML = `<span style="font-size:.78rem;color:#94a3b8;align-self:center;">Berkas fisik belum diunggah</span>`;
    }

    document.getElementById('modalDetail').style.display = 'flex';
}

function closeDetailModal() {
    document.getElementById('modalDetail').style.display = 'none';
}

// Close modals when clicking backdrop
window.addEventListener('click', function(e) {
    if (e.target.id === 'modalUpload') closeUploadModal();
    if (e.target.id === 'modalReupload') closeReuploadModal();
    if (e.target.id === 'modalDetail') closeDetailModal();
});
</script>
@endsection
