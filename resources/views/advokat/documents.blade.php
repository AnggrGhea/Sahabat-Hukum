@extends('layouts.advokat')

@section('title', 'Manajemen Dokumen Perkara')

@section('content')
<div style="display:flex;flex-direction:column;gap:18px;">

    {{-- Header Page --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0 0 4px 0;">Pemeriksaan & Manajemen Dokumen</h1>
            <p style="font-size:.825rem;color:#64748b;margin:0;">Verifikasi berkas klien, tolak dokumen tidak valid dengan alasan resmi, atau minta dokumen perkara baru.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <button type="button" onclick="openModalMintaDokumen()"
                    style="padding:8px 14px;background:#fff;color:#1e3a5f;border:1px solid #cbd5e1;border-radius:8px;font-size:.825rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                <i data-lucide="file-plus" style="width:15px;height:15px;"></i> Minta Dokumen Klien
            </button>
            <button type="button" onclick="openModalUploadAdvokat()"
                    style="padding:8px 16px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:.825rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 4px rgba(30,58,95,.2);">
                <i data-lucide="upload" style="width:15px;height:15px;"></i> Unggah Dokumen Advokat
            </button>
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

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(190px, 1fr));gap:12px;">
        <div class="card" style="padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;">
                <i data-lucide="file-text" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div style="font-size:.72rem;color:#64748b;font-weight:600;text-transform:uppercase;">Total Dokumen</div>
                <div style="font-size:1.25rem;font-weight:700;color:#0f172a;">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div class="card" style="padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;color:#10b981;">
                <i data-lucide="check-circle" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div style="font-size:.72rem;color:#64748b;font-weight:600;text-transform:uppercase;">Terverifikasi</div>
                <div style="font-size:1.25rem;font-weight:700;color:#10b981;">{{ $stats['verified'] }}</div>
            </div>
        </div>

        <div class="card" style="padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;color:#f59e0b;">
                <i data-lucide="clock" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div style="font-size:.72rem;color:#64748b;font-weight:600;text-transform:uppercase;">Menunggu Periksa</div>
                <div style="font-size:1.25rem;font-weight:700;color:#f59e0b;">{{ $stats['pending'] }}</div>
            </div>
        </div>

        <div class="card" style="padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#fef2f2;display:flex;align-items:center;justify-content:center;color:#ef4444;">
                <i data-lucide="x-circle" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div style="font-size:.72rem;color:#64748b;font-weight:600;text-transform:uppercase;">Ditolak</div>
                <div style="font-size:1.25rem;font-weight:700;color:#ef4444;">{{ $stats['rejected'] }}</div>
            </div>
        </div>

        <div class="card" style="padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;color:#8b5cf6;">
                <i data-lucide="send" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div style="font-size:.72rem;color:#64748b;font-weight:600;text-transform:uppercase;">Permintaan Aktif</div>
                <div style="font-size:1.25rem;font-weight:700;color:#8b5cf6;">{{ $stats['requests'] }}</div>
            </div>
        </div>
    </div>

    {{-- Main Document Card --}}
    <div class="card">
        {{-- Toolbar Filter & Search --}}
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('advokat.documents', ['status' => 'all', 'case_id' => $selectedCaseId]) }}"
                   class="btn btn-outline"
                   style="padding:5px 12px;font-size:.78rem;border-radius:20px;text-decoration:none;{{ $statusFilter === 'all' || !$statusFilter ? 'background:#1e3a5f;color:#fff;border-color:#1e3a5f;' : '' }}">
                    Semua ({{ $stats['total'] }})
                </a>
                <a href="{{ route('advokat.documents', ['status' => 'Menunggu', 'case_id' => $selectedCaseId]) }}"
                   class="btn btn-outline"
                   style="padding:5px 12px;font-size:.78rem;border-radius:20px;text-decoration:none;{{ $statusFilter === 'Menunggu' ? 'background:#f59e0b;color:#fff;border-color:#f59e0b;' : '' }}">
                    Menunggu Verifikasi ({{ $stats['pending'] }})
                </a>
                <a href="{{ route('advokat.documents', ['status' => 'Terverifikasi', 'case_id' => $selectedCaseId]) }}"
                   class="btn btn-outline"
                   style="padding:5px 12px;font-size:.78rem;border-radius:20px;text-decoration:none;{{ $statusFilter === 'Terverifikasi' ? 'background:#10b981;color:#fff;border-color:#10b981;' : '' }}">
                    Terverifikasi ({{ $stats['verified'] }})
                </a>
                <a href="{{ route('advokat.documents', ['status' => 'Ditolak', 'case_id' => $selectedCaseId]) }}"
                   class="btn btn-outline"
                   style="padding:5px 12px;font-size:.78rem;border-radius:20px;text-decoration:none;{{ $statusFilter === 'Ditolak' ? 'background:#ef4444;color:#fff;border-color:#ef4444;' : '' }}">
                    Ditolak ({{ $stats['rejected'] }})
                </a>
            </div>

            <form method="GET" action="{{ route('advokat.documents') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                @if($cases->count() > 1)
                <select name="case_id" onchange="this.form.submit()"
                        style="padding:6px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:.8rem;color:#334155;background:#fff;outline:none;">
                    <option value="">Semua Perkara</option>
                    @foreach($cases as $c)
                    <option value="{{ $c->id }}" {{ $selectedCaseId == $c->id ? 'selected' : '' }}>
                        {{ $c->case_number }} — {{ $c->client?->name }}
                    </option>
                    @endforeach
                </select>
                @endif

                <div class="search-box" style="display:flex;align-items:center;background:#f8fafc;border:1px solid #cbd5e1;border-radius:6px;padding:4px 10px;width:220px;">
                    <i data-lucide="search" style="color: #94a3b8; width: 14px; margin-right:6px;"></i>
                    <input type="text" id="advokatDocSearch" name="q" value="{{ $search }}" placeholder="Cari dokumen, klien..." style="border:none;background:transparent;outline:none;font-size:.8rem;width:100%;">
                </div>

                @if($search || $selectedCaseId)
                <a href="{{ route('advokat.documents') }}" style="font-size:.75rem;color:#64748b;text-decoration:none;padding:5px;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-container">
            <table id="advokatDocsTable">
                <thead>
                    <tr>
                        <th>Nama Dokumen</th>
                        <th>Jenis</th>
                        <th>Klien & Perkara</th>
                        <th>Ukuran</th>
                        <th>Pengunggah</th>
                        <th>Status</th>
                        <th class="text-right">Aksi & Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    @php
                        $isVerified = ($doc->status === 'Terverifikasi' || $doc->status === 'Sudah Diterima');
                        $isRejected = in_array($doc->status, ['Ditolak', 'Perlu Diperbaiki']);
                        $isPending  = in_array($doc->status, ['Menunggu Verifikasi', 'Menunggu Pemeriksaan', 'Belum Diunggah']);

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
                                <span style="color:#2563eb;font-weight:600;">• Dokumen dari Advokat</span>
                                @endif
                                @if($doc->version > 1)
                                <span style="color:#4f46e5;font-weight:600;">• Revisi Ke-{{ $doc->version }}</span>
                                @endif
                                @if(!$doc->file_path)
                                <span style="color:#94a3b8;">• Berkas fisik belum diunggah</span>
                                @endif
                            </div>

                            @if($isRejected && $doc->rejection_reason)
                            <div style="margin-top:6px;background:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:4px 8px;font-size:.72rem;color:#991b1b;">
                                <strong>Alasan Ditolak:</strong> {{ $doc->rejection_reason }}
                            </div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:.75rem;background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;">
                                {{ $doc->document_type ?? 'Dokumen' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:500;color:#1e293b;">{{ $doc->client?->name ?? '—' }}</div>
                            <div style="font-size:.72rem;color:#64748b;">
                                {{ $doc->case?->case_number ?? '—' }}
                            </div>
                        </td>
                        <td style="font-size:.75rem;color:#64748b;">{{ $doc->formatted_file_size }}</td>
                        <td>
                            @if($doc->is_from_lawyer)
                            <span style="font-size:.7rem;background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;font-weight:600;">Advokat</span>
                            @else
                            <span style="font-size:.75rem;color:#475569;">{{ $doc->uploader?->name ?? 'Klien' }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="text-right">
                            <div style="display:inline-flex;gap:4px;justify-content:flex-end;align-items:center;flex-wrap:wrap;">
                                {{-- Tombol Detail Informasi Lengkap --}}
                                <button type="button" class="btn btn-outline" style="padding: 0.25rem 0.5rem;font-size:.75rem;"
                                        data-doc-id="{{ $doc->id }}"
                                        data-name="{{ e($doc->name) }}"
                                        data-type="{{ e($doc->document_type ?? 'Dokumen') }}"
                                        data-case="{{ e($doc->case ? ($doc->case->case_number . ' — ' . $doc->case->title) : '—') }}"
                                        data-client="{{ e($doc->client?->name ?? '—') }}"
                                        data-uploader="{{ e($doc->is_from_lawyer ? 'Dokumen Resmi Advokat' : ($doc->uploader?->name ?? 'Klien')) }}"
                                        data-size="{{ $doc->formatted_file_size }}"
                                        data-status="{{ e($statusLabel) }}"
                                        data-version="v{{ $doc->version ?? 1 }}"
                                        data-date="{{ $doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') : '—' }}"
                                        data-desc="{{ e($doc->description ?? 'Tidak ada keterangan tambahan.') }}"
                                        data-reason="{{ e($doc->rejection_reason ?? '') }}"
                                        data-has-file="{{ $doc->file_path ? '1' : '0' }}"
                                        data-view-url="{{ $doc->file_path ? route('documents.view', $doc->id) : '' }}"
                                        data-download-url="{{ $doc->file_path ? route('documents.download', $doc->id) : '' }}"
                                        onclick="openAdvokatDocDetailModal(this)" title="Lihat Detail Informasi">
                                    <i data-lucide="info" style="width: 14px;"></i> Detail
                                </button>

                                @if($doc->file_path)
                                <a href="{{ route('documents.view', $doc->id) }}" target="_blank" class="btn btn-outline" style="padding: 0.25rem 0.5rem;font-size:.75rem;text-decoration:none;" title="Lihat Pratinjau">
                                    <i data-lucide="eye" style="width: 14px;"></i>
                                </a>
                                <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.5rem;font-size:.75rem;text-decoration:none;" title="Unduh Berkas">
                                    <i data-lucide="download" style="width: 14px;"></i>
                                </a>
                                @endif

                                {{-- Aksi Verifikasi jika status masih menunggu --}}
                                @if(!$isVerified)
                                <form method="POST" action="{{ route('advokat.documents.verify', $doc->id) }}" style="display:inline;" onsubmit="return confirm('Verifikasi dan terima dokumen ini?');">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" style="padding:0.25rem 0.6rem;font-size:.75rem;background:#10b981;border-color:#10b981;display:inline-flex;align-items:center;gap:3px;" title="Verifikasi Dokumen">
                                        <i data-lucide="check" style="width: 13px;"></i> Terima
                                    </button>
                                </form>
                                @endif

                                {{-- Aksi Tolak jika belum ditolak --}}
                                @if(!$isRejected)
                                <button type="button" class="btn btn-outline" style="padding:0.25rem 0.6rem;font-size:.75rem;color:#dc2626;border-color:#fecaca;display:inline-flex;align-items:center;gap:3px;"
                                        data-doc-name="{{ $doc->name }}"
                                        onclick="openModalTolak({{ $doc->id }}, this.getAttribute('data-doc-name'))" title="Tolak Dokumen dengan Alasan">
                                    <i data-lucide="x" style="width: 13px;"></i> Tolak
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:2.5rem;color:#94a3b8;">
                            Belum ada dokumen yang terdaftar sesuai kriteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: Minta Dokumen dari Klien --}}
<div id="modalMintaDokumen" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 10px 25px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0;">Minta Dokumen dari Klien</h3>
            <button type="button" onclick="closeModalMintaDokumen()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>

        <form id="formMintaDokumen" method="POST" action="">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Pilih Perkara *</label>
                <select id="mintaCaseSelect" onchange="updateMintaFormAction(this.value)" required
                        style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;background:#fff;">
                    @foreach($cases as $c)
                    <option value="{{ $c->id }}" {{ $selectedCaseId == $c->id ? 'selected' : '' }}>
                        {{ $c->case_number }} — {{ $c->client?->name }} ({{ $c->title }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Nama Dokumen yang Diminta *</label>
                <input type="text" name="name" required placeholder="Contoh: Salinan Surat Nikah, Bukti Chat WhatsApp..."
                       style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;box-sizing:border-box;">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Jenis Dokumen</label>
                    <select name="document_type" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;background:#fff;">
                        <option value="Identitas Diri (KTP/SIM/Paspor)">Identitas Diri</option>
                        <option value="Surat Kuasa">Surat Kuasa</option>
                        <option value="Alat Bukti Surat/Dokumen">Alat Bukti Surat / Dokumen</option>
                        <option value="Bukti Pembayaran/Transfer">Bukti Pembayaran / Transfer</option>
                        <option value="Dokumen Lainnya">Dokumen Lainnya</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Tingkat Prioritas</label>
                    <select name="priority" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;background:#fff;">
                        <option value="Tinggi">Tinggi</option>
                        <option value="Sedang" selected>Sedang</option>
                        <option value="Rendah">Rendah</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Catatan / Instruksi untuk Klien (Opsional)</label>
                <textarea name="description" rows="2" placeholder="Instruksi spesifik mengenai resolusi, tanda tangan, atau kejelasan berkas..."
                          style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;">
                <button type="button" onclick="closeModalMintaDokumen()" class="btn btn-outline" style="font-size:.8rem;">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-size:.8rem;">Kirim Permintaan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Tolak Dokumen (Alasan Penolakan Wajib) --}}
<div id="modalTolakDokumen" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 10px 25px rgba(0,0,0,.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.05rem;font-weight:700;color:#991b1b;margin:0;">Tolak Dokumen Klien</h3>
            <button type="button" onclick="closeModalTolak()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>

        <form id="formTolakDokumen" method="POST" action="">
            @csrf
            <div style="font-size:.825rem;color:#475569;margin-bottom:12px;">
                Anda akan menolak berkas: <strong id="tolakDocTitle" style="color:#1e293b;"></strong>. Klien akan menerima notifikasi beserta catatan penolakan ini untuk mengunggah berkas revisi.
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Alasan Penolakan * (Wajib, min. 5 karakter)</label>
                <textarea name="rejection_reason" required minlength="5" rows="3"
                          placeholder="Jelaskan alasan penolakan, misalnya: 'Foto dokumen buram, mohon unggah scan/foto asli berwarna dengan pencahayaan jelas'..."
                          style="width:100%;padding:8px 12px;border:1px solid #fca5a5;border-radius:6px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;">
                <button type="button" onclick="closeModalTolak()" class="btn btn-outline" style="font-size:.8rem;">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#dc2626;border-color:#dc2626;font-size:.8rem;">Tolak Dokumen</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: Unggah Dokumen Resmi Advokat --}}
<div id="modalUploadAdvokat" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:500px;box-shadow:0 10px 25px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0;">Unggah Dokumen Perkara (Advokat)</h3>
            <button type="button" onclick="closeModalUploadAdvokat()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>

        <form id="formUploadAdvokat" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:12px;font-size:.78rem;color:#2563eb;background:#eff6ff;padding:8px 12px;border-radius:6px;border:1px solid #bfdbfe;">
                Dokumen yang diunggah akan otomatis terverifikasi dan ditandai sebagai <strong>"Dokumen dari Advokat"</strong>.
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Pilih Perkara *</label>
                <select id="advokatUploadCaseSelect" onchange="updateAdvokatUploadAction(this.value)" required
                        style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;background:#fff;">
                    @foreach($cases as $c)
                    <option value="{{ $c->id }}" {{ $selectedCaseId == $c->id ? 'selected' : '' }}>
                        {{ $c->case_number }} — {{ $c->client?->name }} ({{ $c->title }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Nama Dokumen *</label>
                <input type="text" name="name" required placeholder="Contoh: Gugatan Resmi, Duplik, Eksepsi, Bukti Pendukung P-1..."
                       style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Jenis Dokumen *</label>
                <select name="document_type" required style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;background:#fff;">
                    <option value="Surat/Dokumen Resmi">Surat / Dokumen Resmi Perkara</option>
                    <option value="Gugatan/Permohonan">Gugatan / Permohonan</option>
                    <option value="Jawaban/Eksepsi/Duplik">Jawaban / Eksepsi / Duplik</option>
                    <option value="Daftar Alat Bukti">Daftar Alat Bukti</option>
                    <option value="Dokumen Lainnya">Dokumen Lainnya</option>
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Catatan Tambahan (Opsional)</label>
                <textarea name="description" rows="2" placeholder="Catatan atau keterangan mengenai dokumen..."
                          style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#334155;margin-bottom:4px;">Pilih Berkas * (Maks. 10 MB)</label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       style="width:100%;padding:8px;border:1px dashed #cbd5e1;border-radius:6px;font-size:.8rem;background:#f8fafc;box-sizing:border-box;">
                <div style="font-size:.72rem;color:#64748b;margin-top:4px;">Format: PDF, JPG, PNG, DOC, DOCX.</div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;">
                <button type="button" onclick="closeModalUploadAdvokat()" class="btn btn-outline" style="font-size:.8rem;">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-size:.8rem;">Unggah Dokumen</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: Detail Informasi Dokumen Lengkap (Advokat) --}}
<div id="modalAdvokatDocDetail" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:520px;box-shadow:0 10px 25px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0;">Informasi Detail Dokumen</h3>
            <button type="button" onclick="closeAdvokatDocDetail()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;font-size:.85rem;">
            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Nama Dokumen</span>
                <strong id="mAdvDocName" style="color:#1e293b;font-size:1rem;"></strong>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Jenis Dokumen</span>
                    <span id="mAdvDocType" style="color:#334155;font-weight:500;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Status Verifikasi</span>
                    <span id="mAdvDocStatus" style="font-weight:600;"></span>
                </div>
            </div>

            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Perkara Terkait</span>
                <span id="mAdvDocCase" style="color:#1e293b;font-weight:500;"></span>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Klien</span>
                    <span id="mAdvDocClient" style="color:#334155;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Pengunggah</span>
                    <span id="mAdvDocUploader" style="color:#334155;"></span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Versi Dokumen</span>
                    <span id="mAdvDocVersion" style="color:#2563eb;font-weight:600;"></span>
                </div>
                <div>
                    <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Ukuran Berkas</span>
                    <span id="mAdvDocSize" style="color:#334155;"></span>
                </div>
            </div>

            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Waktu Unggah</span>
                <span id="mAdvDocDate" style="color:#334155;"></span>
            </div>

            <div>
                <span style="color:#64748b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:600;">Keterangan / Catatan</span>
                <p id="mAdvDocDesc" style="color:#475569;margin:2px 0 0 0;line-height:1.4;background:#f8fafc;padding:8px 10px;border-radius:6px;border:1px solid #e2e8f0;"></p>
            </div>

            <div id="mAdvDocRejectionContainer" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px;">
                <span style="color:#991b1b;display:block;font-size:.72rem;text-transform:uppercase;font-weight:700;">Alasan Penolakan</span>
                <p id="mAdvDocRejection" style="color:#b91c1c;margin:2px 0 0 0;font-size:.85rem;font-style:italic;"></p>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:14px;border-top:1px solid #e2e8f0;">
            <button type="button" onclick="closeAdvokatDocDetail()" class="btn btn-outline" style="font-size:.825rem;">Tutup</button>
            <div id="mAdvDocFileActions" style="display:flex;gap:8px;">
                <a id="mAdvDocViewLink" href="#" target="_blank" class="btn btn-outline" style="font-size:.825rem;display:inline-flex;align-items:center;gap:4px;text-decoration:none;">
                    <i data-lucide="eye" style="width:14px;"></i> Lihat Dokumen
                </a>
                <a id="mAdvDocDownloadLink" href="#" class="btn btn-primary" style="font-size:.825rem;display:inline-flex;align-items:center;gap:4px;text-decoration:none;">
                    <i data-lucide="download" style="width:14px;"></i> Unduh
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function updateMintaFormAction(caseId) {
    document.getElementById('formMintaDokumen').action = `/advokat/perkara/${caseId}/dokumen`;
}

function updateAdvokatUploadAction(caseId) {
    document.getElementById('formUploadAdvokat').action = `/advokat/perkara/${caseId}/dokumen/upload`;
}

function openModalMintaDokumen() {
    const sel = document.getElementById('mintaCaseSelect');
    if (sel && sel.value) updateMintaFormAction(sel.value);
    document.getElementById('modalMintaDokumen').style.display = 'flex';
}

function closeModalMintaDokumen() {
    document.getElementById('modalMintaDokumen').style.display = 'none';
}

function openModalTolak(docId, title) {
    document.getElementById('formTolakDokumen').action = `/advokat/dokumen/${docId}/tolak`;
    document.getElementById('tolakDocTitle').textContent = title;
    document.getElementById('modalTolakDokumen').style.display = 'flex';
}

function closeModalTolak() {
    document.getElementById('modalTolakDokumen').style.display = 'none';
}

function openModalUploadAdvokat() {
    const sel = document.getElementById('advokatUploadCaseSelect');
    if (sel && sel.value) updateAdvokatUploadAction(sel.value);
    document.getElementById('modalUploadAdvokat').style.display = 'flex';
}

function closeModalUploadAdvokat() {
    document.getElementById('modalUploadAdvokat').style.display = 'none';
}

// Detail Dokumen Modal (Advokat)
function openAdvokatDocDetailModal(btn) {
    const d = btn.dataset;
    document.getElementById('mAdvDocName').textContent = d.name || '—';
    document.getElementById('mAdvDocType').textContent = d.type || '—';
    document.getElementById('mAdvDocStatus').textContent = d.status || '—';
    document.getElementById('mAdvDocCase').textContent = d.case || '—';
    document.getElementById('mAdvDocClient').textContent = d.client || '—';
    document.getElementById('mAdvDocUploader').textContent = d.uploader || '—';
    document.getElementById('mAdvDocVersion').textContent = d.version || 'v1';
    document.getElementById('mAdvDocSize').textContent = d.size || '—';
    document.getElementById('mAdvDocDate').textContent = d.date || '—';
    document.getElementById('mAdvDocDesc').textContent = d.desc || '—';

    const rejContainer = document.getElementById('mAdvDocRejectionContainer');
    const rejText = document.getElementById('mAdvDocRejection');
    if (d.reason && d.reason.trim() !== '') {
        rejText.textContent = d.reason;
        rejContainer.style.display = 'block';
    } else {
        rejContainer.style.display = 'none';
    }

    const fileActions = document.getElementById('mAdvDocFileActions');
    const viewLink = document.getElementById('mAdvDocViewLink');
    const downloadLink = document.getElementById('mAdvDocDownloadLink');

    if (d.hasFile === '1' && d.viewUrl) {
        fileActions.style.display = 'flex';
        viewLink.href = d.viewUrl;
        downloadLink.href = d.downloadUrl;
    } else {
        fileActions.style.display = 'none';
    }

    document.getElementById('modalAdvokatDocDetail').style.display = 'flex';
    setTimeout(() => lucide.createIcons(), 50);
}

function closeAdvokatDocDetail() {
    document.getElementById('modalAdvokatDocDetail').style.display = 'none';
}

// Live search filter
document.getElementById('advokatDocSearch')?.addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    const rows = document.querySelectorAll('#advokatDocsTable tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
    });
});

window.addEventListener('click', function(e) {
    if (e.target.id === 'modalMintaDokumen') closeModalMintaDokumen();
    if (e.target.id === 'modalTolakDokumen') closeModalTolak();
    if (e.target.id === 'modalUploadAdvokat') closeModalUploadAdvokat();
    if (e.target.id === 'modalAdvokatDocDetail') closeAdvokatDocDetail();
});
</script>
@endsection
