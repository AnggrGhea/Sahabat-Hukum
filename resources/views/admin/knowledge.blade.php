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
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">Panduan Layanan Konsultasi</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">Teks / Artikel</div>
                    </td>
                    <td>Prosedur Layanan</td>
                    <td>10 Agu 2026</td>
                    <td><span class="badge badge-success">Aktif</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="edit" style="width: 14px;"></i> Edit</button>
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; color: var(--color-red); border-color: #fca5a5;"><i data-lucide="trash-2" style="width: 14px;"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">UU No. 1 Tahun 1974 tentang Perkawinan</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">PDF - 1.2 MB</div>
                    </td>
                    <td>Referensi Peraturan</td>
                    <td>12 Agu 2026</td>
                    <td><span class="badge badge-success">Aktif</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="edit" style="width: 14px;"></i> Edit</button>
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; color: var(--color-red); border-color: #fca5a5;"><i data-lucide="trash-2" style="width: 14px;"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
