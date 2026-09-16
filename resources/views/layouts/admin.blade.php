<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sahabat Hukum - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon-box">
                    <i data-lucide="scale"></i>
                </div>
                <div class="brand-text-wrapper">
                    <span class="brand-name">SAHABAT HUKUM</span>
                    <span class="brand-subtitle">Sistem Informasi Manajemen Perkara</span>
                </div>
            </div>

            <div class="sidebar-role-banner">
                <span class="role-banner-label">Masuk Sebagai</span>
                <span class="role-banner-value">Admin</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i> Beranda
                </a>
                <a href="{{ route('admin.clients') }}" class="nav-item {{ request()->routeIs('admin.clients') ? 'active' : '' }}">
                    <i data-lucide="users"></i> Data Klien
                </a>
                <a href="{{ route('admin.lawyers') }}" class="nav-item {{ request()->routeIs('admin.lawyers') ? 'active' : '' }}">
                    <i data-lucide="briefcase"></i> Data Advokat
                </a>
                <a href="{{ route('admin.cases') }}" class="nav-item {{ request()->routeIs('admin.cases') ? 'active' : '' }}">
                    <i data-lucide="folder-open"></i> Data Perkara
                </a>
                <a href="{{ route('admin.consultations') }}" class="nav-item {{ request()->routeIs('admin.consultations') ? 'active' : '' }}">
                    <i data-lucide="message-square"></i> Data Konsultasi
                </a>
                <a href="{{ route('admin.documents') }}" class="nav-item {{ request()->routeIs('admin.documents') ? 'active' : '' }}">
                    <i data-lucide="file-text"></i> Dokumen
                </a>
                <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i data-lucide="user-cog"></i> Pengguna
                </a>
                <a href="{{ route('admin.knowledge') }}" class="nav-item {{ request()->routeIs('admin.knowledge') ? 'active' : '' }}">
                    <i data-lucide="book-open"></i> Basis Pengetahuan
                </a>
                <a href="{{ route('admin.reports') }}" class="nav-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-2"></i> Laporan
                </a>
                <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i data-lucide="settings"></i> Pengaturan
                </a>
            </nav>
            
            <div class="sidebar-profile-card">
                <div class="sidebar-profile-avatar" style="background-color: var(--color-gold);">
                    {{ substr(Auth::check() ? Auth::user()->name : 'Admin', 0, 1) }}
                </div>
                <div class="sidebar-profile-info">
                    <span class="sidebar-profile-name">{{ Auth::check() ? Auth::user()->name : 'Administrator' }}</span>
                    <span class="sidebar-profile-role">Admin</span>
                </div>
            </div>

            <div class="sidebar-logout-container">
                <form action="{{ route('logout') }}" method="POST" style="display: block; width: 100%;">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn">
                        <i data-lucide="log-out"></i> Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="main-content">
            <header class="header">
                <div class="header-title">
                    @yield('title', 'Admin Dashboard')
                </div>
                
                <div class="header-actions">
                    <button class="bell-button">
                        <i data-lucide="bell"></i>
                        <span class="bell-badge">2</span>
                    </button>
                    
                    <div class="header-profile-menu">
                        <div class="header-profile-avatar" style="background-color: var(--color-gold);">
                            {{ substr(Auth::check() ? Auth::user()->name : 'A', 0, 1) }}
                        </div>
                        <div class="header-profile-details">
                            <span class="header-profile-name">{{ Auth::check() ? Auth::user()->name : 'Administrator' }}</span>
                            <span class="header-profile-role">Admin</span>
                        </div>
                        <i data-lucide="chevron-down" style="width: 16px; margin-left: 4px;"></i>
                    </div>
                </div>
            </header>

            <div class="page-content">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
      lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
