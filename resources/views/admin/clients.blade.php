@extends('layouts.admin')

@section('title', 'Data Klien')

@section('content')
<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari nama, email..." id="searchInput" oninput="filterTable(this.value)">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-outline"><i data-lucide="filter"></i> Filter</button>
        </div>
    </div>

    <div class="table-container">
        <table id="clientsTable">
            <thead>
                <tr>
                    <th>Nama Klien</th>
                    <th>Email / Telepon</th>
                    <th>Alamat</th>
                    <th>Jumlah Perkara</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                @php
                    $caseCount = \App\Models\LegalCase::where('client_id', $client->id)->count();
                    $badgeClass = $client->status === 'aktif' ? 'badge-success' : 'badge-danger';
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:#1e3a5f;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;">
                                {{ strtoupper(substr($client->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 500; color: var(--color-gray-dark);">{{ $client->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--color-gray-text);">ID: K-{{ str_pad($client->id, 3, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>{{ $client->email }}</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">{{ $client->clientProfile->phone ?? '—' }}</div>
                    </td>
                    <td style="font-size:.85rem;">{{ $client->clientProfile->address ?? '—' }}</td>
                    <td style="text-align:center;">{{ $caseCount }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ ucfirst($client->status) }}</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="eye" style="width: 14px;"></i> Detail</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--color-gray-text);">Tidak ada data klien.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
function filterTable(val) {
    const rows = document.querySelectorAll('#clientsTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val.toLowerCase()) ? '' : 'none';
    });
}
</script>
@endsection
