@extends('layouts.klien')

@section('title', 'Konsultasi')
@section('header-title', 'Konsultasi')

@section('content')

{{-- Flash Message --}}
@if(session('success'))
<div style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.875rem;font-weight:500;">
    ✓ {{ session('success') }}
</div>
@endif

{{-- Breadcrumb --}}
<div class="breadcrumb" style="margin-bottom:16px;">
    <a href="{{ route('klien.dashboard') }}">Beranda</a>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    <span class="current">Konsultasi</span>
</div>

{{-- Page Title --}}
<div class="page-title-row" style="margin-bottom:20px;">
    <div class="page-heading">
        <h1>Konsultasi</h1>
        <p>Ajukan dan pantau konsultasi hukum Anda.</p>
    </div>
    <button onclick="document.getElementById('modal-ajukan').style.display='flex'" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Ajukan Konsultasi
    </button>
</div>

{{-- Consultation Table --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Riwayat Konsultasi</div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Topik Konsultasi</th>
                    <th>Advokat</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Jadwal</th>
                    <th>Jenis Masalah</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($consultations as $item)
                @php
                    if ($item->status === 'Menunggu') {
                        $badgeClass = $item->lawyer_id ? 'badge-blue' : 'badge-yellow';
                        $statusLabel = $item->lawyer_id ? 'Advokat Ditetapkan' : 'Menunggu Penetapan Advokat';
                    } elseif ($item->status === 'Dijadwalkan') {
                        $badgeClass = 'badge-blue';
                        $statusLabel = 'Dijadwalkan';
                    } elseif ($item->status === 'Selesai') {
                        $badgeClass = 'badge-gray';
                        $statusLabel = 'Selesai';
                    } elseif ($item->status === 'Dibatalkan') {
                        $badgeClass = 'badge-red';
                        $statusLabel = 'Dibatalkan';
                    } else {
                        $badgeClass = 'badge-yellow';
                        $statusLabel = $item->status;
                    }
                @endphp
                <tr>
                    <td class="td-bold">{{ $item->title }}</td>
                    <td>
                        @if($item->lawyer)
                            <span style="font-weight:500; color:#1e293b;">{{ $item->lawyer->name }}</span>
                        @else
                            <span style="color:#d97706; font-size:0.78rem; font-weight:600;">Menunggu Admin</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                    <td>{{ $item->scheduled_at ? \Carbon\Carbon::parse($item->scheduled_at)->locale('id')->isoFormat('D MMM YYYY, HH.mm') . ' WIB' : '—' }}</td>
                    <td>{{ $item->problem_type }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                    <td>
                        <a href="{{ route('klien.consultations.show', $item->id) }}" class="link-btn">
                            Lihat Detail
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">
                        Belum ada konsultasi. Ajukan konsultasi pertama Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Detail Card --}}
@if($consultation)
<div class="card" style="margin-top:16px;">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="card-title">Detail: {{ $consultation->title }}</div>
        <div style="display:flex; align-items:center; gap:8px;">
            @php
                if ($consultation->status === 'Menunggu') {
                    $detailBadge = $consultation->lawyer_id ? 'badge-blue' : 'badge-yellow';
                    $detailStatusLabel = $consultation->lawyer_id ? 'Advokat Ditetapkan' : 'Menunggu Penetapan Advokat';
                } elseif ($consultation->status === 'Dijadwalkan') {
                    $detailBadge = 'badge-blue';
                    $detailStatusLabel = 'Dijadwalkan';
                } elseif ($consultation->status === 'Selesai') {
                    $detailBadge = 'badge-gray';
                    $detailStatusLabel = 'Selesai';
                } elseif ($consultation->status === 'Dibatalkan') {
                    $detailBadge = 'badge-red';
                    $detailStatusLabel = 'Dibatalkan';
                } else {
                    $detailBadge = 'badge-yellow';
                    $detailStatusLabel = $consultation->status;
                }
            @endphp
            <span class="badge {{ $detailBadge }}">{{ $detailStatusLabel }}</span>
            @if($consultation->conversation)
                <a href="{{ route('klien.chat', ['conversation_id' => $consultation->conversation->id]) }}"
                   class="btn btn-primary"
                   style="padding:6px 12px; font-size:0.8rem; display:inline-flex; align-items:center; gap:6px; background:#0b1a30; border-color:#0b1a30;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Buka Percakapan
                </a>
            @endif
        </div>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div>
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Data Konsultasi</div>
                <div style="margin-bottom:8px;">
                    <span style="font-size:.75rem;color:#94a3b8;display:block;">Advokat Penanggung Jawab</span>
                    <span style="font-size:.875rem;font-weight:600;color:#1e293b;">
                        {{ $consultation->lawyer?->name ?? 'Menunggu penetapan oleh Admin' }}
                    </span>
                </div>
                <div style="margin-bottom:8px;"><span style="font-size:.75rem;color:#94a3b8;display:block;">Tanggal Pengajuan</span><span style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ \Carbon\Carbon::parse($consultation->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</span></div>
                <div style="margin-bottom:8px;"><span style="font-size:.75rem;color:#94a3b8;display:block;">Jadwal</span><span style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $consultation->scheduled_at ? \Carbon\Carbon::parse($consultation->scheduled_at)->locale('id')->isoFormat('D MMM YYYY, HH.mm') . ' WIB' : 'Belum dijadwalkan' }}</span></div>
                <div style="margin-bottom:8px;"><span style="font-size:.75rem;color:#94a3b8;display:block;">Jenis Masalah</span><span style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $consultation->problem_type }}</span></div>
            </div>
            <div>
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Ringkasan Permasalahan</div>
                <p style="font-size:.875rem;color:#374151;line-height:1.6;">{{ $consultation->description }}</p>
            </div>
        </div>
        @if($consultation->result_notes)
        <div style="margin-top:16px;padding:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
            <div style="font-size:.75rem;color:#15803d;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Catatan Hasil Konsultasi</div>
            <p style="font-size:.875rem;color:#374151;line-height:1.6;">{{ $consultation->result_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Modal: Ajukan Konsultasi --}}
<div id="modal-ajukan" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:28px;width:520px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;">Ajukan Konsultasi</h3>
            <button onclick="document.getElementById('modal-ajukan').style.display='none'" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;">✕</button>
        </div>
        <form method="POST" action="{{ route('klien.consultations.store') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Jenis Permasalahan *</label>
                <select name="problem_type" required style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:.875rem;box-sizing:border-box;">
                    <option value="">-- Pilih Jenis --</option>
                    <option value="Hukum Pidana">Hukum Pidana</option>
                    <option value="Hukum Perdata">Hukum Perdata</option>
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Judul / Topik Konsultasi *</label>
                <input type="text" name="title" required placeholder="Ringkasan singkat permasalahan Anda" style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:.875rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:20px;">
                <label style="font-size:.78rem;font-weight:500;color:#374151;display:block;margin-bottom:4px;">Uraian Permasalahan *</label>
                <textarea name="description" rows="5" required placeholder="Ceritakan permasalahan Anda secara detail..." style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:.875rem;box-sizing:border-box;line-height:1.5;"></textarea>
            </div>
            <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:12px;margin-bottom:20px;font-size:.8rem;color:#92400e;">
                <strong>Catatan:</strong> Setelah pengajuan dikirim, Admin akan meninjau dan menetapkan Advokat penanggung jawab. Percakapan akan aktif setelah Advokat ditetapkan.
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-ajukan').style.display='none'" style="padding:9px 20px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;cursor:pointer;background:#fff;color:#374151;">Batal</button>
                <button type="submit" style="padding:9px 20px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;">Ajukan Konsultasi</button>
            </div>
        </form>
    </div>
</div>

@endsection
