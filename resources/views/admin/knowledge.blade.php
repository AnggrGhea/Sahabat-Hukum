@extends('layouts.admin')

@section('title', 'Basis Pengetahuan')

@section('content')

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:12px 18px;margin-bottom:16px;font-size:.875rem;display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px;color:#16a34a;"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;padding:12px 18px;margin-bottom:16px;font-size:.875rem;">
    <strong>Terjadi kesalahan:</strong>
    <ul style="margin:4px 0 0 16px;">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari sumber pengetahuan..." id="searchInput">
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus"></i> Tambah Sumber
            </button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 35%;">Judul Sumber</th>
                    <th>Jenis Sumber</th>
                    <th>Status</th>
                    <th>Verifikator</th>
                    <th>Terakhir Diperbarui</th>
                    <th class="text-right" style="min-width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sources as $src)
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--color-navy); font-size: 0.875rem;">{{ $src->title }}</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text); margin-top: 2px;">{{ Str::limit($src->description ?? 'Tidak ada deskripsi singkat.', 75) }}</div>
                    </td>
                    <td>
                        <span style="font-size: 0.75rem; background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 4px; font-weight: 500;">
                            {{ $src->source_type }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('admin.knowledge.toggle', $src->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="Klik untuk mengubah status"
                                    style="border:none;cursor:pointer;background:transparent;padding:0;">
                                <span class="badge {{ $src->status === 'Aktif' ? 'badge-success' : 'badge-danger' }}" style="cursor:pointer;">
                                    {{ $src->status }}
                                </span>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div style="font-size: 0.8rem; color: var(--color-gray-dark);">{{ $src->verifier?->name ?? 'Admin Sistem' }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.775rem; color: var(--color-gray-text);">
                            {{ $src->updated_at ? \Carbon\Carbon::parse($src->updated_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '—' }}
                        </div>
                    </td>
                    <td class="text-right">
                        <div style="display: inline-flex; gap: 4px;">
                            <button type="button" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" 
                                    onclick="openDetailModal({{ json_encode($src) }})" title="Lihat Detail">
                                <i data-lucide="eye" style="width: 14px;"></i>
                            </button>
                            <button type="button" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" 
                                    onclick="openEditModal({{ json_encode($src) }})" title="Edit Sumber">
                                <i data-lucide="edit-2" style="width: 14px;"></i>
                            </button>
                            <form action="{{ route('admin.knowledge.destroy', $src->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sumber pengetahuan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: #ef4444; border-color: #fecaca;" title="Hapus Sumber">
                                    <i data-lucide="trash-2" style="width: 14px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2.5rem;color:var(--color-gray-text);">Belum ada data sumber pengetahuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL: TAMBAH SUMBER --}}
<div id="modal-create" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 25px rgba(0,0,0,.15);padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--color-navy);margin:0;">Tambah Sumber Pengetahuan</h3>
            <button type="button" onclick="closeCreateModal()" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;">✕</button>
        </div>

        <form action="{{ route('admin.knowledge.store') }}" method="POST">
            @csrf
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Judul Sumber Hukum / Panduan <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: UU No. 23 Tahun 2004 tentang Penghapusan KDRT"
                           style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;box-sizing:border-box;">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Jenis Sumber <span style="color:#ef4444;">*</span></label>
                        <select name="source_type" required style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;background:#fff;box-sizing:border-box;">
                            <option value="Undang-Undang">Undang-Undang</option>
                            <option value="KUHP">KUHP</option>
                            <option value="KUHPerdata">KUHPerdata</option>
                            <option value="Hukum Acara Perdata">Hukum Acara Perdata</option>
                            <option value="Surat Edaran Mahkamah Agung">Surat Edaran Mahkamah Agung</option>
                            <option value="Prosedur Layanan">Prosedur Layanan</option>
                            <option value="Peraturan Pemerintah">Peraturan Pemerintah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Status <span style="color:#ef4444;">*</span></label>
                        <select name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;background:#fff;box-sizing:border-box;">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Deskripsi Singkat / Metadata</label>
                    <textarea name="description" rows="2" placeholder="Ringkasan atau fokus materi hukum..."
                              style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
                </div>

                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Isi Materi Hukum Lengkap <span style="color:#ef4444;">*</span></label>
                    <textarea name="content" rows="8" required placeholder="Tuliskan norma hukum, pasal, poin-poin ketentuan hukum yang lengkap dan terverifikasi..."
                              style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;box-sizing:border-box;resize:vertical;line-height:1.5;"></textarea>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px;padding-top:12px;border-top:1px solid #e2e8f0;">
                    <button type="button" onclick="closeCreateModal()" class="btn btn-outline">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Sumber</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: EDIT SUMBER --}}
<div id="modal-edit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 25px rgba(0,0,0,.15);padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--color-navy);margin:0;">Edit Sumber Pengetahuan</h3>
            <button type="button" onclick="closeEditModal()" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;">✕</button>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Judul Sumber Hukum / Panduan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_title" name="title" required
                           style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;box-sizing:border-box;">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Jenis Sumber <span style="color:#ef4444;">*</span></label>
                        <select id="edit_source_type" name="source_type" required style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;background:#fff;box-sizing:border-box;">
                            <option value="Undang-Undang">Undang-Undang</option>
                            <option value="KUHP">KUHP</option>
                            <option value="KUHPerdata">KUHPerdata</option>
                            <option value="Hukum Acara Perdata">Hukum Acara Perdata</option>
                            <option value="Surat Edaran Mahkamah Agung">Surat Edaran Mahkamah Agung</option>
                            <option value="Prosedur Layanan">Prosedur Layanan</option>
                            <option value="Peraturan Pemerintah">Peraturan Pemerintah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Status <span style="color:#ef4444;">*</span></label>
                        <select id="edit_status" name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;background:#fff;box-sizing:border-box;">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Deskripsi Singkat / Metadata</label>
                    <textarea id="edit_description" name="description" rows="2"
                              style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;box-sizing:border-box;resize:vertical;"></textarea>
                </div>

                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Isi Materi Hukum Lengkap <span style="color:#ef4444;">*</span></label>
                    <textarea id="edit_content" name="content" rows="8" required
                              style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:.85rem;box-sizing:border-box;resize:vertical;line-height:1.5;"></textarea>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px;padding-top:12px;border-top:1px solid #e2e8f0;">
                    <button type="button" onclick="closeEditModal()" class="btn btn-outline">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: DETAIL SUMBER --}}
<div id="modal-detail" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:720px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 25px rgba(0,0,0,.15);padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <div>
                <span id="detail_type" style="font-size:.75rem;background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:4px;font-weight:600;"></span>
                <h3 id="detail_title" style="font-size:1.15rem;font-weight:700;color:var(--color-navy);margin:6px 0 0;"></h3>
            </div>
            <button type="button" onclick="closeDetailModal()" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;">✕</button>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px;">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;">
                <div style="font-size:.75rem;color:#64748b;font-weight:600;margin-bottom:2px;">Ringkasan / Metadata:</div>
                <div id="detail_desc" style="font-size:.85rem;color:#334155;line-height:1.5;"></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;font-size:.78rem;color:#64748b;background:#f1f5f9;padding:10px 14px;border-radius:6px;">
                <div>Status: <span id="detail_status" style="font-weight:600;color:#0f172a;"></span></div>
                <div>Verifikator: <span id="detail_verifier" style="font-weight:600;color:#0f172a;"></span></div>
                <div>Diperbarui: <span id="detail_date" style="font-weight:600;color:#0f172a;"></span></div>
            </div>

            <div>
                <div style="font-size:.8rem;font-weight:700;color:#0b1a30;margin-bottom:6px;">Isi Materi Hukum:</div>
                <div id="detail_content" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;font-size:.85rem;color:#1e293b;line-height:1.65;white-space:pre-wrap;max-height:360px;overflow-y:auto;"></div>
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:10px;">
                <button type="button" onclick="closeDetailModal()" class="btn btn-outline">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table-container tbody tr');
            rows.forEach(r => {
                r.style.display = r.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }
});

function openCreateModal() {
    document.getElementById('modal-create').style.display = 'flex';
}
function closeCreateModal() {
    document.getElementById('modal-create').style.display = 'none';
}

function openEditModal(src) {
    document.getElementById('editForm').action = '/admin/knowledge/' + src.id;
    document.getElementById('edit_title').value = src.title || '';
    document.getElementById('edit_source_type').value = src.source_type || 'Undang-Undang';
    document.getElementById('edit_status').value = src.status || 'Aktif';
    document.getElementById('edit_description').value = src.description || '';
    document.getElementById('edit_content').value = src.content || '';
    document.getElementById('modal-edit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modal-edit').style.display = 'none';
}

function openDetailModal(src) {
    document.getElementById('detail_type').textContent = src.source_type || 'Peraturan';
    document.getElementById('detail_title').textContent = src.title || '';
    document.getElementById('detail_desc').textContent = src.description || 'Tidak ada deskripsi.';
    document.getElementById('detail_status').textContent = src.status || 'Aktif';
    document.getElementById('detail_verifier').textContent = (src.verifier && src.verifier.name) ? src.verifier.name : 'Admin Sistem';
    document.getElementById('detail_date').textContent = src.updated_at ? src.updated_at.substring(0, 10) : '—';
    document.getElementById('detail_content').textContent = src.content || '';
    document.getElementById('modal-detail').style.display = 'flex';
}
function closeDetailModal() {
    document.getElementById('modal-detail').style.display = 'none';
}
</script>
@endsection
