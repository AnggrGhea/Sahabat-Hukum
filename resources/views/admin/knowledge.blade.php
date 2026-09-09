@extends('layouts.admin')

@section('title', 'Basis Pengetahuan')

@section('content')
<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari sumber pengetahuan...">
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-outline">
                <i data-lucide="filter"></i> Filter
            </button>
            <button class="btn btn-primary">
                <i data-lucide="plus"></i> Tambah Sumber
            </button>
            <button class="btn btn-primary" style="background-color: var(--color-navy-dark);">
                <i data-lucide="upload"></i> Unggah Dokumen
            </button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Judul Sumber</th>
                    <th>Kategori</th>
                    <th>Terakhir Diperbarui</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sources as $src)
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">{{ $src->title }}</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">{{ Str::limit($src->description ?? $src->source_type, 60) }}</div>
                    </td>
                    <td>{{ $src->source_type }}</td>
                    <td>{{ $src->updated_at ? \Carbon\Carbon::parse($src->updated_at)->locale('id')->isoFormat('D MMM YYYY') : '—' }}</td>
                    <td><span class="badge {{ $src->status === 'Aktif' ? 'badge-success' : 'badge-danger' }}">{{ $src->status }}</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="eye" style="width: 14px;"></i> Lihat</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:2rem;color:var(--color-gray-text);">Belum ada data sumber pengetahuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table-container tbody tr');
            rows.forEach(r => {
                r.style.display = r.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection
