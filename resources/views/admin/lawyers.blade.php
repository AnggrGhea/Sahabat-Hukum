@extends('layouts.admin')

@section('title', 'Data Advokat')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
<div style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.875rem;font-weight:500;display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#b91c1c;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.875rem;font-weight:500;">
    <ul style="margin:0;padding-left:18px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari nama, spesialisasi, email..." id="searchInput" oninput="filterTable(this.value)">
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-primary" onclick="openModalTambahAdvokat()">
                <i data-lucide="user-plus"></i> Tambah Advokat
            </button>
        </div>
    </div>

    <div class="table-container">
        <table id="lawyersTable">
            <thead>
                <tr>
                    <th>Nama Advokat</th>
                    <th>Spesialisasi</th>
                    <th>Kontak</th>
                    <th style="text-align:center;">Perkara Aktif</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lawyers as $lawyer)
                @php
                    $activeCases = \App\Models\LegalCase::where('lawyer_id', $lawyer->id)
                        ->whereNotIn('status', ['Selesai', 'Dibatalkan'])
                        ->count();
                    $badgeClass = $lawyer->status === 'aktif' ? 'badge-success' : 'badge-danger';
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:50%;background:#1e3a5f;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;">
                                {{ strtoupper(substr($lawyer->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; color: var(--color-gray-dark);">{{ $lawyer->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--color-gray-text);">ID: A-{{ str_pad($lawyer->id, 3, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $lawyer->lawyerProfile->specialization ?? 'Hukum Umum' }}</td>
                    <td>
                        <div>{{ $lawyer->email }}</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">{{ $lawyer->lawyerProfile->phone ?? '—' }}</div>
                    </td>
                    <td style="text-align:center;font-weight:600;">{{ $activeCases }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ ucfirst($lawyer->status) }}</span></td>
                    <td class="text-right">
                        <button type="button" class="btn btn-outline" style="padding: 0.25rem 0.6rem;font-size:.78rem;" 
                                onclick="openDetailAdvokat('{{ addslashes($lawyer->name) }}', '{{ addslashes($lawyer->email) }}', '{{ addslashes($lawyer->lawyerProfile->specialization ?? 'Hukum Umum') }}', '{{ addslashes($lawyer->lawyerProfile->phone ?? '-') }}', '{{ ucfirst($lawyer->status) }}', '{{ $activeCases }}')">
                            <i data-lucide="eye" style="width: 14px;"></i> Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2.5rem;color:var(--color-gray-text);">
                        <i data-lucide="users" style="width:36px;height:36px;margin:0 auto 8px;display:block;opacity:.4;"></i>
                        Belum ada data advokat.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tambah Advokat --}}
<div id="modalTambahAdvokat" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,.15);overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;">
            <div style="font-weight:700;font-size:1.05rem;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <i data-lucide="user-plus" style="width:18px;height:18px;color:#1e3a5f;"></i>
                Tambah Advokat Baru
            </div>
            <button type="button" onclick="closeModalTambahAdvokat()" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;line-height:1;">&times;</button>
        </div>
        <form action="{{ route('admin.lawyers.store') }}" method="POST" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
            @csrf
            <div>
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Nama Lengkap & Gelar <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Rian Pratama, S.H., M.H." style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.875rem;box-sizing:border-box;">
            </div>

            <div>
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Email Akun <span style="color:#ef4444;">*</span></label>
                <input type="email" name="email" required placeholder="advokat@sahabathukum.com" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.875rem;box-sizing:border-box;">
            </div>

            <div>
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Kata Sandi Awal <span style="color:#ef4444;">*</span></label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.875rem;box-sizing:border-box;">
            </div>

            <div>
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">Spesialisasi Hukum</label>
                <input type="text" name="specialization" placeholder="Contoh: Hukum Pidana, Hukum Perdata" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.875rem;box-sizing:border-box;">
            </div>

            <div>
                <label style="display:block;font-size:.825rem;font-weight:600;color:#334155;margin-bottom:4px;">No. Telepon / WhatsApp</label>
                <input type="text" name="phone" placeholder="Contoh: 0812-3456-7890" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.875rem;box-sizing:border-box;">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;padding-top:12px;border-top:1px solid #f1f5f9;">
                <button type="button" onclick="closeModalTambahAdvokat()" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Advokat</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Advokat --}}
<div id="modalDetailAdvokat" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:440px;box-shadow:0 20px 25px -5px rgba(0,0,0,.15);overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;">
            <div style="font-weight:700;font-size:1.05rem;color:#0f172a;">Detail Informasi Advokat</div>
            <button type="button" onclick="closeDetailAdvokat()" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;line-height:1;">&times;</button>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:12px;">
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Nama Advokat</div>
                <div id="detailNama" style="font-size:1rem;font-weight:600;color:#0f172a;margin-top:2px;">-</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Email</div>
                <div id="detailEmail" style="font-size:.9rem;color:#334155;margin-top:2px;">-</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Spesialisasi</div>
                <div id="detailSpesialisasi" style="font-size:.9rem;color:#334155;margin-top:2px;">-</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Telepon / WhatsApp</div>
                <div id="detailTelepon" style="font-size:.9rem;color:#334155;margin-top:2px;">-</div>
            </div>
            <div style="display:flex;gap:16px;">
                <div style="flex:1;">
                    <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Status</div>
                    <div id="detailStatus" style="font-size:.9rem;color:#334155;margin-top:2px;">-</div>
                </div>
                <div style="flex:1;">
                    <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;">Perkara Aktif</div>
                    <div id="detailPerkara" style="font-size:.9rem;font-weight:700;color:#1e3a5f;margin-top:2px;">-</div>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:8px;padding-top:12px;border-top:1px solid #f1f5f9;">
                <button type="button" onclick="closeDetailAdvokat()" class="btn btn-outline">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable(val) {
    const rows = document.querySelectorAll('#lawyersTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(val.toLowerCase()) ? '' : 'none';
    });
}

function openModalTambahAdvokat() {
    const modal = document.getElementById('modalTambahAdvokat');
    if (modal) {
        modal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
}

function closeModalTambahAdvokat() {
    const modal = document.getElementById('modalTambahAdvokat');
    if (modal) modal.style.display = 'none';
}

function openDetailAdvokat(name, email, spec, phone, status, cases) {
    document.getElementById('detailNama').textContent = name;
    document.getElementById('detailEmail').textContent = email;
    document.getElementById('detailSpesialisasi').textContent = spec;
    document.getElementById('detailTelepon').textContent = phone;
    document.getElementById('detailStatus').textContent = status;
    document.getElementById('detailPerkara').textContent = cases + ' Perkara';
    
    const modal = document.getElementById('modalDetailAdvokat');
    if (modal) modal.style.display = 'flex';
}

function closeDetailAdvokat() {
    const modal = document.getElementById('modalDetailAdvokat');
    if (modal) modal.style.display = 'none';
}
</script>
@endsection
