@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
    <!-- Settings Navigation -->
    <div class="card" style="padding: 0;">
        <div style="display: flex; flex-direction: column;">
            <a href="#" class="nav-item active" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-gray-border);">
                <i data-lucide="building"></i> Profil Kantor
            </a>
            <a href="#" class="nav-item" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-gray-border);">
                <i data-lucide="info"></i> Informasi Layanan
            </a>
            <a href="#" class="nav-item" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-gray-border);">
                <i data-lucide="bell"></i> Notifikasi
            </a>
            <a href="#" class="nav-item" style="padding: 1rem 1.5rem;">
                <i data-lucide="sliders"></i> Preferensi Umum
            </a>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="card">
        <h2 class="card-title">Profil Kantor</h2>
        
        <form style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="font-weight: 500; font-size: 0.875rem;">Nama Kantor Hukum</label>
                <input type="text" class="search-box" style="width: 100%; max-width: 500px;" value="Kantor Hukum Sahabat Hukum & Rekan">
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="font-weight: 500; font-size: 0.875rem;">Alamat Lengkap</label>
                <textarea class="search-box" style="width: 100%; max-width: 500px; height: 100px; padding: 0.75rem; font-family: inherit;">Gedung Cyber, Jl. Kuningan Barat No. 8, Jakarta Selatan</textarea>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="font-weight: 500; font-size: 0.875rem;">Nomor Telepon Utama</label>
                <input type="text" class="search-box" style="width: 100%; max-width: 500px;" value="021-12345678">
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="font-weight: 500; font-size: 0.875rem;">Email Kontak</label>
                <input type="email" class="search-box" style="width: 100%; max-width: 500px;" value="info@sahabathukum.com">
            </div>
            
            <div style="margin-top: 1rem; border-top: 1px solid var(--color-gray-border); padding-top: 1.5rem; display: flex; gap: 1rem;">
                <button type="button" class="btn btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn btn-outline">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection
