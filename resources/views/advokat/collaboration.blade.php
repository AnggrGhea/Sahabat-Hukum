@extends('layouts.advokat')

@section('title', 'Ruang Kolaborasi Dokumen — ' . $case->case_number)

@section('content')
<style>
/* CSS Khusus Ruang Kolaborasi Dokumen */
.collab-container { display: flex; flex-direction: column; gap: 20px; }
.collab-header { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.breadcrumb { font-size: 0.8rem; color: #64748b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
.breadcrumb a { color: #1e3a5f; text-decoration: none; font-weight: 500; }
.breadcrumb a:hover { text-decoration: underline; }
.case-title-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
.case-main-title { font-size: 1.35rem; font-weight: 700; color: #0f172a; margin: 0 0 6px 0; }
.case-meta-tags { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.tag-badge { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px; }
.tag-blue { background: #e0f2fe; color: #0369a1; }
.tag-navy { background: #eff6ff; color: #1e3a5f; border: 1px solid #bfdbfe; }
.tag-green { background: #ecfdf5; color: #059669; }
.tag-amber { background: #fffbeb; color: #b45309; }
.tag-purple { background: #f5f3ff; color: #6d28d9; }
.tag-gray { background: #f1f5f9; color: #475569; }

/* Grid Tim & Statistik */
.collab-summary-grid { display: grid; grid-template-columns: 360px 1fr; gap: 20px; }
@media (max-width: 1024px) { .collab-summary-grid { grid-template-columns: 1fr; } }

.team-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; }
.card-header-clean { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; }
.card-title-clean { font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px; }

.team-member-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 8px; background: #f8fafc; margin-bottom: 8px; border: 1px solid #f1f5f9; }
.team-avatar { width: 34px; height: 34px; border-radius: 50%; background: #1e3a5f; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; flex-shrink: 0; }
.team-info { margin-left: 10px; flex: 1; min-width: 0; }
.team-name { font-size: 0.85rem; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.team-role { font-size: 0.72rem; color: #64748b; }

.stats-grid-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; }
.mini-stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 4px; }
.mini-stat-label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; }
.mini-stat-value { font-size: 1.35rem; font-weight: 700; color: #0f172a; }

/* Filter & Document Area */
.main-collab-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; }
.collab-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
.filter-pill-group { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.filter-pill { font-size: 0.75rem; font-weight: 600; padding: 5px 12px; border-radius: 20px; text-decoration: none; border: 1px solid #cbd5e1; color: #475569; background: #fff; transition: all 0.15s; }
.filter-pill:hover { background: #f8fafc; border-color: #94a3b8; }
.filter-pill.active { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }

/* Documents Table / Card List */
.doc-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.doc-table th { background: #f8fafc; color: #64748b; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 10px 14px; text-align: left; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
.doc-table td { padding: 14px; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; vertical-align: middle; }
.doc-table tr:hover td { background: #fafcff; }

/* Status Badges */
.status-badge-draf { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.status-badge-revisi { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.status-badge-review { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.status-badge-final { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 700; }

.ver-badge { background: #f3e8ff; color: #7e22ce; font-weight: 700; padding: 2px 7px; border-radius: 6px; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 3px; }
.vis-badge-internal { background: #fef2f2; color: #991b1b; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.vis-badge-shared { background: #f0fdf4; color: #166534; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }

/* Action Buttons */
.btn-action-icon { width: 32px; height: 32px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; color: #475569; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; transition: all 0.15s; }
.btn-action-icon:hover { background: #eff6ff; color: #1e3a5f; border-color: #bfdbfe; }

/* Modals */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 16px; backdrop-filter: blur(2px); }
.modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
.modal-header { padding: 18px 22px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
.modal-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0; }
.modal-close { background: none; border: none; font-size: 1.4rem; color: #94a3b8; cursor: pointer; line-height: 1; }
.modal-body { padding: 20px 22px; }
.modal-footer { padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 14px 14px; }

.form-group { margin-bottom: 14px; }
.form-label { display: block; font-size: 0.78rem; font-weight: 600; color: #334155; margin-bottom: 5px; }
.form-control { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 0.85rem; color: #0f172a; box-sizing: border-box; }
.form-control:focus { outline: none; border-color: #1e3a5f; ring: 2px rgba(30,58,95,0.1); }
.form-help { font-size: 0.72rem; color: #64748b; margin-top: 4px; }

/* Timeline Versi */
.ver-timeline { display: flex; flex-direction: column; gap: 14px; margin-top: 12px; }
.ver-item { display: flex; gap: 12px; border-left: 2px solid #e2e8f0; padding-left: 16px; position: relative; }
.ver-item::before { content: ''; position: absolute; left: -6px; top: 3px; width: 10px; height: 10px; border-radius: 50%; background: #1e3a5f; border: 2px solid #fff; box-shadow: 0 0 0 2px #bfdbfe; }
.ver-item.is-final::before { background: #059669; box-shadow: 0 0 0 2px #a7f3d0; }

/* Comment bubble */
.comment-item { display: flex; gap: 10px; margin-bottom: 14px; }
.comment-avatar { width: 30px; height: 30px; border-radius: 50%; background: #334155; color: #fff; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.comment-bubble { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; flex: 1; }
.comment-meta { font-size: 0.72rem; color: #64748b; margin-bottom: 4px; display: flex; justify-content: space-between; }
.comment-text { font-size: 0.85rem; color: #1e293b; line-height: 1.5; white-space: pre-wrap; }
</style>

<div class="collab-container">

    {{-- Breadcrumb & Title --}}
    <div class="collab-header">
        <div class="breadcrumb">
            <a href="{{ route('advokat.dashboard') }}">Beranda</a>
            <span>/</span>
            <a href="{{ route('advokat.cases') }}">Perkara</a>
            <span>/</span>
            <a href="{{ route('advokat.cases.show', $case->id) }}">{{ $case->case_number }}</a>
            <span>/</span>
            <span style="color:#0f172a;font-weight:600;">Ruang Kolaborasi Dokumen</span>
        </div>

        <div class="case-title-row">
            <div>
                <h1 class="case-main-title">{{ $case->title }}</h1>
                <div class="case-meta-tags">
                    <span class="tag-badge tag-navy">
                        <i data-lucide="hash" style="width:12px;height:12px;"></i> {{ $case->case_number }}
                    </span>
                    <span class="tag-badge tag-blue">
                        <i data-lucide="tag" style="width:12px;height:12px;"></i> {{ $case->case_type }}
                    </span>
                    <span class="tag-badge tag-gray">
                        <i data-lucide="user" style="width:12px;height:12px;"></i> Klien: {{ $case->client->name }}
                    </span>
                    <span class="tag-badge tag-amber">
                        <i data-lucide="activity" style="width:12px;height:12px;"></i> Status: {{ $case->status }}
                    </span>
                </div>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <a href="{{ route('advokat.cases.show', $case->id) }}" class="btn btn-outline" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:0.8rem;padding:8px 14px;background:#fff;">
                    <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Ke Detail Perkara
                </a>
                <button type="button" onclick="openModalUploadDokumen()" class="btn btn-primary" style="background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:0.8rem;font-weight:600;padding:8px 16px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 4px rgba(30,58,95,0.2);">
                    <i data-lucide="plus-circle" style="width:15px;height:15px;"></i> Unggah Dokumen Baru
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:8px;font-size:.85rem;display:flex;align-items:center;gap:8px;">
        <i data-lucide="check-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:.85rem;display:flex;align-items:center;gap:8px;">
        <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Grid Tim Advokat & Metrik Dokumen --}}
    <div class="collab-summary-grid">
        {{-- Kartu Tim Advokat --}}
        <div class="team-card">
            <div class="card-header-clean">
                <h3 class="card-title-clean">
                    <i data-lucide="users" style="width:17px;height:17px;color:#1e3a5f;"></i> Tim Advokat Perkara
                </h3>
                @if($isLead)
                <button type="button" onclick="openModalTambahTim()" style="background:#eff6ff;color:#1e3a5f;border:1px solid #bfdbfe;border-radius:6px;font-size:0.75rem;font-weight:600;padding:4px 10px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                    <i data-lucide="user-plus" style="width:12px;height:12px;"></i> Tambah
                </button>
                @endif
            </div>

            {{-- Ketua Tim (Lawyer Utama) --}}
            <div class="team-member-item" style="border-left:3px solid #1e3a5f;background:#f8fafc;">
                <div class="team-avatar" style="background:#1e3a5f;">
                    {{ substr($case->lawyer->name, 0, 1) }}
                </div>
                <div class="team-info">
                    <div class="team-name">{{ $case->lawyer->name }} @if(Auth::id() === $case->lawyer_id)<span style="color:#2563eb;font-size:0.7rem;">(Anda)</span>@endif</div>
                    <div class="team-role" style="color:#0369a1;font-weight:600;">Ketua Tim / Penanggung Jawab</div>
                </div>
                <span class="tag-badge tag-navy" style="font-size:0.68rem;">Lead</span>
            </div>

            {{-- Anggota Tim Tambahan --}}
            @forelse($case->caseLawyers as $cl)
            <div class="team-member-item">
                <div class="team-avatar" style="background:#475569;">
                    {{ substr($cl->lawyer->name, 0, 1) }}
                </div>
                <div class="team-info">
                    <div class="team-name">{{ $cl->lawyer->name }} @if(Auth::id() === $cl->user_id)<span style="color:#2563eb;font-size:0.7rem;">(Anda)</span>@endif</div>
                    <div class="team-role">{{ $cl->role_in_case }}</div>
                </div>
                @if($isLead && $cl->user_id !== Auth::id())
                <form action="{{ route('advokat.cases.team.remove', ['id' => $case->id, 'userId' => $cl->user_id]) }}" method="POST" onsubmit="return confirm('Cabut akses advokat ini dari tim penanganan perkara?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="Cabut Anggota Tim" style="background:none;border:none;color:#ef4444;cursor:pointer;padding:4px;">
                        <i data-lucide="user-x" style="width:15px;height:15px;"></i>
                    </button>
                </form>
                @endif
            </div>
            @empty
            <div style="font-size:0.78rem;color:#94a3b8;padding:8px 0;text-align:center;">
                Belum ada advokat pendamping yang ditugaskan.
            </div>
            @endforelse
        </div>

        {{-- Metrik Kolaborasi Dokumen --}}
        <div class="stats-grid-row">
            <div class="mini-stat-card">
                <span class="mini-stat-label">Total Dokumen</span>
                <span class="mini-stat-value">{{ $stats['total'] }}</span>
                <span style="font-size:0.7rem;color:#64748b;">Seluruh berkas perkara</span>
            </div>
            <div class="mini-stat-card" style="border-left:3px solid #64748b;">
                <span class="mini-stat-label">Draf</span>
                <span class="mini-stat-value">{{ $stats['draf'] }}</span>
                <span style="font-size:0.7rem;color:#64748b;">Tahap perancangan</span>
            </div>
            <div class="mini-stat-card" style="border-left:3px solid #2563eb;">
                <span class="mini-stat-label">Dalam Revisi</span>
                <span class="mini-stat-value" style="color:#2563eb;">{{ $stats['revisi'] }}</span>
                <span style="font-size:0.7rem;color:#64748b;">Penyempurnaan tim</span>
            </div>
            <div class="mini-stat-card" style="border-left:3px solid #f59e0b;">
                <span class="mini-stat-label">Menunggu Persetujuan</span>
                <span class="mini-stat-value" style="color:#d97706;">{{ $stats['review'] }}</span>
                <span style="font-size:0.7rem;color:#64748b;">Perlu review ketua tim</span>
            </div>
            <div class="mini-stat-card" style="border-left:3px solid #10b981;">
                <span class="mini-stat-label">Versi Final</span>
                <span class="mini-stat-value" style="color:#059669;">{{ $stats['final'] }}</span>
                <span style="font-size:0.7rem;color:#64748b;">Siap disidangkan / arsip</span>
            </div>
            <div class="mini-stat-card" style="border-left:3px solid #ef4444;">
                <span class="mini-stat-label">Internal Advokat</span>
                <span class="mini-stat-value" style="color:#991b1b;">{{ $stats['internal'] }}</span>
                <span style="font-size:0.7rem;color:#64748b;">Tertutup untuk klien</span>
            </div>
        </div>
    </div>

    {{-- Main Working Area: Daftar Dokumen Kolaborasi --}}
    <div class="main-collab-card">
        {{-- Toolbar Filter & Pencarian --}}
        <div class="collab-toolbar">
            <div class="filter-pill-group">
                <a href="{{ route('advokat.cases.collaboration', ['id' => $case->id, 'status' => 'all', 'type' => $typeFilter, 'visibility' => $visibilityFilter]) }}" class="filter-pill {{ $statusFilter === 'all' ? 'active' : '' }}">
                    Semua ({{ $stats['total'] }})
                </a>
                <a href="{{ route('advokat.cases.collaboration', ['id' => $case->id, 'status' => 'Draf', 'type' => $typeFilter, 'visibility' => $visibilityFilter]) }}" class="filter-pill {{ $statusFilter === 'Draf' ? 'active' : '' }}">
                    Draf ({{ $stats['draf'] }})
                </a>
                <a href="{{ route('advokat.cases.collaboration', ['id' => $case->id, 'status' => 'Dalam Revisi', 'type' => $typeFilter, 'visibility' => $visibilityFilter]) }}" class="filter-pill {{ $statusFilter === 'Dalam Revisi' ? 'active' : '' }}">
                    Dalam Revisi ({{ $stats['revisi'] }})
                </a>
                <a href="{{ route('advokat.cases.collaboration', ['id' => $case->id, 'status' => 'Menunggu Persetujuan', 'type' => $typeFilter, 'visibility' => $visibilityFilter]) }}" class="filter-pill {{ $statusFilter === 'Menunggu Persetujuan' ? 'active' : '' }}">
                    Menunggu Persetujuan ({{ $stats['review'] }})
                </a>
                <a href="{{ route('advokat.cases.collaboration', ['id' => $case->id, 'status' => 'Final', 'type' => $typeFilter, 'visibility' => $visibilityFilter]) }}" class="filter-pill {{ $statusFilter === 'Final' ? 'active' : '' }}">
                    Final ({{ $stats['final'] }})
                </a>
            </div>

            <form method="GET" action="{{ route('advokat.cases.collaboration', $case->id) }}" style="display:flex;gap:8px;align-items:center;">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <select name="type" onchange="this.form.submit()" class="form-control" style="width:160px;font-size:0.78rem;padding:6px 8px;">
                    <option value="all">Semua Jenis</option>
                    <option value="Surat Kuasa" {{ $typeFilter === 'Surat Kuasa' ? 'selected' : '' }}>Surat Kuasa</option>
                    <option value="Surat Gugatan" {{ $typeFilter === 'Surat Gugatan' ? 'selected' : '' }}>Surat Gugatan</option>
                    <option value="Jawaban" {{ $typeFilter === 'Jawaban' ? 'selected' : '' }}>Jawaban</option>
                    <option value="Replik" {{ $typeFilter === 'Replik' ? 'selected' : '' }}>Replik</option>
                    <option value="Duplik" {{ $typeFilter === 'Duplik' ? 'selected' : '' }}>Duplik</option>
                    <option value="Alat Bukti" {{ $typeFilter === 'Alat Bukti' ? 'selected' : '' }}>Alat Bukti</option>
                    <option value="Kajian Hukum" {{ $typeFilter === 'Kajian Hukum' ? 'selected' : '' }}>Kajian Hukum</option>
                    <option value="Dokumen Lainnya" {{ $typeFilter === 'Dokumen Lainnya' ? 'selected' : '' }}>Dokumen Lainnya</option>
                </select>

                <select name="visibility" onchange="this.form.submit()" class="form-control" style="width:150px;font-size:0.78rem;padding:6px 8px;">
                    <option value="all">Semua Akses</option>
                    <option value="internal" {{ $visibilityFilter === 'internal' ? 'selected' : '' }}>🔒 Internal Tim</option>
                    <option value="klien" {{ $visibilityFilter === 'klien' ? 'selected' : '' }}>👥 Dibagikan Klien</option>
                </select>

                <div style="position:relative;">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari dokumen..." class="form-control" style="width:180px;font-size:0.78rem;padding:6px 10px 6px 28px;">
                    <i data-lucide="search" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);width:13px;height:13px;color:#94a3b8;"></i>
                </div>
            </form>
        </div>

        {{-- Tabel Dokumen Kolaborasi --}}
        <div style="overflow-x:auto;">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Dokumen & Jenis</th>
                        <th>Akses</th>
                        <th>Versi Aktif</th>
                        <th>Status Kolaborasi</th>
                        <th>Pengunggah Terakhir</th>
                        <th>Pembaruan</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    @php
                        $latestVer = $doc->documentVersions->first();
                        $statusClass = match($doc->collaboration_status) {
                            'Final'                => 'status-badge-final',
                            'Menunggu Persetujuan' => 'status-badge-review',
                            'Dalam Revisi'         => 'status-badge-revisi',
                            default                => 'status-badge-draf',
                        };
                        $commentsCount = $doc->comments->count();
                        $versionsCount = $doc->documentVersions->count();
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;color:#1e3a5f;flex-shrink:0;">
                                    <i data-lucide="file-text" style="width:18px;height:18px;"></i>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:#0f172a;font-size:0.875rem;">
                                        {{ $doc->name }}
                                    </div>
                                    <div style="font-size:0.72rem;color:#64748b;display:flex;gap:6px;align-items:center;margin-top:2px;">
                                        <span class="tag-badge tag-navy" style="font-size:0.68rem;padding:1px 6px;">{{ $doc->document_type }}</span>
                                        <span>•</span>
                                        <span>{{ $doc->formatted_file_size }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($doc->is_internal)
                            <span class="vis-badge-internal" title="Hanya dapat dilihat oleh Advokat tim penanganan perkara">
                                <i data-lucide="lock" style="width:11px;height:11px;"></i> Internal Advokat
                            </span>
                            @else
                            <span class="vis-badge-shared" title="Dokumen terlihat oleh Klien">
                                <i data-lucide="eye" style="width:11px;height:11px;"></i> Dibagikan Klien
                            </span>
                            @endif
                        </td>

                        <td>
                            <span class="ver-badge">
                                <i data-lucide="git-branch" style="width:11px;height:11px;"></i> v{{ $doc->version ?? 1 }}
                            </span>
                            <div style="font-size:0.7rem;color:#64748b;margin-top:2px;">{{ $versionsCount }} riwayat versi</div>
                        </td>

                        <td>
                            <span class="tag-badge {{ $statusClass }}">
                                @if($doc->collaboration_status === 'Final')
                                <i data-lucide="check" style="width:11px;height:11px;"></i>
                                @endif
                                {{ $doc->collaboration_status ?? 'Draf' }}
                            </span>
                        </td>

                        <td>
                            <div style="font-size:0.8rem;font-weight:500;color:#1e293b;">
                                {{ $doc->uploader?->name ?? 'Advokat' }}
                            </div>
                            <div style="font-size:0.7rem;color:#94a3b8;">
                                {{ $doc->uploader?->role === 'klien' ? 'Klien' : 'Advokat' }}
                            </div>
                        </td>

                        <td>
                            <div style="font-size:0.78rem;color:#475569;">
                                {{ $doc->updated_at ? \Carbon\Carbon::parse($doc->updated_at)->locale('id')->diffForHumans() : '—' }}
                            </div>
                            <div style="font-size:0.7rem;color:#94a3b8;">
                                {{ $doc->updated_at ? \Carbon\Carbon::parse($doc->updated_at)->format('d M Y, H:i') : '' }}
                            </div>
                        </td>

                        <td style="text-align:right;">
                            <div style="display:inline-flex;gap:6px;align-items:center;">
                                {{-- Tombol Download Versi Terkini --}}
                                @if($doc->file_path)
                                <a href="{{ route('documents.download', $doc->id) }}" class="btn-action-icon" title="Unduh Berkas Versi Terkini">
                                    <i data-lucide="download" style="width:15px;height:15px;"></i>
                                </a>
                                @endif

                                {{-- Tombol Preview Browser --}}
                                @if($doc->file_path)
                                <a href="{{ route('documents.view', $doc->id) }}" target="_blank" class="btn-action-icon" title="Pratinjau Dokumen">
                                    <i data-lucide="external-link" style="width:15px;height:15px;"></i>
                                </a>
                                @endif

                                {{-- Tombol Buka Riwayat Versi & Catatan --}}
                                <button type="button" onclick="openDetailModal({{ $doc->id }})" class="btn-action-icon" title="Riwayat Versi & Komentar Tim" style="position:relative;">
                                    <i data-lucide="message-square" style="width:15px;height:15px;"></i>
                                    @if($commentsCount > 0)
                                    <span style="position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;font-size:0.6rem;padding:1px 5px;border-radius:10px;font-weight:700;">{{ $commentsCount }}</span>
                                    @endif
                                </button>

                                {{-- Tombol Upload Revisi Baru --}}
                                <button type="button" onclick="openModalUploadRevisi({{ $doc->id }}, '{{ addslashes($doc->name) }}', {{ $doc->version ?? 1 }})" class="btn-action-icon" title="Unggah Versi Revisi Baru" style="color:#2563eb;">
                                    <i data-lucide="upload-cloud" style="width:15px;height:15px;"></i>
                                </button>

                                {{-- Tombol Finalisasi (Jika Belum Final) --}}
                                @if($doc->collaboration_status !== 'Final')
                                <button type="button" onclick="openModalFinalize({{ $doc->id }}, '{{ addslashes($doc->name) }}', {{ $doc->version ?? 1 }})" class="btn-action-icon" title="Tetapkan Versi Final" style="color:#059669;">
                                    <i data-lucide="check-circle" style="width:15px;height:15px;"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                <i data-lucide="folder-x" style="width:36px;height:36px;stroke-width:1.5;"></i>
                                <span style="font-size:0.9rem;font-weight:500;">Belum ada dokumen yang sesuai dengan filter ini.</span>
                                <button type="button" onclick="openModalUploadDokumen()" style="margin-top:6px;background:#1e3a5f;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:0.8rem;cursor:pointer;">
                                    Mulai Unggah Dokumen
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Riwayat Aktivitas Kolaborasi (Audit Trail) --}}
    <div class="team-card" style="margin-top:4px;">
        <div class="card-header-clean">
            <h3 class="card-title-clean">
                <i data-lucide="history" style="width:17px;height:17px;color:#1e3a5f;"></i> Log Riwayat Aktivitas & Perubahan
            </h3>
            <span style="font-size:0.75rem;color:#64748b;">Mencatat pengunggahan berkas, versi, komentar, dan penetapan status</span>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;">
            @forelse($auditLogs as $log)
            <div style="display:flex;align-items:flex-start;gap:12px;padding:9px 12px;border-radius:8px;background:#f8fafc;font-size:0.8rem;border:1px solid #f1f5f9;">
                <div style="width:24px;height:24px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#475569;flex-shrink:0;margin-top:2px;">
                    <i data-lucide="activity" style="width:12px;height:12px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="color:#1e293b;font-weight:500;">
                        {{ $log->notes }}
                    </div>
                    <div style="font-size:0.7rem;color:#94a3b8;margin-top:2px;">
                        Oleh <strong>{{ $log->user?->name ?? 'Sistem' }}</strong> • {{ \Carbon\Carbon::parse($log->created_at)->locale('id')->diffForHumans() }} ({{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }})
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:#94a3b8;font-size:0.8rem;padding:12px;">
                Belum ada aktivitas tercatat pada perkara ini.
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ─── MODAL 1: Unggah Dokumen Kolaborasi Baru ─────────────────────────── --}}
<div id="modalUploadDokumen" class="modal-overlay">
    <div class="modal-box">
        <form method="POST" action="{{ route('advokat.cases.collaboration.document', $case->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">Unggah Dokumen Baru</h3>
                <button type="button" onclick="closeModal('modalUploadDokumen')" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Dokumen Hukum <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" required placeholder="Mis: Surat Gugatan Wanprestasi v1" class="form-control">
                </div>

                <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="form-label">Jenis Dokumen <span style="color:#ef4444;">*</span></label>
                        <select name="document_type" required class="form-control">
                            <option value="Surat Kuasa">Surat Kuasa</option>
                            <option value="Surat Gugatan">Surat Gugatan</option>
                            <option value="Jawaban">Jawaban</option>
                            <option value="Replik">Replik</option>
                            <option value="Duplik">Duplik</option>
                            <option value="Alat Bukti">Alat Bukti</option>
                            <option value="Kajian Hukum">Kajian Hukum</option>
                            <option value="Dokumen Lainnya" selected>Dokumen Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Tingkat Kerahasiaan / Akses <span style="color:#ef4444;">*</span></label>
                        <select name="is_internal" required class="form-control">
                            <option value="1" selected>🔒 Internal Tim Advokat (Rahasia)</option>
                            <option value="0">👥 Terbuka / Dapat Dilihat Klien</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Berkas File Dokumen <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="file" required class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                    <div class="form-help">Mendukung PDF, DOC, DOCX, JPG, PNG, XLS (Maks. 10 MB). Berkas akan disimpan sebagai versi pertama (v1).</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Revisi Awal (Opsional)</label>
                    <textarea name="revision_notes" rows="2" placeholder="Catatan awal mengenai draf dokumen ini..." class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Uraian / Deskripsi Dokumen (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Keterangan singkat tentang isi dokumen..." class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalUploadDokumen')" class="btn btn-outline" style="border:1px solid #cbd5e1;background:#fff;padding:8px 14px;border-radius:8px;font-size:0.85rem;cursor:pointer;">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#1e3a5f;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;">Unggah Dokumen</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── MODAL 2: Unggah Versi Revisi Baru ───────────────────────────────── --}}
<div id="modalUploadRevisi" class="modal-overlay">
    <div class="modal-box">
        <form id="formUploadRevisi" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <div>
                    <h3 class="modal-title">Unggah Versi Revisi Baru</h3>
                    <div id="revisiDocName" style="font-size:0.8rem;color:#64748b;margin-top:2px;"></div>
                </div>
                <button type="button" onclick="closeModal('modalUploadRevisi')" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:0.8rem;color:#1e3a5f;">
                    <i data-lucide="info" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:4px;"></i>
                    Unggahan ini akan menghasilkan nomor versi berikutnya (<strong id="nextVerLabel"></strong>) tanpa menghapus atau menimpa berkas versi sebelumnya.
                </div>

                <div class="form-group">
                    <label class="form-label">Berkas Revisi Baru <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="file" required class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                    <div class="form-help">Format: PDF, DOC, DOCX, JPG, PNG (Maks. 10 MB).</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status Dokumen Setelah Revisi <span style="color:#ef4444;">*</span></label>
                    <select name="status" required class="form-control">
                        <option value="Dalam Revisi" selected>Dalam Revisi</option>
                        <option value="Menunggu Persetujuan">Menunggu Persetujuan (Siap Direview)</option>
                        <option value="Draf">Draf</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Perubahan / Revisi <span style="color:#ef4444;">*</span></label>
                    <textarea name="revision_notes" required rows="3" placeholder="Jelaskan pasal, klausul, atau bagian apa saja yang diperbaiki dalam versi baru ini..." class="form-control"></textarea>
                    <div class="form-help">Catatan ini akan tersimpan permanen pada riwayat versi untuk dibaca oleh seluruh anggota tim.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalUploadRevisi')" class="btn btn-outline" style="border:1px solid #cbd5e1;background:#fff;padding:8px 14px;border-radius:8px;font-size:0.85rem;cursor:pointer;">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#2563eb;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;">Unggah Versi Baru</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── MODAL 3: Detail Riwayat Versi & Diskusi Komentar ──────────────── --}}
@foreach($documents as $doc)
<div id="modalDetailDoc_{{ $doc->id }}" class="modal-overlay">
    <div class="modal-box" style="max-width:750px;">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">{{ $doc->name }}</h3>
                <div style="font-size:0.78rem;color:#64748b;margin-top:2px;">
                    Jenis: {{ $doc->document_type }} • Versi Terkini: <strong>v{{ $doc->version ?? 1 }}</strong> • Status: <strong>{{ $doc->collaboration_status }}</strong>
                </div>
            </div>
            <button type="button" onclick="closeModal('modalDetailDoc_{{ $doc->id }}')" class="modal-close">&times;</button>
        </div>

        <div class="modal-body">
            {{-- Tab Switcher Internal Modal --}}
            <div style="display:flex;gap:10px;border-bottom:1px solid #e2e8f0;margin-bottom:16px;">
                <button type="button" onclick="switchDetailTab({{ $doc->id }}, 'versions')" id="tabBtn_ver_{{ $doc->id }}" style="background:none;border:none;border-bottom:2px solid #1e3a5f;padding:8px 14px;font-weight:600;font-size:0.85rem;color:#1e3a5f;cursor:pointer;">
                    <i data-lucide="git-branch" style="width:13px;height:13px;display:inline-block;vertical-align:middle;"></i> Riwayat Versi ({{ $doc->documentVersions->count() }})
                </button>
                <button type="button" onclick="switchDetailTab({{ $doc->id }}, 'comments')" id="tabBtn_com_{{ $doc->id }}" style="background:none;border:none;border-bottom:2px solid transparent;padding:8px 14px;font-weight:600;font-size:0.85rem;color:#64748b;cursor:pointer;">
                    <i data-lucide="message-square" style="width:13px;height:13px;display:inline-block;vertical-align:middle;"></i> Catatan & Komentar ({{ $doc->comments->count() }})
                </button>
            </div>

            {{-- TAB 1: Riwayat Versi --}}
            <div id="tabContent_ver_{{ $doc->id }}">
                <div class="ver-timeline">
                    @forelse($doc->documentVersions as $v)
                    <div class="ver-item {{ $v->is_final ? 'is-final' : '' }}">
                        <div style="flex:1;">
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span class="ver-badge">v{{ $v->version_number }}</span>
                                    @if($v->is_final)
                                    <span class="tag-badge tag-green" style="font-size:0.68rem;">✓ VERSI FINAL</span>
                                    @else
                                    <span class="tag-badge tag-gray" style="font-size:0.68rem;">{{ $v->status }}</span>
                                    @endif
                                    <span style="font-size:0.75rem;font-weight:600;color:#0f172a;">{{ $v->original_filename }}</span>
                                </div>
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <span style="font-size:0.7rem;color:#64748b;">{{ $v->formatted_file_size }}</span>
                                    <a href="{{ route('documents.version.download', ['id' => $doc->id, 'versionId' => $v->id]) }}" class="btn-action-icon" title="Unduh Berkas Versi v{{ $v->version_number }}" style="width:28px;height:28px;">
                                        <i data-lucide="download" style="width:13px;height:13px;"></i>
                                    </a>
                                </div>
                            </div>

                            @if($v->revision_notes)
                            <div style="margin-top:6px;font-size:0.8rem;background:#f8fafc;padding:6px 10px;border-radius:6px;color:#334155;border:1px solid #f1f5f9;">
                                <strong>Catatan:</strong> {{ $v->revision_notes }}
                            </div>
                            @endif

                            <div style="margin-top:4px;font-size:0.7rem;color:#94a3b8;">
                                Diunggah oleh <strong>{{ $v->uploader?->name ?? 'Advokat' }}</strong> • {{ \Carbon\Carbon::parse($v->created_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="color:#94a3b8;font-size:0.8rem;text-align:center;padding:16px;">Belum ada riwayat versi tersimpan.</div>
                    @endforelse
                </div>
            </div>

            {{-- TAB 2: Catatan & Komentar --}}
            <div id="tabContent_com_{{ $doc->id }}" style="display:none;">
                <div style="max-height:260px;overflow-y:auto;margin-bottom:14px;padding-right:6px;">
                    @forelse($doc->comments as $com)
                    <div class="comment-item">
                        <div class="comment-avatar">
                            {{ substr($com->user?->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="comment-bubble">
                            <div class="comment-meta">
                                <strong>{{ $com->user?->name }} @if(Auth::id() === $com->user_id)<span style="color:#2563eb;">(Anda)</span>@endif</strong>
                                <span>
                                    @if($com->version)
                                    <span class="ver-badge" style="font-size:0.65rem;padding:1px 4px;">v{{ $com->version->version_number }}</span>
                                    @endif
                                    {{ \Carbon\Carbon::parse($com->created_at)->locale('id')->diffForHumans() }}
                                </span>
                            </div>
                            <div class="comment-text">{{ $com->comment }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;color:#94a3b8;font-size:0.8rem;padding:24px 0;">
                        Belum ada catatan atau komentar tim pada dokumen ini.
                    </div>
                    @endforelse
                </div>

                {{-- Form Kirim Komentar --}}
                <form method="POST" action="{{ route('advokat.collaboration.comment', $doc->id) }}">
                    @csrf
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <div style="flex:1;">
                            <textarea name="comment" required rows="2" placeholder="Tulis catatan atau saran revisi untuk tim..." class="form-control" style="font-size:0.825rem;"></textarea>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:4px;">
                            <select name="document_version_id" class="form-control" style="font-size:0.75rem;padding:6px;width:110px;">
                                <option value="">Terkait Versi:</option>
                                @foreach($doc->documentVersions as $vOpt)
                                <option value="{{ $vOpt->id }}" {{ $loop->first ? 'selected' : '' }}>v{{ $vOpt->version_number }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary" style="background:#1e3a5f;color:#fff;border:none;border-radius:6px;padding:6px 12px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                                Kirim
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-footer" style="justify-content:space-between;">
            <div>
                {{-- Form Cepat Ubah Status Kolaborasi --}}
                <form method="POST" action="{{ route('advokat.collaboration.status', $doc->id) }}" style="display:inline-flex;gap:6px;align-items:center;">
                    @csrf
                    <span style="font-size:0.75rem;color:#64748b;font-weight:600;">Ubah Status:</span>
                    <select name="status" onchange="this.form.submit()" class="form-control" style="font-size:0.75rem;padding:4px 8px;width:160px;">
                        <option value="Draf" {{ $doc->collaboration_status === 'Draf' ? 'selected' : '' }}>Draf</option>
                        <option value="Dalam Revisi" {{ $doc->collaboration_status === 'Dalam Revisi' ? 'selected' : '' }}>Dalam Revisi</option>
                        <option value="Menunggu Persetujuan" {{ $doc->collaboration_status === 'Menunggu Persetujuan' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                    </select>
                </form>
            </div>
            <button type="button" onclick="closeModal('modalDetailDoc_{{ $doc->id }}')" class="btn btn-outline" style="border:1px solid #cbd5e1;background:#fff;padding:6px 14px;border-radius:6px;font-size:0.8rem;cursor:pointer;">Tutup</button>
        </div>
    </div>
</div>
@endforeach

{{-- ─── MODAL 4: Konfirmasi Penetapan Versi Final ───────────────────────── --}}
<div id="modalFinalize" class="modal-overlay">
    <div class="modal-box" style="max-width:500px;">
        <form id="formFinalize" method="POST" action="">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title" style="color:#047857;">Tetapkan Dokumen sebagai Versi Final</h3>
                <button type="button" onclick="closeModal('modalFinalize')" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display:flex;gap:12px;align-items:flex-start;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;padding:12px 14px;color:#065f46;font-size:0.85rem;">
                    <i data-lucide="shield-check" style="width:24px;height:24px;flex-shrink:0;"></i>
                    <div>
                        <strong id="finalizeDocTitle"></strong>
                        <p style="margin:4px 0 0 0;font-size:0.8rem;color:#047857;line-height:1.4;">
                            Versi terakhir (<span id="finalizeVerNum"></span>) akan dikunci sebagai <strong>Versi Final Resmi</strong>. Jika terdapat perubahan di kemudian hari, Anda tetap dapat mengunggah revisi baru yang akan dicatat sebagai versi selanjutnya.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalFinalize')" class="btn btn-outline" style="border:1px solid #cbd5e1;background:#fff;padding:8px 14px;border-radius:8px;font-size:0.85rem;cursor:pointer;">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#059669;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;">Tetapkan Final Sekarang</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── MODAL 5: Tambah Anggota Tim Advokat ─────────────────────────────── --}}
@if($isLead)
<div id="modalTambahTim" class="modal-overlay">
    <div class="modal-box" style="max-width:500px;">
        <form method="POST" action="{{ route('advokat.cases.team.add', $case->id) }}">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">Tambah Anggota Tim Advokat</h3>
                <button type="button" onclick="closeModal('modalTambahTim')" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Pilih Rekan Advokat <span style="color:#ef4444;">*</span></label>
                    <select name="user_id" required class="form-control">
                        <option value="">-- Pilih Advokat Terdaftar --</option>
                        @foreach($availableLawyers as $avLawyer)
                        <option value="{{ $avLawyer->id }}">{{ $avLawyer->name }} ({{ $avLawyer->email }})</option>
                        @endforeach
                    </select>
                    @if($availableLawyers->isEmpty())
                    <div class="form-help" style="color:#b45309;">Seluruh akun advokat lain telah ditugaskan pada perkara ini.</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">Peran dalam Penanganan Perkara</label>
                    <select name="role_in_case" class="form-control">
                        <option value="Anggota Tim / Co-Counsel" selected>Anggota Tim / Co-Counsel</option>
                        <option value="Advokat Pendamping">Advokat Pendamping</option>
                        <option value="Penyusun Dokumen Hukum">Penyusun Dokumen Hukum</option>
                        <option value="Peneliti Hukum">Peneliti Hukum</option>
                    </select>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;font-size:0.75rem;color:#64748b;">
                    Advokat yang ditambahkan akan memperoleh akses penuh ke dokumen internal perkara ini dan menerima notifikasi saat ada perubahan versi atau komentar baru.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalTambahTim')" class="btn btn-outline" style="border:1px solid #cbd5e1;background:#fff;padding:8px 14px;border-radius:8px;font-size:0.85rem;cursor:pointer;">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#1e3a5f;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;" {{ $availableLawyers->isEmpty() ? 'disabled' : '' }}>Tugaskan ke Perkara</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.display = 'flex';
        lucide.createIcons();
    }
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

function openModalUploadDokumen() {
    openModal('modalUploadDokumen');
}

function openModalTambahTim() {
    openModal('modalTambahTim');
}

function openModalUploadRevisi(docId, docName, currentVer) {
    const nextVer = parseInt(currentVer) + 1;
    document.getElementById('revisiDocName').textContent = 'Dokumen: ' + docName;
    document.getElementById('nextVerLabel').textContent = 'Versi v' + nextVer;
    document.getElementById('formUploadRevisi').action = '/advokat/kolaborasi/dokumen/' + docId + '/versi';
    openModal('modalUploadRevisi');
}

function openModalFinalize(docId, docName, verNum) {
    document.getElementById('finalizeDocTitle').textContent = docName;
    document.getElementById('finalizeVerNum').textContent = 'v' + verNum;
    document.getElementById('formFinalize').action = '/advokat/kolaborasi/dokumen/' + docId + '/final';
    openModal('modalFinalize');
}

function openDetailModal(docId) {
    openModal('modalDetailDoc_' + docId);
}

function switchDetailTab(docId, tab) {
    const verContent = document.getElementById('tabContent_ver_' + docId);
    const comContent = document.getElementById('tabContent_com_' + docId);
    const verBtn     = document.getElementById('tabBtn_ver_' + docId);
    const comBtn     = document.getElementById('tabBtn_com_' + docId);

    if (tab === 'versions') {
        verContent.style.display = 'block';
        comContent.style.display = 'none';
        verBtn.style.borderBottomColor = '#1e3a5f';
        verBtn.style.color = '#1e3a5f';
        comBtn.style.borderBottomColor = 'transparent';
        comBtn.style.color = '#64748b';
    } else {
        verContent.style.display = 'none';
        comContent.style.display = 'block';
        comBtn.style.borderBottomColor = '#1e3a5f';
        comBtn.style.color = '#1e3a5f';
        verBtn.style.borderBottomColor = 'transparent';
        verBtn.style.color = '#64748b';
    }
    lucide.createIcons();
}

// Tutup modal jika user klik di luar area modal box
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});
</script>
@endpush
@endsection
