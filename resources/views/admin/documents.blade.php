@extends('layouts.admin')

@section('title', 'Dokumen')

@section('content')
<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari nama dokumen, klien...">
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-outline">
                <i data-lucide="filter"></i> Filter
            </button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Dokumen</th>
                    <th>Klien</th>
                    <th>Perkara</th>
                    <th>Advokat</th>
                    <th>Tanggal Unggah</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">Bukti Transfer Pembayaran</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">PDF - 2.5 MB</div>
                    </td>
                    <td>Budi Santoso</td>
                    <td>PRK-2026-001</td>
                    <td>Andi Wijaya, S.H.</td>
                    <td>17 Agu 2026</td>
                    <td><span class="badge badge-warning">Menunggu Pemeriksaan</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="download" style="width: 14px;"></i> Unduh</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">Sertifikat Tanah</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">Diminta</div>
                    </td>
                    <td>PT Maju Bersama</td>
                    <td>-</td>
                    <td>Andi Wijaya, S.H.</td>
                    <td>-</td>
                    <td><span class="badge" style="background-color: var(--color-gray-light); color: var(--color-gray-text);">Belum Diunggah</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="eye" style="width: 14px;"></i> Detail</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
