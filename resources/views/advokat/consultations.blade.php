@extends('layouts.advokat')

@section('title', 'Konsultasi')

@section('content')
<style>
.konsultasi-layout { display: grid; grid-template-columns: 340px 1fr; gap: 0; height: calc(100vh - 80px); border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; }
.k-list-panel { border-right: 1px solid #e2e8f0; overflow-y: auto; background: #fff; }
.k-list-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; }
.k-list-title { font-size: 1rem; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
.k-filter-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.k-tab { padding: 5px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 500; cursor: pointer; text-decoration: none; border: 1px solid #e2e8f0; color: #64748b; background: #f8fafc; transition: all .15s; }
.k-tab.active { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
.k-item { display: flex; align-items: flex-start; gap: 12px; padding: 14px 20px; cursor: pointer; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: background .15s; }
.k-item:hover { background: #f8fafc; }
.k-item.active-item { background: #eff6ff; border-left: 3px solid #1e3a5f; }
.k-avatar { width: 36px; height: 36px; border-radius: 50%; background: #1e3a5f; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: .9rem; flex-shrink: 0; }
.k-item-info { flex: 1; min-width: 0; }
.k-item-name { font-size: .85rem; font-weight: 600; color: #1e293b; }
.k-item-topic { font-size: .78rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
.k-item-date { font-size: .72rem; color: #94a3b8; margin-top: 2px; }
.status-badge { font-size: .7rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
.badge-menunggu { background: #fef3c7; color: #d97706; }
.badge-dijadwalkan { background: #dbeafe; color: #2563eb; }
.badge-selesai { background: #dcfce7; color: #16a34a; }
.badge-dibatalkan { background: #fee2e2; color: #dc2626; }
/* Detail Panel */
.k-detail-panel { overflow-y: auto; padding: 28px; background: #f8fafc; }
.k-detail-empty { display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; gap: 12px; color: #94a3b8; }
.k-detail-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
.k-detail-status { display: inline-flex; align-items: center; gap: 6px; font-size: .8rem; font-weight: 600; margin-bottom: 20px; }
.k-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.k-detail-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
.k-detail-card-title { font-size: .75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 12px; }
.k-detail-field { margin-bottom: 10px; }
.k-field-label { font-size: .72rem; color: #94a3b8; margin-bottom: 2px; }
.k-field-value { font-size: .85rem; font-weight: 500; color: #1e293b; }
.k-ringkasan { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px; }
.k-actions { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
.k-action-title { font-size: .75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 12px; }
.k-action-btn { display: flex; align-items: center; gap: 8px; width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; color: #374151; font-size: .85rem; font-weight: 500; cursor: pointer; margin-bottom: 8px; transition: all .15s; text-decoration: none; }
.k-action-btn:hover { background: #eff6ff; border-color: #93c5fd; color: #1e3a5f; }
.k-action-btn.primary { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
.k-action-btn.primary:hover { background: #16294a; }
.buat-perkara { background: linear-gradient(135deg, #1e3a5f, #2563eb); color: #fff; border: none; }
.buat-perkara:hover { opacity: .9; }
/* Form inline */
.k-form-section { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 16px; margin-top: 12px; }
.k-form-section label { font-size: .78rem; font-weight: 500; color: #374151; display: block; margin-bottom: 4px; }
.k-form-section input, .k-form-section textarea, .k-form-section select { width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 10px; font-size: .85rem; color: #1e293b; background: #fff; box-sizing: border-box; }
.k-form-section .btn-submit { margin-top: 10px; padding: 8px 18px; background: #1e3a5f; color: #fff; border: none; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer; }
.alert-success { background: #dcfce7; border: 1px solid #bbf7d0; color: #15803d; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: .85rem; font-weight: 500; }
</style>

{{-- Flash Message --}}
@if(session('success'))
<div class="alert-success">✓ {{ session('success') }}</div>
@endif

<div class="konsultasi-layout">
    {{-- LEFT: List Panel --}}
    <div class="k-list-panel">
        <div class="k-list-header">
            <div class="k-list-title">Konsultasi</div>
            <div class="k-filter-tabs">
                @foreach(['Semua','Menunggu','Dijadwalkan','Selesai'] as $tab)
                <a href="{{ route('advokat.consultations', ['status' => $tab]) }}"
                   class="k-tab {{ $filter === $tab ? 'active' : '' }}">{{ $tab }}</a>
                @endforeach
            </div>
        </div>

        @forelse($consultations as $item)
        @php
            $badgeClass = match($item->status) {
                'Dijadwalkan' => 'badge-dijadwalkan',
                'Selesai'     => 'badge-selesai',
                'Dibatalkan'  => 'badge-dibatalkan',
                default       => 'badge-menunggu',
            };
            $isActive = $consultation && $consultation->id === $item->id;
        @endphp
        <a href="{{ route('advokat.consultations.show', $item->id) }}"
           class="k-item {{ $isActive ? 'active-item' : '' }}">
            <div class="k-avatar">{{ strtoupper(substr($item->client->name, 0, 1)) }}</div>
            <div class="k-item-info">
                <div class="k-item-name">{{ $item->client->name }}</div>
                <div class="k-item-topic">{{ $item->title }}</div>
                <div class="k-item-date">{{ \Carbon\Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</div>
            </div>
            <span class="status-badge {{ $badgeClass }}">{{ $item->status }}</span>
        </a>
        @empty
        <div style="text-align:center;padding:3rem;color:#94a3b8;font-size:.875rem;">Tidak ada konsultasi.</div>
        @endforelse
    </div>

    {{-- RIGHT: Detail Panel --}}
    <div class="k-detail-panel">
        @if($consultation)
        @php
            $badgeClass = match($consultation->status) {
                'Dijadwalkan' => 'badge-dijadwalkan',
                'Selesai'     => 'badge-selesai',
                'Dibatalkan'  => 'badge-dibatalkan',
                default       => 'badge-menunggu',
            };
        @endphp
        <div class="k-detail-title">{{ $consultation->title }}</div>
        <div class="k-detail-status">
            <span class="status-badge {{ $badgeClass }}">{{ $consultation->status }}</span>
        </div>

        <div class="k-detail-grid">
            <div class="k-detail-card">
                <div class="k-detail-card-title">Data Klien</div>
                <div class="k-detail-field">
                    <div class="k-field-label">Nama Klien</div>
                    <div class="k-field-value">{{ $consultation->client->name }}</div>
                </div>
                <div class="k-detail-field">
                    <div class="k-field-label">Tanggal Pengajuan</div>
                    <div class="k-field-value">{{ \Carbon\Carbon::parse($consultation->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</div>
                </div>
                <div class="k-detail-field">
                    <div class="k-field-label">Jadwal</div>
                    <div class="k-field-value">{{ $consultation->scheduled_at ? \Carbon\Carbon::parse($consultation->scheduled_at)->locale('id')->isoFormat('D MMM YYYY, HH.mm') : '—' }}</div>
                </div>
                <div class="k-detail-field">
                    <div class="k-field-label">Jenis Masalah</div>
                    <div class="k-field-value">{{ $consultation->problem_type }}</div>
                </div>
            </div>

            <div class="k-actions">
                <div class="k-action-title">Tindakan Cepat</div>

                {{-- Buka Percakapan --}}
                @if($consultation->conversation)
                <a href="{{ route('advokat.chat', ['conversation_id' => $consultation->conversation->id]) }}" class="k-action-btn primary" style="background:#0b1a30; border-color:#0b1a30; color:#fff;">
                    <i data-lucide="message-circle" style="width:16px;"></i> Buka Percakapan
                </a>
                @endif

                {{-- Jadwalkan --}}
                @if(in_array($consultation->status, ['Menunggu', 'Dijadwalkan']))
                <button onclick="document.getElementById('form-jadwal').classList.toggle('hidden')" class="k-action-btn">
                    <i data-lucide="calendar" style="width:16px;"></i> Jadwalkan Konsultasi
                </button>
                <div id="form-jadwal" class="k-form-section hidden">
                    <form method="POST" action="{{ route('advokat.consultations.schedule', $consultation->id) }}">
                        @csrf
                        <label>Tanggal & Waktu</label>
                        <input type="datetime-local" name="scheduled_at" required
                               value="{{ $consultation->scheduled_at ? \Carbon\Carbon::parse($consultation->scheduled_at)->format('Y-m-d\TH:i') : '' }}">
                        <label style="margin-top:8px;">Lokasi</label>
                        <input type="text" name="location" placeholder="Kantor / Online" value="Kantor">
                        <button type="submit" class="btn-submit">Simpan Jadwal</button>
                    </form>
                </div>
                @endif

                {{-- Catat Hasil --}}
                @if($consultation->status !== 'Selesai' && $consultation->status !== 'Dibatalkan')
                <button onclick="document.getElementById('form-selesai').classList.toggle('hidden')" class="k-action-btn">
                    <i data-lucide="check-circle" style="width:16px;"></i> Catat Hasil Konsultasi
                </button>
                <div id="form-selesai" class="k-form-section hidden">
                    <form method="POST" action="{{ route('advokat.consultations.complete', $consultation->id) }}">
                        @csrf
                        <label>Ringkasan Hasil Konsultasi</label>
                        <textarea name="result_notes" rows="4" required placeholder="Catat hasil konsultasi...">{{ $consultation->result_notes }}</textarea>
                        <button type="submit" class="btn-submit">Simpan & Selesaikan</button>
                    </form>
                </div>
                @endif

                {{-- Buat Perkara --}}
                @if($consultation->status === 'Selesai' && !$consultation->case)
                <a href="{{ route('advokat.cases', ['from_consultation' => $consultation->id]) }}" class="k-action-btn buat-perkara">
                    <i data-lucide="plus" style="width:16px;"></i> Buat Perkara Baru
                </a>
                @endif
                @if($consultation->case)
                <a href="{{ route('advokat.cases.show', $consultation->case->id) }}" class="k-action-btn">
                    <i data-lucide="folder-open" style="width:16px;"></i> Lihat Perkara Terkait
                </a>
                @endif
            </div>
        </div>

        <div class="k-ringkasan">
            <div class="k-detail-card-title">Ringkasan Permasalahan</div>
            <p style="font-size:.875rem;color:#374151;line-height:1.6;">{{ $consultation->description }}</p>
        </div>

        @if($consultation->result_notes)
        <div class="k-ringkasan" style="border-color:#bbf7d0;background:#f0fdf4;">
            <div class="k-detail-card-title" style="color:#15803d;">Catatan Hasil Konsultasi</div>
            <p style="font-size:.875rem;color:#374151;line-height:1.6;">{{ $consultation->result_notes }}</p>
        </div>
        @endif

        @else
        <div class="k-detail-empty">
            <i data-lucide="message-square" style="width:48px;height:48px;"></i>
            <p>Pilih konsultasi untuk melihat detail.</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Re-init lucide icons for dynamically rendered elements
    document.querySelectorAll('[onclick]').forEach(btn => {
        btn.addEventListener('click', () => setTimeout(() => lucide.createIcons(), 100));
    });
</script>
@endpush
@endsection
