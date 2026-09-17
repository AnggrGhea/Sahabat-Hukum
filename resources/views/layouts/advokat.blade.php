<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sahabat Hukum - Advokat Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/css/admin.css">
    @stack('styles')
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
                <span class="role-banner-value">Advokat</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="{{ route('advokat.dashboard') }}" class="nav-item {{ request()->routeIs('advokat.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i> Beranda
                </a>
                <a href="{{ route('advokat.consultations') }}" class="nav-item {{ request()->routeIs('advokat.consultations*') ? 'active' : '' }}">
                    <i data-lucide="message-square"></i> Konsultasi
                </a>
                <a href="{{ route('advokat.cases') }}" class="nav-item {{ request()->routeIs('advokat.cases*') ? 'active' : '' }}">
                    <i data-lucide="folder-open"></i> Perkara
                </a>
                <a href="{{ route('advokat.clients') }}" class="nav-item {{ request()->routeIs('advokat.clients*') ? 'active' : '' }}">
                    <i data-lucide="users"></i> Klien
                </a>
                <a href="{{ route('advokat.assistant') }}" class="nav-item {{ request()->routeIs('advokat.assistant*') ? 'active' : '' }}">
                    <i data-lucide="scale"></i> Asisten Hukum
                </a>
                <a href="{{ route('advokat.documents') }}" class="nav-item {{ request()->routeIs('advokat.documents*') ? 'active' : '' }}">
                    <i data-lucide="file-text"></i> Dokumen
                </a>
                <a href="{{ route('advokat.schedule') }}" class="nav-item {{ request()->routeIs('advokat.schedule*') ? 'active' : '' }}">
                    <i data-lucide="calendar"></i> Jadwal
                </a>
                <a href="{{ route('advokat.chat') }}" class="nav-item {{ request()->routeIs('advokat.chat*') ? 'active' : '' }}">
                    <i data-lucide="message-circle"></i> Percakapan
                </a>
                <a href="#" class="nav-item">
                    <i data-lucide="bell"></i> Pemberitahuan <span class="nav-item-badge">4</span>
                </a>
            </nav>
            
            <div class="sidebar-profile-card">
                <div class="sidebar-profile-avatar">
                    {{ substr(Auth::check() ? Auth::user()->name : 'Advokat', 0, 1) }}
                </div>
                <div class="sidebar-profile-info">
                    <span class="sidebar-profile-name">{{ Auth::check() ? Auth::user()->name : 'Advokat' }}</span>
                    <span class="sidebar-profile-role">Advokat</span>
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
                    @yield('title', 'Advokat Dashboard')
                </div>
                
                <div class="header-actions">
                    @php $unreadCountAdv = Auth::check() ? Auth::user()->unreadNotifications->count() : 0; @endphp
                    <button class="bell-button" title="Pemberitahuan">
                        <i data-lucide="bell"></i>
                        @if($unreadCountAdv > 0)
                        <span class="bell-badge">{{ $unreadCountAdv }}</span>
                        @endif
                    </button>
                    
                    <div class="header-profile-menu">
                        <div class="header-profile-avatar">
                            {{ substr(Auth::check() ? Auth::user()->name : 'A', 0, 1) }}
                        </div>
                        <div class="header-profile-details">
                            <span class="header-profile-name">{{ Auth::check() ? Auth::user()->name : 'Advokat' }}</span>
                            <span class="header-profile-role">Advokat</span>
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
