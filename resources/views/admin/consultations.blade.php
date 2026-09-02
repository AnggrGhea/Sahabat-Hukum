@extends('layouts.admin')

@section('title', 'Data Konsultasi')

@section('content')
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
                </tr>
            </thead>
            <tbody>
                @forelse($consultations as $c)
                @php
                    $badgeClass = match($c->status) {
                        'Selesai'     => 'badge-success',
                        'Dijadwalkan' => 'badge-info',
                        'Dibatalkan'  => 'badge-danger',
                        default       => 'badge-warning',
                    };
                @endphp
                <tr>
                    <td style="font-weight:500;">{{ $c->title }}</td>
                    <td>{{ $c->client->name }}</td>
                    <td>{{ $c->lawyer?->name ?? '—' }}</td>
                    <td>{{ $c->problem_type }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->created_at)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                    <td>{{ $c->scheduled_at ? \Carbon\Carbon::parse($c->scheduled_at)->locale('id')->isoFormat('D MMM YYYY, HH.mm') : '—' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $c->status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:2rem;color:var(--color-gray-text);">Tidak ada data konsultasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
function filterTable(val) {
    const rows = document.querySelectorAll('#consultTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val.toLowerCase()) ? '' : 'none';
    });
}
</script>
@endsection
