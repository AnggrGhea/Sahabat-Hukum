@extends('layouts.admin')

@section('title', 'Data Advokat')

@section('content')
<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari nama, spesialisasi...">
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-outline">
                <i data-lucide="filter"></i> Filter
            </button>
            <button class="btn btn-primary">
                <i data-lucide="user-plus"></i> Tambah Advokat
            </button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Advokat</th>
                    <th>Spesialisasi</th>
                    <th>Kontak</th>
                    <th>Perkara Aktif</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">Andi Wijaya, S.H., M.H.</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">ID: A-001</div>
                    </td>
                    <td>Hukum Pidana, Hukum Perusahaan</td>
                    <td>
                        <div>andi.w@sahabathukum.com</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">0811-2233-4455</div>
                    </td>
                    <td>5</td>
                    <td><span class="badge badge-success">Aktif</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="eye" style="width: 14px;"></i> Detail</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">Siti Aminah, S.H.</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">ID: A-002</div>
                    </td>
                    <td>Hukum Keluarga, Perdata</td>
                    <td>
                        <div>siti.a@sahabathukum.com</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">0811-5566-7788</div>
                    </td>
                    <td>3</td>
                    <td><span class="badge badge-success">Aktif</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="eye" style="width: 14px;"></i> Detail</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
