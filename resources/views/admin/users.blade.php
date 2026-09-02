@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari pengguna, email...">
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-outline">  
                <i data-lucide="filter"></i> Role
            </button>
            <button class="btn btn-primary">
                <i data-lucide="user-plus"></i> Tambah Admin
            </button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">Super Admin</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">Dibuat: 01 Jan 2026</div>
                    </td>
                    <td>admin@sahabathukum.com</td>
                    <td><span class="badge" style="background-color: var(--color-gray-dark); color: white;">Admin</span></td>
                    <td><span class="badge badge-success">Aktif</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;" disabled><i data-lucide="edit" style="width: 14px;"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">Andi Wijaya</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">Dibuat: 10 Jan 2026</div>
                    </td>
                    <td>andi.w@sahabathukum.com</td>
                    <td><span class="badge" style="background-color: var(--color-navy); color: white;">Advokat</span></td>
                    <td><span class="badge badge-success">Aktif</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="lock" style="width: 14px;"></i> Reset Sandi</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--color-gray-dark);">Budi Santoso</div>
                        <div style="font-size: 0.75rem; color: var(--color-gray-text);">Dibuat: 12 Agu 2026</div>
                    </td>
                    <td>budi.s@email.com</td>
                    <td><span class="badge" style="background-color: var(--color-gray-light); color: var(--color-gray-dark);">Klien</span></td>
                    <td><span class="badge badge-success">Aktif</span></td>
                    <td class="text-right">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"><i data-lucide="lock" style="width: 14px;"></i> Reset Sandi</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
