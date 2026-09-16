@extends('layouts.admin')

@section('title', 'Manajemen Dokumen Perkara')

@section('content')
<div style="display:flex;flex-direction:column;gap:18px;">

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;">
        <div class="card" style="padding:16px;display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;">
                <i data-lucide="file-text" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--color-gray-text);font-weight:600;text-transform:uppercase;">Total Dokumen</div>
                <div style="font-size:1.35rem;font-weight:700;color:var(--color-gray-dark);">{{ $stats['total'] ?? $documents->count() }}</div>
            </div>
        </div>

        <div class="card" style="padding:16px;display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:10px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;color:#10b981;">
                <i data-lucide="check-circle" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--color-gray-text);font-weight:600;text-transform:uppercase;">Terverifikasi</div>
                <div style="font-size:1.35rem;font-weight:700;color:#10b981;">{{ $stats['verified'] ?? 0 }}</div>
            </div>
        </div>

        <div class="card" style="padding:16px;display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:10px;background:#fffbeb;display:flex;align-items:center;justify-content:center;color:#f59e0b;">
                <i data-lucide="clock" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--color-gray-text);font-weight:600;text-transform:uppercase;">Menunggu Verifikasi</div>
                <div style="font-size:1.35rem;font-weight:700;color:#f59e0b;">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>

        <div class="card" style="padding:16px;display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;color:#ef4444;">
                <i data-lucide="x-circle" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--color-gray-text);font-weight:600;text-transform:uppercase;">Ditolak (Revisi)</div>
                <div style="font-size:1.35rem;font-weight:700;color:#ef4444;">{{ $stats['rejected'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Main Document Card --}}
    <div class="card">
        {{-- Toolbar Filter & Search --}}
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.documents', ['status' => 'all', 'case_id' => $selectedCaseId]) }}"
                   class="btn btn-outline"
                   style="padding:5px 12px;font-size:.78rem;border-radius:20px;text-decoration:none;{{ $statusFilter === 'all' || !$statusFilter ? 'background:var(--color-navy);color:#fff;border-color:var(--color-navy);' : '' }}">
                    Semua ({{ $stats['total'] ?? $documents->count() }})
                </a>
                <a href="{{ route('admin.documents', ['status' => 'Terverifikasi', 'case_id' => $selectedCaseId]) }}"
                   class="btn btn-outline"
                   style="padding:5px 12px;font-size:.78rem;border-radius:20px;text-decoration:none;{{ $statusFilter === 'Terverifikasi' ? 'background:#10b981;color:#fff;border-color:#10b981;' : '' }}">
                    Terverifikasi ({{ $stats['verified'] ?? 0 }})
                </a>
                <a href="{{ route('admin.documents', ['status' => 'Menunggu', 'case_id' => $selectedCaseId]) }}"
                   class="btn btn-outline"
                   style="padding:5px 12px;font-size:.78rem;border-radius:20px;text-decoration:none;{{ $statusFilter === 'Menunggu' ? 'background:#f59e0b;color:#fff;border-color:#f59e0b;' : '' }}">
                    Menunggu ({{ $stats['pending'] ?? 0 }})
                </a>
                <a href="{{ route('admin.documents', ['status' => 'Ditolak', 'case_id' => $selectedCaseId]) }}"
                   class="btn btn-outline"
                   style="padding:5px 12px;font-size:.78rem;border-radius:20px;text-decoration:none;{{ $statusFilter === 'Ditolak' ? 'background:#ef4444;color:#fff;border-color:#ef4444;' : '' }}">
                    Ditolak ({{ $stats['rejected'] ?? 0 }})
                </a>
            </div>

            <form method="GET" action="{{ route('admin.documents') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                @if(isset($cases) && $cases->count() > 0)
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

                <div class="search-box" style="display:flex;align-items:center;background:#f8fafc;border:1px solid #cbd5e1;border-radius:6px;padding:4px 10px;width:240px;">
                    <i data-lucide="search" style="color: var(--color-gray-text); width: 14px; margin-right:6px;"></i>
                    <input type="text" id="adminDocSearch" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, klien, perkara..." style="border:none;background:transparent;outline:none;font-size:.8rem;width:100%;">
                </div>

                @if($search || $selectedCaseId)
                <a href="{{ route('admin.documents') }}" style="font-size:.75rem;color:var(--color-gray-text);text-decoration:none;padding:5px;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-container">
            <table id="adminDocsTable">
                <thead>
                    <tr>
                        <th>Nama Dokumen</th>
                        <th>Jenis</th>
                        <th>Klien</th>
                        <th>Perkara</th>
                        <th>Advokat</th>
                        <th>Ukuran</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    @php
                        $isVerified = ($doc->status === 'Terverifikasi' || $doc->status === 'Sudah Diterima');
                        $isRejected = in_array($doc->status, ['Ditolak', 'Perlu Diperbaiki']);
                        
                        $badgeClass = match(true) {
                            $isVerified => 'badge-success',
                            $isRejected => 'badge-danger',
                            default     => 'badge-warning',
                        };
                        $statusLabel = match(true) {
                            $isVerified => 'Terverifikasi',
                            $isRejected => 'Ditolak',
                            default     => 'Menunggu Verifikasi',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">{{ $doc->name }}</div>
                            <div style="font-size: 0.72rem; color: #64748b;">
                                @if($doc->is_from_lawyer)
                                <span style="color:#2563eb;font-weight:600;">• Dokumen Advokat</span>
                                @endif
                                @if($doc->version > 1)
                                <span style="color:#4f46e5;font-weight:600;">• Revisi v{{ $doc->version }}</span>
                                @endif
                                @if(!$doc->file_path)
                                <span style="color:#94a3b8;">• Belum ada berkas fisik</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span style="font-size:.75rem;background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;">
                                {{ $doc->document_type ?? 'Dokumen Lainnya' }}
                            </span>
                        </td>
                        <td>{{ $doc->client?->name ?? '—' }}</td>
                        <td>
                            @if($doc->case)
                            <span style="font-weight:500;color:#1e3a5f;">{{ $doc->case->case_number }}</span>
                            @else
                            <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td>{{ $doc->lawyer?->name ?? '—' }}</td>
                        <td style="font-size:.75rem;color:#64748b;">{{ $doc->formatted_file_size }}</td>
                        <td>
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="text-right">
                            <div style="display:inline-flex;gap:4px;justify-content:flex-end;align-items:center;">
                                {{-- Tombol Detail: Selalu bisa diklik oleh Admin --}}
                                <button type="button" class="btn btn-outline" style="padding: 0.25rem 0.5rem;font-size:.75rem;"
                                        data-doc-id="{{ $doc->id }}"
                                        data-name="{{ $doc->name }}"
                                        data-type="{{ $doc->document_type ?? 'Dokumen' }}"
                                        data-case="{{ $doc->case ? ($doc->case->case_number . ' — ' . $doc->case->title) : '—' }}"
                                        data-client="{{ $doc->client?->name ?? '—' }}"
                                        data-lawyer="{{ $doc->lawyer?->name ?? '—' }}"
                                        data-uploader="{{ $doc->is_from_lawyer ? 'Dokumen Resmi Advokat' : ($doc->uploader?->name ?? 'Klien') }}"
                                        data-size="{{ $doc->formatted_file_size }}"
                                        data-status="{{ $statusLabel }}"
                                        data-version="{{ $doc->version ?? 1 }}"
                                        data-date="{{ $doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') : '—' }}"
                                        data-desc="{{ $doc->description ?? 'Tidak ada keterangan tambahan.' }}"
                                        data-reason="{{ $doc->rejection_reason ?? '' }}"
                                        data-has-file="{{ $doc->file_path ? '1' : '0' }}"
                                        data-view-url="{{ $doc->file_path ? route('documents.view', $doc->id) : '' }}"
                                        data-download-url="{{ $doc->file_path ? route('documents.download', $doc->id) : '' }}"
                                        onclick="openAdminDocModal(this)" title="Lihat Detail Informasi">
                                    <i data-lucide="info" style="width: 14px;"></i> Detail
                                </button>

                                @if($doc->file_path)
                                <a href="{{ route('documents.view', $doc->id) }}" target="_blank" class="btn btn-outline" style="padding: 0.25rem 0.5rem;font-size:.75rem;text-decoration:none;" title="Pratinjau File">
                                    <i data-lucide="eye" style="width: 14px;"></i>
                                </a>
                                <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.5rem;font-size:.75rem;text-decoration:none;" title="Unduh File">
                                    <i data-lucide="download" style="width: 14px;"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:2.5rem;color:#94a3b8;">
                            Belum ada dokumen yang terdaftar sesuai kriteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Detail Dokumen Admin --}}
<div id="modalAdminDocDetail" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:520px;box-shadow:0 10px 25px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0;">Informasi Detail Dokumen</h3>
            <button type="button" onclick="closeAdminDocDetail()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;font-size:.85rem;">
            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Nama Dokumen</span>
                <strong id="mDocName" style="color:#1e293b;font-size:1rem;"></strong>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Jenis Dokumen</span>
                    <span id="mDocType" style="color:#334155;font-weight:500;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Status Verifikasi</span>
                    <span id="mDocStatus" style="font-weight:600;"></span>
                </div>
            </div>

            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Perkara Terkait</span>
                <span id="mDocCase" style="color:#1e293b;font-weight:500;"></span>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Klien</span>
                    <span id="mDocClient" style="color:#334155;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Advokat</span>
                    <span id="mDocLawyer" style="color:#334155;"></span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Pengunggah</span>
                    <span id="mDocUploader" style="color:#334155;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Ukuran Berkas</span>
                    <span id="mDocSize" style="color:#334155;"></span>
                </div>
            </div>

            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Waktu Unggah</span>
                <span id="mDocDate" style="color:#334155;"></span>
            </div>

            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Keterangan</span>
                <p id="mDocDesc" style="margin:2px 0 0 0;color:#475569;background:#f8fafc;padding:8px 10px;border-radius:6px;border:1px solid #f1f5f9;"></p>
            </div>

            <div id="mDocRejectionBox" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:8px 10px;">
                <span style="color:#991b1b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:700;">Catatan Penolakan dari Advokat</span>
                <p id="mDocReason" style="margin:2px 0 0 0;color:#b91c1c;"></p>
            </div>

            <div id="mDocFileNotice" style="display:none;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:6px;padding:10px;text-align:center;color:#64748b;font-size:.8rem;">
                <i data-lucide="alert-circle" style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:4px;"></i>
                Dokumen ini merupakan data awal perkara. Berkas fisik belum diunggah.
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;margin-top:16px;">
            <div id="mDocActionButtons" style="display:flex;gap:6px;"></div>
            <button type="button" onclick="closeAdminDocDetail()" class="btn btn-outline" style="font-size:.8rem;">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function openAdminDocModal(btn) {
    const d = btn.dataset;
    document.getElementById('mDocName').textContent = d.name || '—';
    document.getElementById('mDocType').textContent = d.type || '—';
    document.getElementById('mDocStatus').textContent = d.status || '—';
    document.getElementById('mDocCase').textContent = d.case || '—';
    document.getElementById('mDocClient').textContent = d.client || '—';
    document.getElementById('mDocLawyer').textContent = d.lawyer || '—';
    document.getElementById('mDocUploader').textContent = d.uploader || '—';
    document.getElementById('mDocSize').textContent = d.size || '—';
    document.getElementById('mDocDate').textContent = d.date || '—';
    document.getElementById('mDocDesc').textContent = d.desc || 'Tidak ada keterangan tambahan.';

    const rejBox = document.getElementById('mDocRejectionBox');
    if (d.reason && d.reason.trim() !== '') {
        rejBox.style.display = 'block';
        document.getElementById('mDocReason').textContent = d.reason;
    } else {
        rejBox.style.display = 'none';
    }

    const fileNotice = document.getElementById('mDocFileNotice');
    const actBox = document.getElementById('mDocActionButtons');
    actBox.innerHTML = '';

    if (d.hasFile === '1' && d.viewUrl) {
        fileNotice.style.display = 'none';
        actBox.innerHTML = `
            <a href="${d.viewUrl}" target="_blank" class="btn btn-primary" style="font-size:.8rem;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <i data-lucide="eye" style="width:14px;"></i> Lihat
            </a>
            <a href="${d.downloadUrl}" class="btn btn-primary" style="font-size:.8rem;background:#10b981;border-color:#10b981;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <i data-lucide="download" style="width:14px;"></i> Unduh
            </a>
        `;
    } else {
        fileNotice.style.display = 'block';
    }

    document.getElementById('modalAdminDocDetail').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function openAdminDocDetailById(id) {
    const btn = document.querySelector(`button[data-doc-id="${id}"]`);
    if (btn) {
        openAdminDocModal(btn);
    }
}

function openAdminDocDetail(data) {
    document.getElementById('mDocName').textContent = data.name || '—';
    document.getElementById('mDocType').textContent = data.type || '—';
    document.getElementById('mDocStatus').textContent = data.status || '—';
    document.getElementById('mDocCase').textContent = data.case || '—';
    document.getElementById('mDocClient').textContent = data.client || '—';
    document.getElementById('mDocLawyer').textContent = data.lawyer || '—';
    document.getElementById('mDocUploader').textContent = data.uploader || '—';
    document.getElementById('mDocSize').textContent = data.size || '—';
    document.getElementById('mDocDate').textContent = data.date || '—';
    document.getElementById('mDocDesc').textContent = data.desc || 'Tidak ada keterangan tambahan.';

    const rejBox = document.getElementById('mDocRejectionBox');
    if (data.rejection_reason && data.rejection_reason.trim() !== '') {
        rejBox.style.display = 'block';
        document.getElementById('mDocReason').textContent = data.rejection_reason;
    } else {
        rejBox.style.display = 'none';
    }

    const fileNotice = document.getElementById('mDocFileNotice');
    const actBox = document.getElementById('mDocActionButtons');
    actBox.innerHTML = '';

    if (data.has_file && data.view_url) {
        fileNotice.style.display = 'none';
        actBox.innerHTML = `
            <a href="${data.view_url}" target="_blank" class="btn btn-primary" style="font-size:.8rem;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <i data-lucide="eye" style="width:14px;"></i> Lihat
            </a>
            <a href="${data.download_url}" class="btn btn-primary" style="font-size:.8rem;background:#10b981;border-color:#10b981;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <i data-lucide="download" style="width:14px;"></i> Unduh
            </a>
        `;
    } else {
        fileNotice.style.display = 'block';
    }

    document.getElementById('modalAdminDocDetail').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeAdminDocDetail() {
    document.getElementById('modalAdminDocDetail').style.display = 'none';
}

// Live filter table on typing
document.getElementById('adminDocSearch')?.addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    const rows = document.querySelectorAll('#adminDocsTable tbody tr');
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
    });
});

window.addEventListener('click', function(e) {
    if (e.target.id === 'modalAdminDocDetail') closeAdminDocDetail();
});
</script>
@endsection
