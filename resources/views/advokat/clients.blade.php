@extends('layouts.advokat')

@section('title', 'Klien')

@section('content')
<style>
.klien-layout { display: grid; grid-template-columns: 340px 1fr; gap: 0; height: calc(100vh - 80px); border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; }
.kl-list-panel { border-right: 1px solid #e2e8f0; overflow-y: auto; background: #fff; display: flex; flex-direction: column; }
.kl-list-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; }
.kl-list-title { font-size: 1rem; font-weight: 600; color: #1e293b; margin-bottom: 10px; }
.kl-search { width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .85rem; box-sizing: border-box; margin-bottom: 8px; }
.kl-filter-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.kl-tab { padding: 4px 12px; border-radius: 20px; font-size: .75rem; font-weight: 500; cursor: pointer; text-decoration: none; border: 1px solid #e2e8f0; color: #64748b; background: #f8fafc; }
.kl-tab.active { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
.kl-count { font-size: .75rem; color: #94a3b8; padding: 8px 20px; border-bottom: 1px solid #f1f5f9; }
.kl-item { display: flex; align-items: center; gap: 12px; padding: 14px 20px; cursor: pointer; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: background .15s; }
.kl-item:hover { background: #f8fafc; }
.kl-item.active-item { background: #eff6ff; border-left: 3px solid #1e3a5f; }
.kl-avatar { width: 40px; height: 40px; border-radius: 50%; background: #1e3a5f; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .95rem; flex-shrink: 0; }
.kl-item-info { flex: 1; min-width: 0; }
.kl-item-name { font-size: .875rem; font-weight: 600; color: #1e293b; }
.kl-item-sub { font-size: .75rem; color: #64748b; margin-top: 2px; }
/* Detail */
.kl-detail-panel { overflow-y: auto; background: #f8fafc; }
.kl-detail-empty { display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; gap: 12px; color: #94a3b8; }
.kl-detail-hero { background: #1e3a5f; padding: 24px; display: flex; align-items: center; justify-content: space-between; }
.kl-hero-left { display: flex; align-items: center; gap: 16px; }
.kl-hero-avatar { width: 52px; height: 52px; border-radius: 50%; background: rgba(255,255,255,.2); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; flex-shrink: 0; }
.kl-hero-name { font-size: 1.1rem; font-weight: 700; color: #fff; }
.kl-hero-meta { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
.kl-hero-badge { font-size: .72rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; background: rgba(255,255,255,.2); color: #fff; }
.kl-hero-date { font-size: .75rem; color: rgba(255,255,255,.75); }
.kl-hero-actions { display: flex; gap: 8px; }
.btn-pesan { padding: 8px 16px; background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.3); border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-lihat-perkara { padding: 8px 16px; background: #fff; color: #1e3a5f; border: none; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.kl-detail-body { padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.kl-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
.kl-card-title { font-size: .75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 14px; }
.kl-field { margin-bottom: 12px; display: flex; align-items: flex-start; gap: 10px; }
.kl-field-icon { width: 18px; height: 18px; color: #94a3b8; flex-shrink: 0; margin-top: 1px; }
.kl-field-inner label { font-size: .72rem; color: #94a3b8; display: block; }
.kl-field-inner span { font-size: .875rem; font-weight: 500; color: #1e293b; }
.kl-summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.kl-summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; text-align: center; }
.kl-summary-num { font-size: 1.5rem; font-weight: 700; color: #1e3a5f; }
.kl-summary-label { font-size: .72rem; color: #64748b; margin-top: 2px; }
.kl-perkara-item { padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.kl-perkara-num { font-size: .78rem; font-weight: 700; color: #1e3a5f; }
.kl-perkara-title { font-size: .85rem; font-weight: 500; color: #1e293b; }
.kl-perkara-status { font-size: .72rem; }
</style>

<div class="klien-layout">
    {{-- LEFT: Client List --}}
    <div class="kl-list-panel">
        <div class="kl-list-header">
            <div class="kl-list-title">Klien Saya</div>
            <form method="GET" action="{{ route('advokat.clients') }}">
                <input type="text" name="search" class="kl-search" placeholder="Cari nama klien..." value="{{ $search }}">
            </form>
            <div class="kl-filter-tabs">
                @foreach(['Semua','Aktif','Baru','Konsultasi','Selesai'] as $tab)
                <a href="{{ route('advokat.clients', ['filter' => $tab]) }}"
                   class="kl-tab {{ $filter === $tab ? 'active' : '' }}">{{ $tab }}</a>
                @endforeach
            </div>
        </div>

        <div class="kl-count">{{ $clients->count() }} dari {{ $clients->count() }} klien</div>

        @forelse($clients as $cl)
        @php
            $activeCases = \App\Models\LegalCase::where('client_id', $cl->id)
                ->where('lawyer_id', Auth::id())
                ->whereNotIn('status', ['Selesai','Dibatalkan'])->count();
            $lastConsult = \App\Models\Consultation::where('client_id', $cl->id)
                ->where('lawyer_id', Auth::id())->latest()->first();
            $isActive = $client && $client->id === $cl->id;
        @endphp
        <a href="{{ route('advokat.clients.show', $cl->id) }}" class="kl-item {{ $isActive ? 'active-item' : '' }}">
            <div class="kl-avatar">{{ strtoupper(substr($cl->name, 0, 1)) }}</div>
            <div class="kl-item-info">
                <div class="kl-item-name">{{ $cl->name }}</div>
                <div class="kl-item-sub">{{ $activeCases }} perkara aktif</div>
                @if($lastConsult)
                <div class="kl-item-sub">Konsultasi: {{ \Carbon\Carbon::parse($lastConsult->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</div>
                @endif
            </div>
            @if($activeCases > 0)
            <span class="status-badge badge-dijadwalkan">Aktif</span>
            @else
            <span class="status-badge badge-selesai">Selesai</span>
            @endif
        </a>
        @empty
        <div style="text-align:center;padding:3rem;color:#94a3b8;font-size:.875rem;">Tidak ada klien ditemukan.</div>
        @endforelse
    </div>

    {{-- RIGHT: Client Detail --}}
    <div class="kl-detail-panel">
        @if($client)
        <div class="kl-detail-hero">
            <div class="kl-hero-left">
                <div class="kl-hero-avatar">{{ strtoupper(substr($client->name, 0, 1)) }}</div>
                <div>
                    <div class="kl-hero-name">{{ $client->name }}</div>
                    <div class="kl-hero-meta">
                        <span class="kl-hero-badge">{{ $client->activeCases > 0 ? 'Aktif' : 'Selesai' }}</span>
                        <span class="kl-hero-date">Terdaftar: {{ \Carbon\Carbon::parse($client->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</span>
                    </div>
                </div>
            </div>
            <div class="kl-hero-actions">
                <a href="#" class="btn-pesan"><i data-lucide="message-circle" style="width:14px;"></i> Pesan</a>
                <a href="{{ route('advokat.cases', ['client_id' => $client->id]) }}" class="btn-lihat-perkara"><i data-lucide="folder-open" style="width:14px;"></i> Lihat Perkara</a>
            </div>
        </div>

        <div class="kl-detail-body">
            {{-- Informasi Klien --}}
            <div class="kl-card">
                <div class="kl-card-title">Informasi Klien</div>
                <div class="kl-field">
                    <i data-lucide="mail" class="kl-field-icon"></i>
                    <div class="kl-field-inner">
                        <label>Email</label>
                        <span>{{ $client->email }}</span>
                    </div>
                </div>
                <div class="kl-field">
                    <i data-lucide="phone" class="kl-field-icon"></i>
                    <div class="kl-field-inner">
                        <label>Telepon</label>
                        <span>{{ $client->clientProfile->phone ?? '—' }}</span>
                    </div>
                </div>
                <div class="kl-field">
                    <i data-lucide="calendar" class="kl-field-icon"></i>
                    <div class="kl-field-inner">
                        <label>Tanggal Bergabung</label>
                        <span>{{ \Carbon\Carbon::parse($client->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</span>
                    </div>
                </div>
                @if($client->lastConsultation)
                <div class="kl-field">
                    <i data-lucide="message-square" class="kl-field-icon"></i>
                    <div class="kl-field-inner">
                        <label>Konsultasi Terakhir</label>
                        <span>{{ \Carbon\Carbon::parse($client->lastConsultation->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</span>
                    </div>
                </div>
                @endif
                @if($client->clientProfile?->address)
                <div class="kl-field">
                    <i data-lucide="map-pin" class="kl-field-icon"></i>
                    <div class="kl-field-inner">
                        <label>Alamat</label>
                        <span>{{ $client->clientProfile->address }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Ringkasan --}}
            <div>
                <div class="kl-card" style="margin-bottom:16px;">
                    <div class="kl-card-title">Ringkasan</div>
                    <div class="kl-summary-grid">
                        <div class="kl-summary-box">
                            <div class="kl-summary-num">{{ $client->totalCases }}</div>
                            <div class="kl-summary-label">Total Perkara</div>
                        </div>
                        <div class="kl-summary-box" style="border-color:#bae6fd;background:#f0f9ff;">
                            <div class="kl-summary-num" style="color:#0369a1;">{{ $client->activeCases }}</div>
                            <div class="kl-summary-label">Perkara Aktif</div>
                        </div>
                    </div>
                </div>

                {{-- Perkara List --}}
                <div class="kl-card">
                    <div class="kl-card-title">Perkara</div>
                    @forelse($client->caseList as $perkara)
                    @php
                        $pBadge = match($perkara->status) {
                            'Persidangan','Penyidikan' => 'badge-dijadwalkan',
                            'Selesai' => 'badge-selesai',
                            default => 'badge-menunggu',
                        };
                    @endphp
                    <div class="kl-perkara-item">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                            <div>
                                <div class="kl-perkara-num">{{ $perkara->case_number }}</div>
                                <div class="kl-perkara-title">{{ $perkara->title }}</div>
                                <div class="kl-perkara-status" style="color:#64748b;">{{ $perkara->case_type }}</div>
                            </div>
                            <span class="status-badge {{ $pBadge }}">{{ $perkara->status }}</span>
                        </div>
                    </div>
                    @empty
                    <div style="color:#94a3b8;font-size:.85rem;">Belum ada perkara.</div>
                    @endforelse
                </div>
            </div>
        </div>

        @else
        <div class="kl-detail-empty">
            <i data-lucide="users" style="width:48px;height:48px;"></i>
            <p>Pilih klien untuk melihat detail.</p>
        </div>
        @endif
    </div>
</div>

<style>
/* Reuse badge styles from consultations */
.status-badge { font-size: .7rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
.badge-menunggu { background: #fef3c7; color: #d97706; }
.badge-dijadwalkan { background: #dbeafe; color: #2563eb; }
.badge-selesai { background: #dcfce7; color: #16a34a; }
.badge-dibatalkan { background: #fee2e2; color: #dc2626; }
</style>
@endsection
