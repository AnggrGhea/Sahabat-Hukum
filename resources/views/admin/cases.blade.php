@extends('layouts.admin')

@section('title', 'Data Perkara')

@section('content')
<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari nomor perkara, klien..." id="searchInput" oninput="filterTable(this.value)">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-outline"><i data-lucide="filter"></i> Filter</button>
        </div>
    </div>

    <div class="table-container">
        <table id="casesTable">
            <thead>
                <tr>
                    <th>No Perkara</th>
                    <th>Klien</th>
                    <th>Advokat</th>
                    <th>Jenis / Mulai</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cases as $case)
                @php
                    $badgeClass = match($case->status) {
                        'Selesai'     => 'badge-success',
                        'Dibatalkan'  => 'badge-danger',
                        'Persidangan','Penyidikan' => 'badge-info',
                        default       => 'badge-warning',
                    };
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">{{ $case->case_number }}</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">{{ $case->title }}</div>
                    </td>
                    <td>{{ $case->client->name }}</td>
                    <td>{{ $case->lawyer->name }}</td>
                    <td>
                        <div>{{ $case->case_type }}</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">
                            {{ $case->started_at ? \Carbon\Carbon::parse($case->started_at)->locale('id')->isoFormat('D MMM YYYY') : '—' }}
                        </div>
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $case->status }}</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                            <i data-lucide="eye" style="width: 14px;"></i> Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--color-gray-text);">Tidak ada data perkara.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
function filterTable(val) {
    const rows = document.querySelectorAll('#casesTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val.toLowerCase()) ? '' : 'none';
    });
}
</script>
@endsection
