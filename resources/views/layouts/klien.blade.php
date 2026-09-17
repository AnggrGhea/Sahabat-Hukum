<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beranda') — Sahabat Hukum</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/klien.css') }}">
    @stack('styles')
</head>
<body>
<div class="app-layout">

    {{-- ═══════════ SIDEBAR ═══════════ --}}
    <aside class="sidebar">

        {{-- Brand --}}
        <div class="sidebar-brand">
            <div class="brand-icon-box">
                <svg viewBox="0 0 24 24"><path d="M12 3L2 7l2 .8V18c0 .6.4 1 1 1h2v1h10v-1h2c.6 0 1-.4 1-1V7.8L22 7 12 3zm-2 14H6v-7.6l4 1.6V17zm8 0h-4v-6l4-1.6V17zM12 11.2L4.8 8.4 12 5.6l7.2 2.8L12 11.2z"/></svg>
            </div>
            <div class="brand-text">
                <div class="brand-name">Sahabat Hukum</div>
                <div class="brand-sub">Sistem Informasi Manajemen Perkara</div>
            </div>
        </div>

        {{-- Role Label --}}
        <div class="sidebar-role">
            <div class="role-label">Masuk Sebagai</div>
            <div class="role-name">Klien</div>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav">
            <a href="{{ route('klien.dashboard') }}"
               class="nav-item {{ request()->routeIs('klien.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Beranda
            </a>

            <a href="{{ route('klien.consultations') }}"
               class="nav-item {{ request()->routeIs('klien.consultations*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Konsultasi
            </a>

            <a href="{{ route('klien.cases') }}"
               class="nav-item {{ request()->routeIs('klien.cases*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
                Perkara Saya
            </a>

            <a href="{{ route('klien.documents') }}"
               class="nav-item {{ request()->routeIs('klien.documents*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
                Dokumen
            </a>

            <a href="{{ route('klien.schedule') }}"
               class="nav-item {{ request()->routeIs('klien.schedule*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Jadwal
            </a>

            <a href="{{ route('klien.chat') }}"
               class="nav-item {{ request()->routeIs('klien.chat*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Percakapan
            </a>

            <a href="#"
               class="nav-item {{ request()->routeIs('klien.notifications*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Pemberitahuan
                <span class="nav-badge">3</span>
            </a>

            <a href="{{ route('klien.assistant') }}"
               class="nav-item {{ request()->routeIs('klien.assistant*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07M8.46 8.46a5 5 0 0 0 0 7.07"/>
                </svg>
                Asisten Hukum
            </a>
        </nav>

        {{-- User Info + Logout --}}
        <div class="sidebar-user">
            <div class="sidebar-user-info">
                <div class="user-avatar-sm">{{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 1)) }}</div>
                <div>
                    <div class="user-name-sm">{{ auth()->user()->name ?? 'Klien' }}</div>
                    <div class="user-role-sm">Klien</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══════════ MAIN ═══════════ --}}
    <div class="main-wrapper">

        {{-- Top Header --}}
        <header class="app-header">
            <div class="header-left">@yield('header-title', 'Beranda')</div>
            <div class="header-right">
                <button class="notif-btn" title="Pemberitahuan">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    @php $unreadCount = auth()->check() ? auth()->user()->unreadNotifications->count() : 0; @endphp
                    @if($unreadCount > 0)
                    <span class="notif-badge">{{ $unreadCount }}</span>
                    @endif
                </button>
                <div class="header-user">
                    <div class="header-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 1)) }}</div>
                    <div class="header-user-info">
                        <div class="user-fullname">{{ auth()->user()->name ?? 'Klien' }}</div>
                        <div class="user-role">Klien</div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="page-body">
            @yield('content')
        </main>

    </div>{{-- end main-wrapper --}}
</div>{{-- end app-layout --}}

@stack('scripts')
</body>
</html>
