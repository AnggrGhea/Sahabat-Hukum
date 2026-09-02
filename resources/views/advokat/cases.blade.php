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
.btn-tambah-perkembangan { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #1e3a5f; color: #fff; border: none; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; }
.btn-minta-dokumen { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #fff; color: #374151; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; }
.p-detail-body { padding: 20px 24px; display: grid; grid-template-columns: 1fr 280px; gap: 16px; }
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
/* Documents */
.doc-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.doc-name { font-size: .85rem; font-weight: 500; color: #1e293b; }
.doc-status { font-size: .72rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
.doc-diterima { background: #dcfce7; color: #16a34a; }
.doc-belum { background: #f1f5f9; color: #64748b; }
.doc-menunggu { background: #fef9c3; color: #ca8a04; }
.doc-perbaiki { background: #fee2e2; color: #dc2626; }
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
                <button onclick="document.getElementById('form-progress').classList.toggle('hidden')" class="btn-tambah-perkembangan">
                    <i data-lucide="plus" style="width:14px;"></i> Tambah Perkembangan
                </button>
                <button onclick="document.getElementById('form-dokumen').classList.toggle('hidden')" class="btn-minta-dokumen">
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

                {{-- Request Document Form --}}
                <div id="form-dokumen" class="form-section hidden" style="background:#fff7ed;border-color:#fed7aa;">
                    <strong style="font-size:.85rem;font-weight:600;color:#c2410c;">Minta Dokumen</strong>
                    <form method="POST" action="{{ route('advokat.cases.document', $case->id) }}">
                        @csrf
                        <label>Nama Dokumen</label>
                        <input type="text" name="name" required placeholder="Mis: Sertifikat Tanah">
                        <label>Keterangan</label>
                        <textarea name="description" rows="2" placeholder="Keterangan tambahan..."></textarea>
                        <label>Batas Waktu</label>
                        <input type="date" name="due_date">
                        <label>Tingkat Kepentingan</label>
                        <select name="priority">
                            <option value="Normal">Normal</option>
                            <option value="Tinggi">Tinggi</option>
                        </select>
                        <button type="submit" class="btn-submit">Kirim Permintaan</button>
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

            {{-- RIGHT: Documents --}}
            <div>
                <div class="p-info-card">
                    <div class="p-card-title">Dokumen <a href="#" onclick="document.getElementById('form-dokumen').classList.remove('hidden');return false;" style="float:right;font-size:.72rem;color:#2563eb;text-decoration:none;">+ Minta</a></div>
                    @forelse($case->documents as $doc)
                    @php
                        $docBadge = match($doc->status) {
                            'Sudah Diterima'       => 'doc-diterima',
                            'Menunggu Pemeriksaan' => 'doc-menunggu',
                            'Perlu Diperbaiki'     => 'doc-perbaiki',
                            default                => 'doc-belum',
                        };
                    @endphp
                    <div class="doc-item">
                        <div>
                            <div class="doc-name">{{ $doc->name }}</div>
                            <div style="font-size:.72rem;color:#94a3b8;">{{ $doc->priority }}</div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                            <span class="doc-status {{ $docBadge }}">{{ $doc->status }}</span>
                            @if($doc->status === 'Menunggu Pemeriksaan')
                            <form method="POST" action="{{ route('advokat.documents.verify', $doc->id) }}" style="display:inline;">
                                @csrf
                                <select name="status" onchange="this.form.submit()" style="font-size:.7rem;padding:2px 4px;border:1px solid #d1d5db;border-radius:4px;">
                                    <option value="">Verifikasi</option>
                                    <option value="Sudah Diterima">Terima</option>
                                    <option value="Perlu Diperbaiki">Perlu Diperbaiki</option>
                                </select>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div style="color:#94a3b8;font-size:.85rem;padding:8px 0;">Belum ada dokumen diminta.</div>
                    @endforelse
                </div>
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
</script>
@endpush
@endsection
