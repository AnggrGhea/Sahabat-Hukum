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
                    $badgeClass = match($item->status) {
                        'Dijadwalkan' => 'badge-blue',
                        'Selesai'     => 'badge-gray',
                        'Dibatalkan'  => 'badge-red',
                        default       => 'badge-yellow',
                    };
                @endphp
                <tr>
                    <td class="td-bold">{{ $item->title }}</td>
                    <td>{{ $item->lawyer?->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                    <td>{{ $item->scheduled_at ? \Carbon\Carbon::parse($item->scheduled_at)->locale('id')->isoFormat('D MMM YYYY, HH.mm') . ' WIB' : '—' }}</td>
                    <td>{{ $item->problem_type }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $item->status }}</span></td>
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
    <div class="card-header">
        <div class="card-title">Detail: {{ $consultation->title }}</div>
        @php
            $detailBadge = match($consultation->status) {
                'Dijadwalkan' => 'badge-blue',
                'Selesai'     => 'badge-gray',
                'Dibatalkan'  => 'badge-red',
                default       => 'badge-yellow',
            };
        @endphp
        <span class="badge {{ $detailBadge }}">{{ $consultation->status }}</span>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div>
                <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Data Konsultasi</div>
                <div style="margin-bottom:8px;"><span style="font-size:.75rem;color:#94a3b8;display:block;">Advokat</span><span style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $consultation->lawyer?->name ?? '—' }}</span></div>
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
                <strong>Catatan:</strong> Setelah pengajuan diterima, advokat akan menghubungi Anda untuk menjadwalkan waktu konsultasi.
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-ajukan').style.display='none'" style="padding:9px 20px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;cursor:pointer;background:#fff;color:#374151;">Batal</button>
                <button type="submit" style="padding:9px 20px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;">Ajukan Konsultasi</button>
            </div>
        </form>
    </div>
</div>

@endsection
