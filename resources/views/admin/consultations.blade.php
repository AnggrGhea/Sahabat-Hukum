@extends('layouts.admin')

@section('title', 'Data Konsultasi')

@section('content')
@if(session('success'))
<div style="background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:.875rem; font-weight:500; display:flex; align-items:center; gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari konsultasi..." id="searchInput" oninput="filterTable(this.value)">
        </div>
    </div>

    <div class="table-container">
        <table id="consultTable">
            <thead>
                <tr>
                    <th>Topik</th>
                    <th>Klien</th>
                    <th>Advokat</th>
                    <th>Jenis Masalah</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Jadwal</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($consultations as $c)
                @php
                    if ($c->status === 'Menunggu') {
                        $badgeClass = $c->lawyer_id ? 'badge-info' : 'badge-warning';
                        $statusLabel = $c->lawyer_id ? 'Advokat Ditetapkan' : 'Menunggu Penetapan Advokat';
                    } elseif ($c->status === 'Dijadwalkan') {
                        $badgeClass = 'badge-info';
                        $statusLabel = 'Dijadwalkan';
                    } elseif ($c->status === 'Selesai') {
                        $badgeClass = 'badge-success';
                        $statusLabel = 'Selesai';
                    } elseif ($c->status === 'Dibatalkan') {
                        $badgeClass = 'badge-danger';
                        $statusLabel = 'Dibatalkan';
                    } else {
                        $badgeClass = 'badge-warning';
                        $statusLabel = $c->status;
                    }
                @endphp
                <tr>
                    <td style="font-weight:600; color:var(--color-navy,#0b1a30);">
                        {{ $c->title }}
                    </td>
                    <td>{{ $c->client->name }}</td>
                    <td>
                        @if($c->lawyer)
                            <div style="font-weight:500; display:inline-flex; align-items:center; gap:6px;">
                                <span style="width:22px; height:22px; border-radius:50%; background:#1e3a5f; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:0.68rem; font-weight:700;">
                                    {{ strtoupper(substr($c->lawyer->name, 0, 1)) }}
                                </span>
                                {{ $c->lawyer->name }}
                            </div>
                        @else
                            <span style="display:inline-block; padding:3px 8px; border-radius:6px; background:#fff1f2; color:#e11d48; font-size:0.75rem; font-weight:600;">
                                Belum Ditentukan
                            </span>
                        @endif
                    </td>
                    <td>{{ $c->problem_type }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                    <td>{{ $c->scheduled_at ? \Carbon\Carbon::parse($c->scheduled_at)->locale('id')->isoFormat('D MMM YYYY, HH.mm') : '—' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                    <td class="text-right">
                        <button type="button"
                                class="btn btn-outline"
                                style="padding: 0.35rem 0.75rem; font-size: 0.8rem; display:inline-flex; align-items:center; gap:4px;"
                                onclick="openAssignModal({{ $c->id }}, '{{ addslashes($c->title) }}', '{{ addslashes($c->client->name) }}', '{{ addslashes($c->problem_type) }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', $c->description)) }}', '{{ \Carbon\Carbon::parse($c->created_at)->locale('id')->isoFormat('D MMM YYYY') }}', '{{ $c->lawyer_id ?? '' }}')">
                            <i data-lucide="user-check" style="width: 14px; height: 14px;"></i>
                            {{ $c->lawyer_id ? 'Ganti Advokat' : 'Tetapkan Advokat' }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:2rem;color:var(--color-gray-text);">Tidak ada data konsultasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tetapkan Advokat --}}
<div id="modalAssignAdvokat" style="display:none; position:fixed; inset:0; background:rgba(11,26,48,0.55); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
    <div style="background:#ffffff; border-radius:12px; width:520px; max-width:92vw; box-shadow:0 12px 30px rgba(0,0,0,0.15); overflow:hidden;">
        <div style="padding:18px 24px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; background:#f8fafc;">
            <div style="display:flex; align-items:center; gap:8px;">
                <i data-lucide="briefcase" style="width:18px; height:18px; color:#1e3a5f;"></i>
                <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#0b1a30;">Tetapkan Advokat Penanggung Jawab</h3>
            </div>
            <button type="button" onclick="closeAssignModal()" style="background:none; border:none; cursor:pointer; color:#64748b; font-size:1.2rem; line-height:1;">✕</button>
        </div>

        <form id="formAssignAdvokat" method="POST" action="" style="padding:20px 24px;">
            @csrf
            {{-- Info Konsultasi --}}
            <div style="background:#f1f5f9; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:16px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:8px;">
                    <div>
                        <span style="display:block; font-size:0.72rem; color:#64748b; text-transform:uppercase; font-weight:600;">Klien</span>
                        <strong id="modalClientName" style="font-size:0.875rem; color:#1e293b;">-</strong>
                    </div>
                    <div>
                        <span style="display:block; font-size:0.72rem; color:#64748b; text-transform:uppercase; font-weight:600;">Jenis Masalah</span>
                        <span id="modalProblemType" style="font-size:0.875rem; color:#1e293b; font-weight:500;">-</span>
                    </div>
                </div>
                <div style="margin-bottom:8px;">
                    <span style="display:block; font-size:0.72rem; color:#64748b; text-transform:uppercase; font-weight:600;">Topik Konsultasi</span>
                    <strong id="modalTopic" style="font-size:0.875rem; color:#1e293b;">-</strong>
                </div>
                <div>
                    <span style="display:block; font-size:0.72rem; color:#64748b; text-transform:uppercase; font-weight:600;">Uraian Masalah</span>
                    <p id="modalDescription" style="margin:4px 0 0 0; font-size:0.82rem; color:#475569; line-height:1.5; max-height:80px; overflow-y:auto;">-</p>
                </div>
            </div>

            {{-- Pilih Advokat --}}
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#1e293b; margin-bottom:6px;">
                    Pilih Advokat yang Menangani <span style="color:#ef4444;">*</span>
                </label>
                <select name="lawyer_id" id="selectLawyerId" required style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:0.875rem; color:#1e293b; background:#fff;">
                    <option value="">-- Pilih Advokat Tersedia --</option>
                    @foreach($lawyers as $lawyer)
                        <option value="{{ $lawyer->id }}">{{ $lawyer->name }} ({{ $lawyer->lawyerProfile?->specialization ?? 'Advokat' }})</option>
                    @endforeach
                </select>
                <div style="font-size:0.75rem; color:#64748b; margin-top:6px;">
                    * Setelah disimpan, percakapan antara Klien dan Advokat yang dipilih akan aktif secara otomatis.
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeAssignModal()" class="btn btn-outline" style="padding:8px 16px;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="padding:8px 20px; background:#0b1a30; border-color:#0b1a30; color:#ffffff;">
                    Simpan Penugasan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filterTable(val) {
    const rows = document.querySelectorAll('#consultTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val.toLowerCase()) ? '' : 'none';
    });
}

function openAssignModal(id, title, clientName, problemType, description, date, currentLawyerId) {
    const modal = document.getElementById('modalAssignAdvokat');
    const form = document.getElementById('formAssignAdvokat');
    form.action = "{{ url('/admin/consultations') }}/" + id + "/assign-lawyer";

    document.getElementById('modalClientName').textContent = clientName;
    document.getElementById('modalProblemType').textContent = problemType;
    document.getElementById('modalTopic').textContent = title;
    document.getElementById('modalDescription').textContent = description || '—';

    const select = document.getElementById('selectLawyerId');
    select.value = currentLawyerId || '';

    modal.style.display = 'flex';
    if (window.lucide) {
        lucide.createIcons();
    }
}

function closeAssignModal() {
    document.getElementById('modalAssignAdvokat').style.display = 'none';
}
</script>
@endsection
