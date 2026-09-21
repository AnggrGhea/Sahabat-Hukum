@extends('layouts.advokat')

@section('title', 'Perkara')

@section('content')
<style>
.perkara-layout { display: grid; grid-template-columns: 340px 1fr; gap: 0; height: calc(100vh - 80px); border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; }
.p-list-panel { border-right: 1px solid #e2e8f0; overflow-y: auto; background: #fff; }
.p-list-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
.p-list-title { font-size: 1rem; font-weight: 600; color: #1e293b; }
.btn-baru { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #1e3a5f; color: #fff; border: none; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; text-decoration: none; }
.p-item { display: block; padding: 14px 20px; cursor: pointer; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: background .15s; }
.p-item:hover { background: #f8fafc; }
.p-item.active-item { background: #eff6ff; border-left: 3px solid #1e3a5f; }
.p-case-number { font-size: .8rem; font-weight: 700; color: #1e3a5f; }
.p-case-client { font-size: .85rem; font-weight: 600; color: #1e293b; }
.p-case-type { font-size: .75rem; color: #64748b; }
.p-case-date { font-size: .72rem; color: #94a3b8; }
.p-status-row { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
/* Detail */
.p-detail-panel { overflow-y: auto; padding: 0; background: #f8fafc; }
.p-detail-empty { display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; gap: 12px; color: #94a3b8; }
.p-detail-topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; }
.p-nomor-label { font-size: .72rem; color: #64748b; font-weight: 500; }
.p-nomor { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
.p-type-badges { display: flex; gap: 6px; margin-top: 4px; }
.p-type-badge { font-size: .72rem; padding: 3px 10px; border-radius: 20px; background: #e0f2fe; color: #0369a1; font-weight: 500; }
.p-topbar-actions { display: flex; gap: 8px; }
.btn-tambah-perkembangan { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #1e3a5f; color: #fff; border: none; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-tambah-perkembangan:hover { background: #0f172a; }
.btn-minta-dokumen { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #fff; color: #1e3a5f; border: 1px solid #cbd5e1; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-minta-dokumen:hover { background: #f8fafc; border-color: #94a3b8; }
.p-detail-body { padding: 20px 24px; display: grid; grid-template-columns: 1fr 310px; gap: 18px; }
.p-info-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
.p-card-title { font-size: .75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 12px; }
.p-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.p-field label { font-size: .72rem; color: #94a3b8; }
.p-field span { font-size: .875rem; font-weight: 500; color: #1e293b; display: block; }
/* Timeline */
.tl { padding: 0; }
.tl-item { display: flex; gap: 12px; padding-bottom: 16px; }
.tl-dot-col { display: flex; flex-direction: column; align-items: center; }
.tl-dot { width: 28px; height: 28px; border-radius: 50%; background: #dbeafe; border: 2px solid #93c5fd; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.tl-dot i { width: 12px; height: 12px; color: #2563eb; }
.tl-line { width: 2px; flex: 1; background: #e2e8f0; margin-top: 4px; min-height: 20px; }
.tl-content { flex: 1; padding-top: 4px; }
.tl-date { font-size: .72rem; color: #94a3b8; margin-bottom: 2px; }
.tl-title { font-size: .85rem; font-weight: 600; color: #1e293b; }
.tl-desc { font-size: .78rem; color: #64748b; margin-top: 2px; line-height: 1.5; }

/* Dokumen Summary Card (Sesuai Referensi Halaman 2) */
.doc-summary-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.doc-summary-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; }
.doc-summary-title { font-size: .95rem; font-weight: 700; color: #1e293b; margin: 0; }
.doc-summary-link { font-size: .8rem; font-weight: 600; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: color .15s; }
.doc-summary-link:hover { color: #1d4ed8; text-decoration: underline; }

.doc-summary-list { display: flex; flex-direction: column; gap: 8px; }
.doc-card-item { display: flex; align-items: center; gap: 12px; padding: 9px 12px; border-radius: 8px; border: 1px solid #f1f5f9; background: #fff; cursor: pointer; transition: all .15s ease; text-decoration: none; user-select: none; }
.doc-card-item:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-1px); box-shadow: 0 2px 5px rgba(0,0,0,.04); }
.doc-card-icon { width: 36px; height: 36px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #64748b; }
.doc-card-item:hover .doc-card-icon { color: #1e3a5f; background: #eff6ff; border-color: #bfdbfe; }
.doc-card-info { flex: 1; min-width: 0; }
.doc-card-name { font-size: .85rem; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px; }
.doc-card-badge { display: inline-flex; align-items: center; gap: 4px; font-size: .7rem; font-weight: 600; padding: 2px 8px; border-radius: 12px; }
.badge-diterima { background: #dcfce7; color: #15803d; }
.badge-menunggu { background: #e0f2fe; color: #0369a1; }
.badge-belum { background: #f1f5f9; color: #64748b; }
.badge-ditolak { background: #fee2e2; color: #b91c1c; }

/* Form sections */
.form-section { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
.form-section label { font-size: .78rem; font-weight: 500; color: #374151; display: block; margin-bottom: 4px; margin-top: 8px; }
.form-section label:first-child { margin-top: 0; }
.form-section input, .form-section textarea, .form-section select { width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 10px; font-size: .85rem; color: #1e293b; background: #fff; box-sizing: border-box; }
.form-section .btn-submit { margin-top: 10px; padding: 8px 18px; background: #1e3a5f; color: #fff; border: none; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer; }
.hidden { display: none !important; }
.alert-success { background: #dcfce7; border: 1px solid #bbf7d0; color: #15803d; border-radius: 8px; padding: 10px 14px; margin: 16px 24px 0; font-size: .85rem; font-weight: 500; }
</style>

@if(session('success'))
<div class="alert-success">✓ {{ session('success') }}</div>
@endif

<div class="perkara-layout">
    {{-- LEFT: Case List --}}
    <div class="p-list-panel">
        <div class="p-list-header">
            <div class="p-list-title">Perkara</div>
            <button onclick="document.getElementById('modal-buat-perkara').classList.toggle('hidden')" class="btn-baru">
                <i data-lucide="plus" style="width:14px;"></i> Baru
            </button>
        </div>

        @forelse($cases as $item)
        @php
            $badgeClass = match($item->status) {
                'Persidangan' => 'badge-dijadwalkan',
                'Selesai'     => 'badge-selesai',
                'Dibatalkan'  => 'badge-dibatalkan',
                'Penyidikan'  => 'badge-menunggu',
                default       => 'status-badge' ,
            };
            $isActive = $case && $case->id === $item->id;
        @endphp
        <a href="{{ route('advokat.cases.show', $item->id) }}" class="p-item {{ $isActive ? 'active-item' : '' }}">
            <div class="p-case-number">{{ $item->case_number }}</div>
            <div class="p-case-client">{{ $item->client->name }}</div>
            <div class="p-case-type">{{ $item->case_type }}</div>
            <div class="p-status-row">
                <div class="p-case-date">Mulai: {{ \Carbon\Carbon::parse($item->started_at)->locale('id')->isoFormat('D MMM YYYY') }}</div>
                <span class="status-badge {{ $badgeClass }}">{{ $item->status }}</span>
            </div>
        </a>
        @empty
        <div style="text-align:center;padding:3rem;color:#94a3b8;font-size:.875rem;">Tidak ada perkara.</div>
        @endforelse
    </div>

    {{-- RIGHT: Detail Panel --}}
    <div class="p-detail-panel">
        @if($case)
        <div class="p-detail-topbar">
            <div>
                <div class="p-nomor-label">Nomor Perkara</div>
                <div class="p-nomor">{{ $case->case_number }}</div>
                <div class="p-type-badges">
                    @foreach(explode(' — ', $case->case_type) as $part)
                    <span class="p-type-badge">{{ trim($part) }}</span>
                    @endforeach
                    <span class="p-type-badge" style="background:#fef9c3;color:#b45309;">{{ $case->status }}</span>
                </div>
            </div>
            <div class="p-topbar-actions">
                @php
                    $caseConv = \App\Models\Conversation::where('case_id', $case->id)->first();
                @endphp
                @if($caseConv)
                <a href="{{ route('advokat.chat', ['conversation_id' => $caseConv->id]) }}" class="btn-minta-dokumen" style="background:#0b1a30; color:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                    <i data-lucide="message-circle" style="width:14px;"></i> Percakapan
                </a>
                @endif
                <button type="button" onclick="document.getElementById('form-progress').classList.toggle('hidden')" class="btn-tambah-perkembangan">
                    <i data-lucide="plus" style="width:14px;"></i> Tambah Perkembangan
                </button>
                <button type="button" onclick="openAdvokatMintaModal()" class="btn-minta-dokumen">
                    <i data-lucide="file-text" style="width:14px;"></i> Minta Dokumen
                </button>
            </div>
        </div>

        <div class="p-detail-body">
            {{-- LEFT: Info + Timeline --}}
            <div>
                {{-- Add Progress Form --}}
                <div id="form-progress" class="form-section hidden">
                    <strong style="font-size:.85rem;font-weight:600;color:#0369a1;">+ Tambah Perkembangan</strong>
                    <form method="POST" action="{{ route('advokat.cases.progress', $case->id) }}">
                        @csrf
                        <label>Judul Perkembangan</label>
                        <input type="text" name="title" required placeholder="Mis: Sidang mediasi selesai">
                        <label>Keterangan</label>
                        <textarea name="description" rows="3" placeholder="Uraian perkembangan..."></textarea>
                        <label>Tanggal</label>
                        <input type="date" name="progress_date" required value="{{ date('Y-m-d') }}">
                        <button type="submit" class="btn-submit">Simpan Perkembangan</button>
                    </form>
                </div>

                {{-- Informasi Klien --}}
                <div class="p-info-card">
                    <div class="p-card-title">Informasi Klien</div>
                    <div class="p-info-grid">
                        <div class="p-field"><label>Nama Klien</label><span>{{ $case->client->name }}</span></div>
                        <div class="p-field"><label>Tanggal Mulai</label><span>{{ \Carbon\Carbon::parse($case->started_at)->locale('id')->isoFormat('D MMM YYYY') }}</span></div>
                        @if($case->client->clientProfile)
                        <div class="p-field"><label>Telepon</label><span>{{ $case->client->clientProfile->phone ?? '—' }}</span></div>
                        @endif
                    </div>
                    @if($case->summary)
                    <div style="margin-top:10px;font-size:.85rem;color:#374151;line-height:1.6;border-top:1px solid #f1f5f9;padding-top:10px;">
                        {{ $case->summary }}
                    </div>
                    @endif
                </div>

                {{-- Timeline Perkembangan --}}
                <div class="p-info-card">
                    <div class="p-card-title">Timeline Perkembangan</div>
                    <div class="tl">
                        @forelse($case->progress as $prog)
                        <div class="tl-item">
                            <div class="tl-dot-col">
                                <div class="tl-dot"><i data-lucide="check" style="width:12px;height:12px;color:#2563eb;"></i></div>
                                @if(!$loop->last)<div class="tl-line"></div>@endif
                            </div>
                            <div class="tl-content">
                                <div class="tl-date">{{ \Carbon\Carbon::parse($prog->progress_date)->locale('id')->isoFormat('D MMM YYYY') }}</div>
                                <div class="tl-title">{{ $prog->title }}</div>
                                @if($prog->description)<div class="tl-desc">{{ $prog->description }}</div>@endif
                            </div>
                        </div>
                        @empty
                        <div style="color:#94a3b8;font-size:.85rem;">Belum ada perkembangan.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- RIGHT: Ringkasan Dokumen Perkara (Sesuai Desain Referensi Halaman 2) --}}
            <div>
                <div class="doc-summary-card">
                    <div class="doc-summary-header">
                        <h4 class="doc-summary-title">Dokumen</h4>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <button type="button" onclick="openDocumentScanner({ caseId: {{ $case->id }}, isAdvocate: true })"
                                    style="padding:4px 10px;background:#fff;border:1px solid #cbd5e1;border-radius:6px;color:#1e3a5f;font-size:.75rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                                <i data-lucide="camera" style="width:13px;height:13px;"></i> Scan Dokumen
                            </button>
                            <a href="{{ route('advokat.documents', ['case_id' => $case->id]) }}" class="doc-summary-link" title="Buka Pusat Manajemen Dokumen">
                                Lihat Semua
                            </a>
                        </div>
                    </div>

                    <div class="doc-summary-list">
                        @php
                            $allSummaryDocs = collect();

                            // 1. Dokumen perkara yang sudah diunggah
                            foreach ($case->documents as $doc) {
                                $isVerified = ($doc->status === 'Terverifikasi' || $doc->status === 'Sudah Diterima');
                                $isRejected = in_array($doc->status, ['Ditolak', 'Perlu Diperbaiki']);

                                $statusLabel = match(true) {
                                    $isVerified => 'Diterima',
                                    $isRejected => 'Ditolak',
                                    default     => 'Menunggu Verifikasi',
                                };

                                $statusType = match(true) {
                                    $isVerified => 'diterima',
                                    $isRejected => 'ditolak',
                                    default     => 'menunggu',
                                };

                                $badgeClass = match(true) {
                                    $isVerified => 'badge-diterima',
                                    $isRejected => 'badge-ditolak',
                                    default     => 'badge-menunggu',
                                };

                                $allSummaryDocs->push([
                                    'type'             => 'document',
                                    'id'               => $doc->id,
                                    'name'             => $doc->name,
                                    'file_name'        => basename($doc->file_path ?: $doc->name),
                                    'category'         => $doc->document_type ?? 'Dokumen Perkara',
                                    'uploader'         => $doc->is_from_lawyer ? 'Advokat (Anda)' : ($doc->uploader?->name ?? 'Klien'),
                                    'date'             => $doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') : '—',
                                    'size'             => $doc->formatted_file_size,
                                    'version'          => 'v' . ($doc->version ?? 1),
                                    'status'           => $statusLabel,
                                    'status_type'      => $statusType,
                                    'badge_class'      => $badgeClass,
                                    'description'      => $doc->description ?? '',
                                    'rejection_reason' => $doc->rejection_reason ?? '',
                                    'has_file'         => $doc->file_path ? '1' : '0',
                                    'view_url'         => route('documents.view', $doc->id),
                                    'download_url'     => route('documents.download', $doc->id),
                                    'verify_url'       => route('advokat.documents.verify', $doc->id),
                                    'reject_url'       => route('advokat.documents.reject', $doc->id),
                                    'mime'             => $doc->mime_type ?? '',
                                    'extension'        => strtolower(pathinfo($doc->file_path ?? '', PATHINFO_EXTENSION)),
                                ]);
                            }

                            // 2. Permintaan dokumen yang masih menunggu upload (belum ada file)
                            if ($case->documentRequests) {
                                foreach ($case->documentRequests->where('status', 'Menunggu Upload') as $req) {
                                    $allSummaryDocs->push([
                                        'type'             => 'request',
                                        'id'               => $req->id,
                                        'name'             => $req->title,
                                        'file_name'        => 'Belum diunggah oleh klien',
                                        'category'         => $req->document_type ?? 'Dokumen Diminta',
                                        'uploader'         => 'Menunggu dari ' . ($case->client->name ?? 'Klien'),
                                        'date'             => \Carbon\Carbon::parse($req->created_at)->locale('id')->isoFormat('D MMMM YYYY'),
                                        'size'             => '—',
                                        'version'          => 'v1',
                                        'status'           => 'Belum Diunggah',
                                        'status_type'      => 'belum',
                                        'badge_class'      => 'badge-belum',
                                        'description'      => $req->description ?? 'Tidak ada instruksi khusus.',
                                        'rejection_reason' => '',
                                        'has_file'         => '0',
                                        'view_url'         => '',
                                        'download_url'     => '',
                                        'verify_url'       => '',
                                        'reject_url'       => '',
                                        'mime'             => '',
                                        'extension'        => '',
                                        'due_date'         => $req->due_date ? \Carbon\Carbon::parse($req->due_date)->locale('id')->isoFormat('D MMMM YYYY') : 'Tidak ditentukan',
                                        'priority'         => $req->priority ?? 'Normal',
                                    ]);
                                }
                            }
                        @endphp

                        @forelse($allSummaryDocs as $item)
                        <div class="doc-card-item"
                             onclick="openDetailDokumenPerkaraModal(this)"
                             data-type="{{ $item['type'] }}"
                             data-id="{{ $item['id'] }}"
                             data-name="{{ e($item['name']) }}"
                             data-file-name="{{ e($item['file_name']) }}"
                             data-category="{{ e($item['category']) }}"
                             data-uploader="{{ e($item['uploader']) }}"
                             data-date="{{ $item['date'] }}"
                             data-size="{{ $item['size'] }}"
                             data-version="{{ $item['version'] }}"
                             data-status="{{ $item['status'] }}"
                             data-status-type="{{ $item['status_type'] }}"
                             data-description="{{ e($item['description']) }}"
                             data-rejection-reason="{{ e($item['rejection_reason']) }}"
                             data-has-file="{{ $item['has_file'] }}"
                             data-view-url="{{ $item['view_url'] }}"
                             data-download-url="{{ $item['download_url'] }}"
                             data-verify-url="{{ $item['verify_url'] }}"
                             data-reject-url="{{ $item['reject_url'] }}"
                             data-mime="{{ e($item['mime']) }}"
                             data-extension="{{ e($item['extension']) }}"
                             @if(isset($item['due_date'])) data-due-date="{{ e($item['due_date']) }}" @endif
                             @if(isset($item['priority'])) data-priority="{{ e($item['priority']) }}" @endif
                             title="Klik untuk melihat detail berkas">
                            <div class="doc-card-icon">
                                <i data-lucide="file-text" style="width:18px;height:18px;"></i>
                            </div>
                            <div class="doc-card-info">
                                <div class="doc-card-name">{{ $item['name'] }}</div>
                                <span class="doc-card-badge {{ $item['badge_class'] }}">
                                    {{ $item['status'] }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div style="text-align:center;padding:24px 12px;color:#94a3b8;font-size:.825rem;">
                            <i data-lucide="folder-open" style="width:28px;height:28px;margin:0 auto 6px;display:block;opacity:.4;"></i>
                            Belum ada dokumen untuk perkara ini.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal: Detail Dokumen Perkara (Clickable Document Detail & Preview) --}}
        <div id="modal-detail-dokumen-perkara" style="position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(2px);">
            <div style="background:#fff;border-radius:14px;width:100%;max-width:580px;box-shadow:0 20px 35px -5px rgba(0,0,0,.2);max-height:90vh;display:flex;flex-direction:column;overflow:hidden;">
                {{-- Modal Header --}}
                <div style="padding:16px 22px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;background:#f8fafc;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#e0f2fe;color:#0369a1;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i data-lucide="file-text" style="width:18px;height:18px;"></i>
                        </div>
                        <div>
                            <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Detail Dokumen Perkara</h3>
                            <div style="font-size:.75rem;color:#64748b;margin-top:1px;">Perkara: {{ $case->case_number }}</div>
                        </div>
                    </div>
                    <button type="button" onclick="closeDetailDokumenPerkaraModal()" style="background:none;border:none;cursor:pointer;font-size:1.3rem;color:#64748b;padding:4px 8px;border-radius:6px;">✕</button>
                </div>

                {{-- Modal Scrollable Body --}}
                <div style="padding:18px 22px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:14px;">
                    {{-- Status Banner: Ditolak --}}
                    <div id="mdd-banner-ditolak" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px;">
                        <div style="display:flex;align-items:center;gap:6px;font-weight:700;font-size:.85rem;color:#991b1b;margin-bottom:6px;">
                            <i data-lucide="x-circle" style="width:16px;height:16px;"></i>
                            <span>Status: Ditolak</span>
                        </div>
                        <div style="font-size:.8rem;color:#7f1d1d;line-height:1.5;">
                            <strong>Alasan Penolakan:</strong>
                            <div id="mdd-rejection-reason" style="margin-top:5px;background:#fff;padding:8px 12px;border-radius:6px;border:1px dashed #fca5a5;font-style:italic;color:#991b1b;"></div>
                        </div>
                        <div style="margin-top:8px;font-size:.72rem;color:#b91c1c;display:flex;align-items:center;gap:5px;">
                            <i data-lucide="info" style="width:13px;height:13px;flex-shrink:0;"></i>
                            <span>Klien telah menerima notifikasi untuk mengunggah ulang dokumen revisi. Riwayat versi lama tetap tersimpan.</span>
                        </div>
                    </div>

                    {{-- Status Banner: Menunggu Verifikasi --}}
                    <div id="mdd-banner-menunggu" style="display:none;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px;">
                        <div style="display:flex;align-items:center;gap:6px;font-weight:700;font-size:.85rem;color:#1e40af;margin-bottom:6px;">
                            <i data-lucide="clock" style="width:16px;height:16px;"></i>
                            <span>Status: Menunggu Verifikasi</span>
                        </div>
                        <div style="font-size:.8rem;color:#1e3a8a;line-height:1.5;">
                            Dokumen ini telah diunggah oleh klien dan memerlukan pemeriksaan serta verifikasi keabsahan oleh advokat.
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:12px;flex-wrap:wrap;">
                            <form id="mdd-verify-form" method="POST" action="" onsubmit="return confirm('Verifikasi dan nyatakan dokumen ini sah / diterima?');" style="margin:0;">
                                @csrf
                                <button type="submit" style="padding:6px 14px;background:#16a34a;color:#fff;border:none;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                                    <i data-lucide="check" style="width:13px;height:13px;"></i> Verifikasi & Terima
                                </button>
                            </form>
                            <button type="button" onclick="toggleMddInlineReject()" style="padding:6px 14px;background:#fff;color:#dc2626;border:1px solid #fca5a5;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                                <i data-lucide="x" style="width:13px;height:13px;"></i> Tolak Dokumen
                            </button>
                        </div>

                        {{-- Inline Reject Form --}}
                        <div id="mdd-inline-reject-box" style="display:none;margin-top:12px;background:#fff;border:1px solid #fecaca;border-radius:8px;padding:12px;">
                            <form id="mdd-reject-form" method="POST" action="">
                                @csrf
                                <label style="font-size:.75rem;font-weight:700;color:#991b1b;display:block;margin-bottom:4px;">Alasan Penolakan Dokumen * (Wajib)</label>
                                <textarea name="rejection_reason" required minlength="5" rows="2" placeholder="Tuliskan catatan perbaikan atau alasan penolakan untuk klien..."
                                          style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px;font-size:.8rem;box-sizing:border-box;resize:vertical;"></textarea>
                                <div style="display:flex;justify-content:flex-end;gap:6px;margin-top:8px;">
                                    <button type="button" onclick="toggleMddInlineReject()" style="padding:4px 10px;border:1px solid #cbd5e1;border-radius:5px;background:#fff;font-size:.75rem;cursor:pointer;">Batal</button>
                                    <button type="submit" style="padding:4px 12px;background:#dc2626;color:#fff;border:none;border-radius:5px;font-size:.75rem;font-weight:600;cursor:pointer;">Kirim Penolakan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Status Banner: Diterima --}}
                    <div id="mdd-banner-diterima" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;">
                        <div style="display:flex;align-items:center;gap:6px;font-weight:700;font-size:.85rem;color:#15803d;margin-bottom:4px;">
                            <i data-lucide="check-circle" style="width:16px;height:16px;"></i>
                            <span>Status: Diterima & Terverifikasi</span>
                        </div>
                        <div style="font-size:.8rem;color:#166534;">
                            Dokumen ini telah diverifikasi dan dinyatakan sah untuk penanganan perkara.
                        </div>
                    </div>

                    {{-- Status Banner: Belum Diunggah --}}
                    <div id="mdd-banner-belum" style="display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px;">
                        <div style="display:flex;align-items:center;gap:6px;font-weight:700;font-size:.85rem;color:#b45309;margin-bottom:4px;">
                            <i data-lucide="clock" style="width:16px;height:16px;"></i>
                            <span>Status: Menunggu Upload dari Klien</span>
                        </div>
                        <div style="font-size:.8rem;color:#92400e;line-height:1.5;">
                            Dokumen ini telah diminta kepada klien tetapi belum diunggah.
                        </div>
                        <div style="margin-top:8px;padding-top:8px;border-top:1px dashed #fcd34d;font-size:.75rem;color:#78350f;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                            <div><strong>Batas Waktu:</strong> <span id="mdd-req-due"></span></div>
                            <div><strong>Prioritas:</strong> <span id="mdd-req-priority"></span></div>
                        </div>
                    </div>

                    {{-- Metadata Grid --}}
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <div style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Nama Dokumen</div>
                                <div id="mdd-doc-name" style="font-size:.85rem;font-weight:600;color:#1e293b;margin-top:2px;"></div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Jenis Dokumen</div>
                                <div id="mdd-doc-category" style="font-size:.85rem;font-weight:500;color:#1e293b;margin-top:2px;"></div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Nama Berkas</div>
                                <div id="mdd-file-name" style="font-size:.8rem;font-family:monospace;color:#334155;margin-top:2px;word-break:break-all;"></div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Pengunggah</div>
                                <div id="mdd-uploader" style="font-size:.85rem;font-weight:500;color:#1e293b;margin-top:2px;"></div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Tanggal</div>
                                <div id="mdd-date" style="font-size:.825rem;color:#334155;margin-top:2px;"></div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Ukuran & Versi</div>
                                <div style="font-size:.825rem;color:#334155;margin-top:2px;">
                                    <span id="mdd-size"></span> • <span id="mdd-version" style="font-weight:600;color:#2563eb;"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Deskripsi / Keterangan --}}
                        <div id="mdd-desc-container" style="margin-top:12px;padding-top:10px;border-top:1px solid #e2e8f0;">
                            <div style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Keterangan / Catatan</div>
                            <div id="mdd-desc" style="font-size:.8rem;color:#334155;margin-top:3px;line-height:1.5;"></div>
                        </div>
                    </div>

                    {{-- Inline Preview Area --}}
                    <div id="mdd-preview-area" style="display:none;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#f8fafc;">
                        <div style="padding:8px 12px;background:#f1f5f9;font-size:.75rem;font-weight:600;color:#475569;display:flex;align-items:center;gap:6px;">
                            <i data-lucide="eye" style="width:14px;height:14px;"></i> Pratinjau Dokumen
                        </div>
                        <div id="mdd-preview-content" style="padding:10px;text-align:center;">
                            {{-- Dynamically injected image, iframe, or placeholder --}}
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div style="padding:14px 22px;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;background:#f8fafc;">
                    <button type="button" onclick="closeDetailDokumenPerkaraModal()" style="padding:8px 16px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;font-size:.825rem;font-weight:500;cursor:pointer;color:#475569;">
                        Tutup
                    </button>
                    <div id="mdd-file-actions" style="display:flex;gap:8px;">
                        <a id="mdd-view-btn" href="#" target="_blank"
                           style="padding:8px 16px;background:#fff;color:#1e3a5f;border:1px solid #1e3a5f;border-radius:8px;font-size:.825rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                            <i data-lucide="external-link" style="width:14px;height:14px;"></i> Lihat Dokumen
                        </a>
                        <a id="mdd-download-btn" href="#"
                           style="padding:8px 16px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:.825rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                            <i data-lucide="download" style="width:14px;height:14px;"></i> Unduh
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal: Minta Dokumen dari Klien --}}
        <div id="modal-minta-dokumen" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:12px;padding:24px 28px;width:100%;max-width:480px;box-shadow:0 10px 25px rgba(0,0,0,.15);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">
                    <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0;">Minta Dokumen dari Klien</h3>
                    <button type="button" onclick="closeAdvokatMintaModal()" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:#64748b;">✕</button>
                </div>
                <form method="POST" action="{{ route('advokat.cases.document', $case->id) }}">
                    @csrf
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Judul Dokumen yang Diminta *</label>
                        <input type="text" name="title" required placeholder="Contoh: Bukti Transfer Pembayaran / Sertifikat Asli"
                               style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Jenis Dokumen</label>
                        <select name="document_type" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
                            <option value="KTP">KTP</option>
                            <option value="KK">KK</option>
                            <option value="Surat Kuasa">Surat Kuasa</option>
                            <option value="Bukti Transaksi">Bukti Transaksi</option>
                            <option value="Kontrak/Perjanjian">Kontrak / Perjanjian</option>
                            <option value="Surat/Dokumen Resmi">Surat / Dokumen Resmi</option>
                            <option value="Bukti Pendukung" selected>Bukti Pendukung</option>
                            <option value="Dokumen Lainnya">Dokumen Lainnya</option>
                        </select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Batas Waktu (Opsional)</label>
                        <input type="date" name="due_date" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Prioritas</label>
                        <select name="priority" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
                            <option value="Normal">Normal</option>
                            <option value="Tinggi">Tinggi</option>
                        </select>
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Instruksi untuk Klien</label>
                        <textarea name="description" rows="2" placeholder="Jelaskan secara spesifik berkas apa yang harus disiapkan oleh klien..."
                                  style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:8px;">
                        <button type="button" onclick="closeAdvokatMintaModal()" style="padding:7px 14px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:.825rem;cursor:pointer;">Batal</button>
                        <button type="submit" style="padding:7px 16px;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:.825rem;font-weight:600;cursor:pointer;">Kirim Permintaan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Tolak Dokumen (Alasan Penolakan Wajib) --}}
        <div id="modal-tolak-dokumen" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:12px;padding:24px 28px;width:100%;max-width:480px;box-shadow:0 10px 25px rgba(0,0,0,.15);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">
                    <h3 style="font-size:1rem;font-weight:700;color:#991b1b;margin:0;">Tolak Dokumen Klien</h3>
                    <button type="button" onclick="closeTolakModal()" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:#64748b;">✕</button>
                </div>
                <form id="form-tolak-dokumen" method="POST" action="">
                    @csrf
                    <p style="font-size:.825rem;color:#475569;margin-bottom:12px;">
                        Anda akan menolak dokumen: <strong id="tolakDocTitle" style="color:#1e293b;"></strong>. Klien akan menerima notifikasi beserta alasan penolakan untuk melakukan unggah ulang.
                    </p>
                    <div style="margin-bottom:16px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Alasan Penolakan <span style="color:#dc2626;">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason_input" required minlength="5" rows="3"
                                  placeholder="Contoh: Dokumen kurang jelas atau buram, mohon unggah ulang foto/scan asli berwarna dengan resolusi yang lebih baik."
                                  style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:8px;">
                        <button type="button" onclick="closeTolakModal()" style="padding:7px 14px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:.825rem;cursor:pointer;">Batal</button>
                        <button type="submit" style="padding:7px 16px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:.825rem;font-weight:600;cursor:pointer;">Tolak Dokumen</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Unggah Dokumen Tambahan oleh Advokat --}}
        <div id="modal-advokat-upload" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:12px;padding:24px 28px;width:100%;max-width:480px;box-shadow:0 10px 25px rgba(0,0,0,.15);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">
                    <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0;">Unggah Dokumen Perkara (Advokat)</h3>
                    <button type="button" onclick="closeAdvokatUploadModal()" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:#64748b;">✕</button>
                </div>
                <form method="POST" action="{{ route('advokat.cases.document.upload', $case->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div style="background:#dbeafe;color:#1e40af;padding:8px 12px;border-radius:6px;font-size:.75rem;margin-bottom:12px;font-weight:500;">
                        Dokumen yang diunggah akan otomatis terverifikasi dan ditandai sebagai <strong>Dokumen dari Advokat</strong>.
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Nama Dokumen *</label>
                        <input type="text" name="name" required placeholder="Contoh: Draf Gugatan Resmi / Surat Somasi"
                               style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Jenis Dokumen *</label>
                        <select name="document_type" required style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
                            <option value="Surat/Dokumen Resmi">Surat / Dokumen Resmi Perkara</option>
                            <option value="Surat Kuasa">Surat Kuasa Khusus</option>
                            <option value="Kontrak/Perjanjian">Kontrak / Perjanjian</option>
                            <option value="Bukti Pendukung">Bukti Pendukung</option>
                            <option value="Dokumen Lainnya">Dokumen Lainnya</option>
                        </select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Pilih File *</label>
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               style="width:100%;border:1px dashed #94a3b8;border-radius:6px;padding:8px;font-size:.8rem;background:#f8fafc;box-sizing:border-box;">
                        <div style="font-size:.7rem;color:#64748b;margin-top:3px;">Format: PDF, JPG, PNG, DOC, DOCX (Maks 10 MB).</div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Keterangan</label>
                        <textarea name="description" rows="2" placeholder="Catatan berkas (opsional)..."
                                  style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:8px;">
                        <button type="button" onclick="closeAdvokatUploadModal()" style="padding:7px 14px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:.825rem;cursor:pointer;">Batal</button>
                        <button type="submit" style="padding:7px 16px;background:#1e3a5f;color:#fff;border:none;border-radius:6px;font-size:.825rem;font-weight:600;cursor:pointer;">Unggah Dokumen</button>
                    </div>
                </form>
            </div>
        </div>

        @else
        <div class="p-detail-empty">
            <i data-lucide="folder-open" style="width:48px;height:48px;"></i>
            <p>Pilih perkara untuk melihat detail.</p>
        </div>
        @endif
    </div>
</div>

{{-- Modal: Buat Perkara Baru --}}
<div id="modal-buat-perkara" class="hidden" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:flex;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:28px;width:520px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;">Buat Perkara Baru</h3>
            <button onclick="document.getElementById('modal-buat-perkara').classList.add('hidden')" style="background:none;border:none;cursor:pointer;color:#64748b;">✕</button>
        </div>
        <form method="POST" action="{{ route('advokat.cases.store') }}">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Konsultasi Terkait *</label>
                <select name="consultation_id" required style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
                    <option value="">-- Pilih Konsultasi --</option>
                    @foreach(\App\Models\Consultation::where('lawyer_id', Auth::id())->where('status','Selesai')->whereDoesntHave('case')->with('client')->get() as $k)
                    <option value="{{ $k->id }}">{{ $k->client->name }} — {{ $k->title }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Nomor Perkara *</label>
                <input type="text" name="case_number" required placeholder="Mis: 001/Pdt.G/2026/PN.Sbg" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Jenis Perkara *</label>
                <select name="case_type" required style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
                    <option value="Perdata — Gugatan">Perdata — Gugatan</option>
                    <option value="Perdata — Permohonan">Perdata — Permohonan</option>
                    <option value="Pidana Biasa">Pidana Biasa</option>
                    <option value="Pidana Khusus">Pidana Khusus</option>
                </select>
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Judul Perkara *</label>
                <input type="text" name="title" required placeholder="Ringkasan judul perkara" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Ringkasan</label>
                <textarea name="summary" rows="3" placeholder="Ringkasan singkat permasalahan..." style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;"></textarea>
            </div>
            <div style="margin-bottom:20px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Tanggal Mulai *</label>
                <input type="date" name="started_at" required value="{{ date('Y-m-d') }}" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:.85rem;box-sizing:border-box;">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-buat-perkara').classList.add('hidden')" style="padding:8px 18px;border:1px solid #e2e8f0;border-radius:8px;font-size:.85rem;cursor:pointer;background:#fff;">Batal</button>
                <button type="submit" style="padding:8px 18px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:.85rem;font-weight:600;cursor:pointer;">Buat Perkara</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('button[onclick], a[onclick]').forEach(el => {
        el.addEventListener('click', () => setTimeout(() => lucide.createIcons(), 50));
    });
    // Modal show fix
    document.getElementById('modal-buat-perkara').style.display = 'none';
    document.querySelectorAll('[onclick*="modal-buat-perkara"]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = document.getElementById('modal-buat-perkara');
            modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
        });
    });

    // Document Modal Functions
    function openDetailDokumenPerkaraModal(btn) {
        const ds = btn.dataset;
        const modal = document.getElementById('modal-detail-dokumen-perkara');
        if (!modal) return;

        // Reset banners and forms
        document.getElementById('mdd-banner-ditolak').style.display = 'none';
        document.getElementById('mdd-banner-menunggu').style.display = 'none';
        document.getElementById('mdd-banner-diterima').style.display = 'none';
        document.getElementById('mdd-banner-belum').style.display = 'none';
        document.getElementById('mdd-inline-reject-box').style.display = 'none';

        // Populate metadata
        document.getElementById('mdd-doc-name').textContent = ds.name || '—';
        document.getElementById('mdd-doc-category').textContent = ds.category || '—';
        document.getElementById('mdd-file-name').textContent = ds.fileName || '—';
        document.getElementById('mdd-uploader').textContent = ds.uploader || '—';
        document.getElementById('mdd-date').textContent = ds.date || '—';
        document.getElementById('mdd-size').textContent = ds.size || '—';
        document.getElementById('mdd-version').textContent = ds.version || '—';

        const descContainer = document.getElementById('mdd-desc-container');
        const descEl = document.getElementById('mdd-desc');
        if (ds.description && ds.description.trim()) {
            descEl.textContent = ds.description;
            descContainer.style.display = 'block';
        } else {
            descContainer.style.display = 'none';
        }

        // Status Banner Logic
        const statusType = ds.statusType; // 'diterima', 'menunggu', 'ditolak', 'belum'
        if (statusType === 'ditolak') {
            document.getElementById('mdd-banner-ditolak').style.display = 'block';
            document.getElementById('mdd-rejection-reason').textContent = ds.rejectionReason || 'Alasan penolakan tidak dicantumkan.';
        } else if (statusType === 'menunggu') {
            document.getElementById('mdd-banner-menunggu').style.display = 'block';
            document.getElementById('mdd-verify-form').action = ds.verifyUrl || '';
            document.getElementById('mdd-reject-form').action = ds.rejectUrl || '';
        } else if (statusType === 'diterima') {
            document.getElementById('mdd-banner-diterima').style.display = 'block';
        } else if (statusType === 'belum') {
            document.getElementById('mdd-banner-belum').style.display = 'block';
            document.getElementById('mdd-req-due').textContent = ds.dueDate || '—';
            document.getElementById('mdd-req-priority').textContent = ds.priority || 'Normal';
        }

        // File Preview & Actions
        const previewArea = document.getElementById('mdd-preview-area');
        const previewContent = document.getElementById('mdd-preview-content');
        const fileActions = document.getElementById('mdd-file-actions');
        const viewBtn = document.getElementById('mdd-view-btn');
        const downloadBtn = document.getElementById('mdd-download-btn');

        if (ds.hasFile === '1' && ds.viewUrl) {
            fileActions.style.display = 'flex';
            viewBtn.href = ds.viewUrl;
            downloadBtn.href = ds.downloadUrl;

            previewArea.style.display = 'block';
            const ext = (ds.extension || '').toLowerCase();
            if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                previewContent.innerHTML = `<img src="${ds.viewUrl}" alt="Preview" style="max-width:100%;max-height:260px;object-fit:contain;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.1);">`;
            } else if (ext === 'pdf') {
                previewContent.innerHTML = `<iframe src="${ds.viewUrl}#toolbar=0" style="width:100%;height:280px;border:1px solid #e2e8f0;border-radius:6px;" loading="lazy"></iframe>`;
            } else {
                previewContent.innerHTML = `<div style="padding:14px;color:#475569;font-size:.825rem;"><div style="font-weight:600;margin-bottom:3px;color:#1e3a5f;">Berkas Dokumen (${ext ? ext.toUpperCase() : 'Dokumen'})</div>Format ini dapat ditinjau secara langsung dengan membuka di tab baru atau mengunduh berkas.</div>`;
            }
        } else {
            fileActions.style.display = 'none';
            previewArea.style.display = 'none';
            previewContent.innerHTML = '';
        }

        modal.style.display = 'flex';
        setTimeout(() => lucide.createIcons(), 50);
    }

    function closeDetailDokumenPerkaraModal() {
        const modal = document.getElementById('modal-detail-dokumen-perkara');
        if (modal) {
            modal.style.display = 'none';
            const previewContent = document.getElementById('mdd-preview-content');
            if (previewContent) previewContent.innerHTML = '';
        }
    }

    function toggleMddInlineReject() {
        const box = document.getElementById('mdd-inline-reject-box');
        if (box) {
            box.style.display = box.style.display === 'none' ? 'block' : 'none';
            setTimeout(() => lucide.createIcons(), 50);
        }
    }

    function openAdvokatMintaModal() {
        const modal = document.getElementById('modal-minta-dokumen');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => lucide.createIcons(), 50);
        }
    }
    function closeAdvokatMintaModal() {
        const modal = document.getElementById('modal-minta-dokumen');
        if (modal) modal.style.display = 'none';
    }

    function openAdvokatUploadModal() {
        const modal = document.getElementById('modal-advokat-upload');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => lucide.createIcons(), 50);
        }
    }
    function closeAdvokatUploadModal() {
        const modal = document.getElementById('modal-advokat-upload');
        if (modal) modal.style.display = 'none';
    }

    function openTolakModal(docId, docTitle) {
        document.getElementById('tolakDocTitle').innerText = docTitle;
        document.getElementById('rejection_reason_input').value = '';
        document.getElementById('form-tolak-dokumen').action = '{{ url("/advokat/dokumen") }}/' + docId + '/tolak';
        const modal = document.getElementById('modal-tolak-dokumen');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => lucide.createIcons(), 50);
        }
    }
    function closeTolakModal() {
        const modal = document.getElementById('modal-tolak-dokumen');
        if (modal) modal.style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        if (e.target.id === 'modal-detail-dokumen-perkara') closeDetailDokumenPerkaraModal();
        if (e.target.id === 'modal-minta-dokumen') closeAdvokatMintaModal();
        if (e.target.id === 'modal-advokat-upload') closeAdvokatUploadModal();
        if (e.target.id === 'modal-tolak-dokumen') closeTolakModal();
    });
</script>
@endpush
@endsection
