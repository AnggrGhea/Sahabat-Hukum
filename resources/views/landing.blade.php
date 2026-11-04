<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sahabat Hukum - Sistem Manajemen Perkara & Layanan Konsultasi Hukum</title>
    <meta name="description" content="Sahabat Hukum — Pendampingan hukum profesional, terpercaya, dan terstruktur. Layanan konsultasi hukum, manajemen perkara, dan koordinasi advokat terintegrasi.">
    <meta name="keywords" content="kantor hukum, advokat, pengacara, konsultasi hukum, manajemen perkara, sahabat hukum, bantuan hukum indonesia">
    <meta name="author" content="Sahabat Hukum">

    <!-- Modern Sans-Serif Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom Modern Landing CSS -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body id="beranda">

    <!-- ====================================================================
         1. NAVBAR
         ==================================================================== -->
    <header class="site-navbar" id="siteNavbar">
        <div class="container navbar-inner">
            <a href="{{ url('/') }}" class="navbar-brand">
                <div class="brand-icon">
                    <i data-lucide="scale" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <div class="brand-title">Sahabat Hukum</div>
                    <div class="brand-desc">Law Office &amp; Legal Tech</div>
                </div>
            </a>

            <!-- Desktop Nav Menu -->
            <ul class="navbar-nav">
                <li><a href="#beranda" class="nav-link active">Beranda</a></li>
                <li><a href="#tentang" class="nav-link">Tentang Kami</a></li>
                <li><a href="#layanan" class="nav-link">Layanan</a></li>
                <li><a href="#advokat" class="nav-link">Tim Advokat</a></li>
                <li><a href="#struktur" class="nav-link">Struktur Organisasi</a></li>
                <li><a href="#kontak" class="nav-link">Kontak</a></li>
            </ul>

            <!-- Navbar Actions -->
            <div class="navbar-auth">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">
                            <i data-lucide="layout-dashboard" style="width: 15px; height: 15px;"></i> Dashboard Admin
                        </a>
                    @elseif(auth()->user()->isAdvokat())
                        <a href="{{ route('advokat.dashboard') }}" class="btn btn-primary btn-sm">
                            <i data-lucide="layout-dashboard" style="width: 15px; height: 15px;"></i> Dashboard Advokat
                        </a>
                    @else
                        <a href="{{ route('klien.dashboard') }}" class="btn btn-primary btn-sm">
                            <i data-lucide="layout-dashboard" style="width: 15px; height: 15px;"></i> Dashboard Klien
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-gold btn-sm">Daftar</a>
                @endauth
            </div>

            <!-- Hamburger Button for Mobile -->
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation menu">
                <i data-lucide="menu" style="width: 24px; height: 24px;"></i>
            </button>
        </div>

        <!-- Mobile Nav Drawer -->
        <nav class="mobile-nav" id="mobileNav">
            <ul class="mobile-nav-list">
                <li><a href="#beranda" class="mobile-nav-link">Beranda</a></li>
                <li><a href="#tentang" class="mobile-nav-link">Tentang Kami</a></li>
                <li><a href="#layanan" class="mobile-nav-link">Layanan</a></li>
                <li><a href="#advokat" class="mobile-nav-link">Tim Advokat</a></li>
                <li><a href="#struktur" class="mobile-nav-link">Struktur Organisasi</a></li>
                <li><a href="#kontak" class="mobile-nav-link">Kontak</a></li>
            </ul>
            <div class="mobile-auth-actions">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">Dashboard Admin</a>
                    @elseif(auth()->user()->isAdvokat())
                        <a href="{{ route('advokat.dashboard') }}" class="btn btn-primary btn-sm">Dashboard Advokat</a>
                    @else
                        <a href="{{ route('klien.dashboard') }}" class="btn btn-primary btn-sm">Dashboard Klien</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-gold btn-sm">Daftar</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        <!-- ====================================================================
             2. HERO SECTION (Smooth Fade Background Slideshow & Modern Sans-Serif)
             ==================================================================== -->
        <section class="hero-section" id="heroSection">
            <!-- Full-atmospheric Background Slideshow with Pure Fade Transition -->
            <div class="hero-slideshow" id="heroSlideshow">
                <div class="hero-slide active" data-caption="Ruang Sidang &amp; Konsultasi Utama">
                    <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1800&q=85"
                         alt="Ruang Rapat &amp; Kantor Hukum Utama"
                         onerror="this.src='{{ asset('images/landing/hero-slide-1.svg') }}'">
                </div>
                <div class="hero-slide" data-caption="Konsultasi Advokat Bersama Klien">
                    <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=1800&q=85"
                         alt="Konsultasi Advokat dengan Klien"
                         onerror="this.src='{{ asset('images/landing/hero-slide-2.svg') }}'">
                </div>
                <div class="hero-slide" data-caption="Rapat Koordinasi &amp; Penanganan Perkara">
                    <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1800&q=85"
                         alt="Rapat Tim Advokat dan Kolaborasi"
                         onerror="this.src='{{ asset('images/landing/hero-slide-3.svg') }}'">
                </div>
                <div class="hero-slide" data-caption="Penelaahan Dokumen &amp; Berkas Perkara">
                    <img src="https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=1800&q=85"
                         alt="Pemeriksaan Berkas dan Surat Kuasa"
                         onerror="this.src='{{ asset('images/landing/hero-slide-4.svg') }}'">
                </div>
            </div>

            <!-- Consistent Dark Navy Gradient Overlay -->
            <div class="hero-overlay"></div>
            <div class="hero-bottom-fade"></div>

            <!-- Minimalist Floating Side Navigation Arrows (Discreet on desktop) -->
            <button type="button" class="hero-arrow-btn hero-arrow-prev" id="heroPrevBtn" aria-label="Slide sebelumnya">
                <i data-lucide="chevron-left" style="width: 20px; height: 20px;"></i>
            </button>
            <button type="button" class="hero-arrow-btn hero-arrow-next" id="heroNextBtn" aria-label="Slide berikutnya">
                <i data-lucide="chevron-right" style="width: 20px; height: 20px;"></i>
            </button>

            <!-- Minimalist Slide Indicators (Small elegant dots at bottom center) -->
            <div class="hero-bottom-indicators" id="heroIndicators" aria-label="Navigasi slide foto">
                <button type="button" class="hero-dot active" data-slide="0" aria-label="Slide 1"></button>
                <button type="button" class="hero-dot" data-slide="1" aria-label="Slide 2"></button>
                <button type="button" class="hero-dot" data-slide="2" aria-label="Slide 3"></button>
                <button type="button" class="hero-dot" data-slide="3" aria-label="Slide 4"></button>
            </div>

            <div class="container hero-container">
                <div class="hero-grid">
                    <!-- Left: Editorial Content -->
                    <div class="hero-content">
                        <div class="hero-badge">
                            <i data-lucide="shield-check" style="width: 15px; height: 15px;"></i>
                            <span>Firma Hukum Resmi &amp; Terakreditasi</span>
                        </div>
                        <h1 class="hero-title">
                            Pendampingan Hukum yang <span class="highlight">Profesional</span>, Terpercaya, dan Terstruktur
                        </h1>
                        <p class="hero-description">
                            Sahabat Hukum membantu klien memperoleh layanan konsultasi, pendampingan perkara, pengelolaan dokumen, serta komunikasi dengan advokat melalui satu platform yang terintegrasi.
                        </p>
                        <div class="hero-actions">
                            <a href="{{ auth()->check() ? (auth()->user()->isKlien() ? route('klien.consultations') : route('login')) : route('login') }}" class="btn btn-gold btn-lg">
                                <i data-lucide="file-text" style="width: 17px; height: 17px;"></i> Ajukan Konsultasi
                            </a>
                            <a href="#layanan" class="btn btn-secondary btn-lg">
                                <i data-lucide="arrow-down" style="width: 17px; height: 17px;"></i> Pelajari Layanan
                            </a>
                        </div>
                        <div class="hero-stats-row">
                            <div class="hero-stat-item">
                                <div class="stat-num">100%</div>
                                <div class="stat-label">Advokat Berlisensi Resmi</div>
                            </div>
                            <div class="hero-stat-item">
                                <div class="stat-num">Terstruktur</div>
                                <div class="stat-label">Manajemen Perkara &amp; Arsip</div>
                            </div>
                            <div class="hero-stat-item">
                                <div class="stat-num">Aman</div>
                                <div class="stat-label">Kerahasiaan Dokumen Terjamin</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Floating Credential Card -->
                    <div class="hero-visual-column">
                        <div class="hero-credential-card">
                            <div class="hcc-header">
                                <div class="hcc-org">
                                    <div class="hcc-org-avatar">
                                        <i data-lucide="scale" style="width: 20px; height: 20px;"></i>
                                    </div>
                                    <div class="hcc-org-meta">
                                        <h4>Firma Hukum Sahabat Hukum</h4>
                                        <p>Advokat &amp; Penasihat Hukum Indonesia</p>
                                    </div>
                                </div>
                                <span class="hcc-badge">
                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i> Terverifikasi
                                </span>
                            </div>

                            <ul class="hcc-features">
                                <li class="hcc-feature-item">
                                    <i data-lucide="check-circle-2" style="width: 17px; height: 17px;"></i>
                                    <span>Konsultasi hukum pidana, perdata, dan ketenagakerjaan</span>
                                </li>
                                <li class="hcc-feature-item">
                                    <i data-lucide="check-circle-2" style="width: 17px; height: 17px;"></i>
                                    <span>Penyusunan berkas &amp; penelaahan legalitas dokumen</span>
                                </li>
                                <li class="hcc-feature-item">
                                    <i data-lucide="check-circle-2" style="width: 17px; height: 17px;"></i>
                                    <span>Pendampingan litigasi pengadilan &amp; negosiasi non-litigasi</span>
                                </li>
                                <li class="hcc-feature-item">
                                    <i data-lucide="check-circle-2" style="width: 17px; height: 17px;"></i>
                                    <span>Pembaruan progres perkara berkala bagi klien</span>
                                </li>
                            </ul>

                            <div class="hcc-footer">
                                <div class="hcc-status">
                                    <span class="status-dot"></span>
                                    <span>Kantor Buka &amp; Layanan Siap Tanggap</span>
                                </div>
                                <span style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.5);">Senin - Jumat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================================================================
             3. TENTANG KAMI (Side-by-side)
             ==================================================================== -->
        <section class="about-section" id="tentang">
            <div class="container">
                <div class="about-grid">
                    <!-- Left: Framed Law Firm Photograph -->
                    <div class="about-image-column reveal-on-scroll">
                        <div class="about-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1505664194779-8beaceb93744?auto=format&fit=crop&w=1000&q=80"
                                 alt="Kantor Hukum dan Perpustakaan Sahabat Hukum"
                                 onerror="this.src='{{ asset('images/landing/about-law-office.svg') }}'">
                        </div>
                        <div class="about-seal-badge">
                            <div class="about-seal-icon">
                                <i data-lucide="award" style="width: 22px; height: 22px;"></i>
                            </div>
                            <div class="about-seal-meta">
                                <h5>Integritas Teruji</h5>
                                <p>Firma Hukum Terakreditasi Nasional</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Content & Highlights -->
                    <div class="about-content reveal-on-scroll">
                        <div class="section-tag">
                            <i data-lucide="award" style="width: 14px; height: 14px;"></i>
                            <span>Tentang Sahabat Hukum</span>
                        </div>
                        <h2>Platform Layanan Hukum Modern &amp; Berintegritas</h2>
                        <p class="about-lead">
                            Sahabat Hukum merupakan platform layanan hukum yang membantu menghubungkan klien dengan advokat dan mengelola proses konsultasi serta perkara secara lebih terstruktur. Kami berkomitmen memberikan perlindungan dan kepastian hukum dengan standar profesionalitas yang tinggi.
                        </p>

                        <div class="about-points">
                            <div class="about-point-card">
                                <div class="about-point-icon">
                                    <i data-lucide="user-check" style="width: 18px; height: 18px;"></i>
                                </div>
                                <h4>Layanan Profesional</h4>
                                <p>Ditangani oleh advokat berlisensi resmi dengan dedikasi etika hukum.</p>
                            </div>

                            <div class="about-point-card">
                                <div class="about-point-icon">
                                    <i data-lucide="git-commit" style="width: 18px; height: 18px;"></i>
                                </div>
                                <h4>Pendampingan Terstruktur</h4>
                                <p>Alur tahapan perkara yang tertata rapi, transparan, dan terukur.</p>
                            </div>

                            <div class="about-point-card">
                                <div class="about-point-icon">
                                    <i data-lucide="folder-lock" style="width: 18px; height: 18px;"></i>
                                </div>
                                <h4>Pengelolaan Dokumen</h4>
                                <p>Penyimpanan berkas digital dengan hak akses terlindungi dan aman.</p>
                            </div>

                            <div class="about-point-card">
                                <div class="about-point-icon">
                                    <i data-lucide="message-square" style="width: 18px; height: 18px;"></i>
                                </div>
                                <h4>Komunikasi Terintegrasi</h4>
                                <p>Koordinasi langsung antara klien dan advokat tanpa kendala birokrasi.</p>
                            </div>
                        </div>

                        <div class="about-checklist-box">
                            <h4>Komitmen &amp; Standar Praktik Hukum</h4>
                            <ul class="about-checklist">
                                <li>
                                    <i data-lucide="check" style="width: 15px; height: 15px;"></i>
                                    <span>Kerahasiaan hubungan advokat-klien dijaga ketat</span>
                                </li>
                                <li>
                                    <i data-lucide="check" style="width: 15px; height: 15px;"></i>
                                    <span>Penanganan perkara berbasis data valid</span>
                                </li>
                                <li>
                                    <i data-lucide="check" style="width: 15px; height: 15px;"></i>
                                    <span>Laporan perkembangan berkala transparan</span>
                                </li>
                                <li>
                                    <i data-lucide="check" style="width: 15px; height: 15px;"></i>
                                    <span>Bebas benturan kepentingan hukum</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================================================================
             4. NILAI UTAMA (Clean Modern Row)
             ==================================================================== -->
        <section class="values-section">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">
                        <i data-lucide="star" style="width: 14px; height: 14px;"></i>
                        <span>Nilai Utama</span>
                    </div>
                    <h2 class="section-title">Prinsip &amp; Keunggulan Kami</h2>
                    <p class="section-subtitle">
                        Kami menjunjung tinggi nilai-nilai fundamental demi memberikan rasa aman dan keadilan optimal bagi setiap pencari keadilan.
                    </p>
                </div>

                <div class="values-grid reveal-on-scroll">
                    <div class="value-card">
                        <div class="value-icon-box">
                            <i data-lucide="award" style="width: 22px; height: 22px;"></i>
                        </div>
                        <h3>Profesional</h3>
                        <p>Bekerja sesuai kode etik profesi advokat dengan keahlian hukum yang mendalam.</p>
                    </div>

                    <div class="value-card">
                        <div class="value-icon-box">
                            <i data-lucide="shield-check" style="width: 22px; height: 22px;"></i>
                        </div>
                        <h3>Terpercaya</h3>
                        <p>Mengedepankan kejujuran, integritas, dan reputasi yang teruji dalam membela hak klien.</p>
                    </div>

                    <div class="value-card">
                        <div class="value-icon-box">
                            <i data-lucide="eye" style="width: 22px; height: 22px;"></i>
                        </div>
                        <h3>Transparan</h3>
                        <p>Memberikan kepastian tahapan, biaya, dan kemajuan penanganan perkara secara terbuka.</p>
                    </div>

                    <div class="value-card">
                        <div class="value-icon-box">
                            <i data-lucide="layers" style="width: 22px; height: 22px;"></i>
                        </div>
                        <h3>Terstruktur</h3>
                        <p>Manajemen berkas, jadwal sidang, dan konsultasi yang tertata rapi dalam sistem.</p>
                    </div>

                    <div class="value-card">
                        <div class="value-icon-box">
                            <i data-lucide="lock" style="width: 22px; height: 22px;"></i>
                        </div>
                        <h3>Aman</h3>
                        <p>Data pribadi dan dokumen perkara dilindungi dengan standar keamanan digital berlapis.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================================================================
             5. LAYANAN HUKUM (Editorial Layout)
             ==================================================================== -->
        <section class="services-section" id="layanan">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">
                        <i data-lucide="briefcase" style="width: 14px; height: 14px;"></i>
                        <span>Layanan Kami</span>
                    </div>
                    <h2 class="section-title">Layanan Hukum Terpadu</h2>
                    <p class="section-subtitle">
                        Solusi hukum komprehensif mulai dari konsultasi awal hingga penyelesaian perkara di pengadilan maupun di luar pengadilan.
                    </p>
                </div>

                <div class="services-grid reveal-on-scroll">
                    <!-- Service 1 -->
                    <div class="service-card">
                        <div>
                            <div class="service-card-top">
                                <span class="service-index">01</span>
                                <div class="service-icon-box">
                                    <i data-lucide="message-square" style="width: 22px; height: 22px;"></i>
                                </div>
                            </div>
                            <h3>Konsultasi Hukum</h3>
                            <p>Konsultasi dengan advokat mengenai permasalahan hukum yang dihadapi.</p>
                        </div>
                        <div class="service-footer">
                            <span>Ajukan lewat platform</span>
                            <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                        </div>
                    </div>

                    <!-- Service 2 -->
                    <div class="service-card">
                        <div>
                            <div class="service-card-top">
                                <span class="service-index">02</span>
                                <div class="service-icon-box">
                                    <i data-lucide="scale" style="width: 22px; height: 22px;"></i>
                                </div>
                            </div>
                            <h3>Pendampingan Perkara</h3>
                            <p>Pengelolaan dan pemantauan proses perkara bersama advokat.</p>
                        </div>
                        <div class="service-footer">
                            <span>Litigasi &amp; Non-Litigasi</span>
                            <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                        </div>
                    </div>

                    <!-- Service 3 -->
                    <div class="service-card">
                        <div>
                            <div class="service-card-top">
                                <span class="service-index">03</span>
                                <div class="service-icon-box">
                                    <i data-lucide="folder-check" style="width: 22px; height: 22px;"></i>
                                </div>
                            </div>
                            <h3>Pengelolaan Dokumen</h3>
                            <p>Pengelolaan dokumen yang berkaitan dengan konsultasi dan perkara.</p>
                        </div>
                        <div class="service-footer">
                            <span>Arsip Digital Terverifikasi</span>
                            <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                        </div>
                    </div>

                    <!-- Service 4 -->
                    <div class="service-card">
                        <div>
                            <div class="service-card-top">
                                <span class="service-index">04</span>
                                <div class="service-icon-box">
                                    <i data-lucide="activity" style="width: 22px; height: 22px;"></i>
                                </div>
                            </div>
                            <h3>Monitoring Perkara</h3>
                            <p>Memantau perkembangan dan status perkara secara terstruktur.</p>
                        </div>
                        <div class="service-footer">
                            <span>Update Progres Real-Time</span>
                            <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                        </div>
                    </div>

                    <!-- Service 5 -->
                    <div class="service-card">
                        <div>
                            <div class="service-card-top">
                                <span class="service-index">05</span>
                                <div class="service-icon-box">
                                    <i data-lucide="calendar" style="width: 22px; height: 22px;"></i>
                                </div>
                            </div>
                            <h3>Penjadwalan Konsultasi</h3>
                            <p>Mengatur jadwal konsultasi antara klien dan advokat.</p>
                        </div>
                        <div class="service-footer">
                            <span>Agenda Terkoordinasi</span>
                            <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                        </div>
                    </div>

                    <!-- Service 6 -->
                    <div class="service-card">
                        <div>
                            <div class="service-card-top">
                                <span class="service-index">06</span>
                                <div class="service-icon-box">
                                    <i data-lucide="messages-square" style="width: 22px; height: 22px;"></i>
                                </div>
                            </div>
                            <h3>Komunikasi dengan Advokat</h3>
                            <p>Memudahkan komunikasi antara klien dan advokat yang menangani perkara.</p>
                        </div>
                        <div class="service-footer">
                            <span>Ruang Percakapan Terpadu</span>
                            <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================================================================
             6. TIM ADVOKAT (Horizontal Draggable Carousel)
             ==================================================================== -->
        <section class="team-section" id="advokat">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">
                        <i data-lucide="users" style="width: 14px; height: 14px;"></i>
                        <span>Tim Advokat</span>
                    </div>
                    <h2 class="section-title">Advokat &amp; Konsultan Hukum Kami</h2>
                    <p class="section-subtitle">
                        Didukung oleh para praktisi hukum berpengalaman yang siap memberikan perlindungan dan bantuan hukum terbaik bagi Anda.
                    </p>
                </div>

                <!-- Carousel Controls & Drag Hint -->
                <div class="team-carousel-controls">
                    <div class="team-carousel-hint">
                        <i data-lucide="move-horizontal" style="width: 15px; height: 15px;"></i>
                        <span>Geser atau gunakan tombol navigasi</span>
                    </div>
                    <div class="team-nav-buttons">
                        <button type="button" class="team-nav-btn" id="teamPrevBtn" aria-label="Advokat sebelumnya">
                            <i data-lucide="chevron-left" style="width: 18px; height: 18px;"></i>
                        </button>
                        <button type="button" class="team-nav-btn" id="teamNextBtn" aria-label="Advokat berikutnya">
                            <i data-lucide="chevron-right" style="width: 18px; height: 18px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Draggable Horizontal Track -->
                <div class="team-carousel-track" id="teamCarouselTrack">
                    @forelse($lawyers as $lawyer)
                        @php
                            $nameParts = explode(' ', trim($lawyer->name));
                            $initials = '';
                            foreach (array_slice($nameParts, 0, 2) as $part) {
                                $initials .= strtoupper(substr($part, 0, 1));
                            }
                            $specialization = optional($lawyer->lawyerProfile)->specialization ?? 'Hukum Umum & Konsultasi Perkara';
                        @endphp
                        <div class="lawyer-card">
                            <div class="lawyer-card-header">
                                <div class="lawyer-avatar-circle">
                                    {{ $initials ?: 'SH' }}
                                </div>
                                <span class="lawyer-badge">
                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i> Berlisensi
                                </span>
                            </div>
                            <div class="lawyer-info">
                                <h3 class="lawyer-name">{{ $lawyer->name }}</h3>
                                <div class="lawyer-role">Advokat &amp; Konsultan Hukum</div>
                                <p class="lawyer-spec">
                                    Berpengalaman dalam pendampingan litigasi dan non-litigasi dengan fokus pada penegakan hukum dan kepentingan terbaik klien.
                                </p>
                                <div>
                                    <span class="lawyer-spec-badge">
                                        <i data-lucide="bookmark" style="width: 13px; height: 13px; color: var(--gold-600);"></i>
                                        <span>{{ $specialization }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="lawyer-card">
                            <div class="lawyer-card-header">
                                <div class="lawyer-avatar-circle">AW</div>
                                <span class="lawyer-badge">
                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i> Berlisensi
                                </span>
                            </div>
                            <div class="lawyer-info">
                                <h3 class="lawyer-name">Andi Wijaya, S.H.</h3>
                                <div class="lawyer-role">Managing Partner / Advokat Senior</div>
                                <p class="lawyer-spec">Spesialisasi dalam penanganan perkara hukum pidana, litigasi, dan investigasi hukum.</p>
                                <div>
                                    <span class="lawyer-spec-badge">Hukum Pidana</span>
                                </div>
                            </div>
                        </div>

                        <div class="lawyer-card">
                            <div class="lawyer-card-header">
                                <div class="lawyer-avatar-circle">SP</div>
                                <span class="lawyer-badge">
                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i> Berlisensi
                                </span>
                            </div>
                            <div class="lawyer-info">
                                <h3 class="lawyer-name">Supri, S.H.</h3>
                                <div class="lawyer-role">Senior Associate / Advokat</div>
                                <p class="lawyer-spec">Fokus pada hukum perdata, sengketa bisnis, wanprestasi, dan penyusunan perjanjian kontrak.</p>
                                <div>
                                    <span class="lawyer-spec-badge">Perdata &amp; Pidana</span>
                                </div>
                            </div>
                        </div>

                        <div class="lawyer-card">
                            <div class="lawyer-card-header">
                                <div class="lawyer-avatar-circle">HB</div>
                                <span class="lawyer-badge">
                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i> Berlisensi
                                </span>
                            </div>
                            <div class="lawyer-info">
                                <h3 class="lawyer-name">Hana Fauziah Balqis, S.H.</h3>
                                <div class="lawyer-role">Associate Lawyer / Advokat</div>
                                <p class="lawyer-spec">Keahlian dalam hukum perdata keluarga, perlindungan hak konsumen, serta ketenagakerjaan.</p>
                                <div>
                                    <span class="lawyer-spec-badge">Pidana &amp; Perdata</span>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- ====================================================================
             7. STRUKTUR ORGANISASI (Visual Hierarchical Tree)
             ==================================================================== -->
        <section class="org-section" id="struktur">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">
                        <i data-lucide="network" style="width: 14px; height: 14px;"></i>
                        <span>Struktur Organisasi</span>
                    </div>
                    <h2 class="section-title">Tata Kelola &amp; Hierarki Kantor Hukum</h2>
                    <p class="section-subtitle">
                        Struktur kelembagaan yang jelas memastikan setiap proses hukum dan layanan klien berjalan sesuai prosedur dan tanggung jawab profesional.
                    </p>
                </div>

                <div class="org-diagram reveal-on-scroll">
                    <!-- Tier 1: Pimpinan -->
                    <div class="org-tier">
                        <div class="org-node tier-leader">
                            <div class="org-icon-badge">
                                <i data-lucide="crown" style="width: 20px; height: 20px;"></i>
                            </div>
                            <h3 class="org-title">Pimpinan</h3>
                            <div class="org-role-label">Managing Partner &amp; Dewan Direksi</div>
                            <p class="org-desc">Menetapkan arah kebijakan strategis, standar kepatuhan etika hukum, serta pengawasan mutu pelayanan.</p>
                        </div>
                    </div>

                    <div class="org-connector"></div>

                    <!-- Tier 2: Advokat -->
                    <div class="org-tier">
                        <div class="org-node tier-lawyers">
                            <div class="org-icon-badge">
                                <i data-lucide="scale" style="width: 20px; height: 20px;"></i>
                            </div>
                            <h3 class="org-title">Advokat</h3>
                            <div class="org-role-label">Tim Advokat &amp; Konsultan Hukum</div>
                            <p class="org-desc">Melaksanakan penanganan perkara, konsultasi hukum langsung, penyusunan strategi, dan pembelaan di pengadilan.</p>
                        </div>
                    </div>

                    <div class="org-connector"></div>

                    <!-- Tier 3: Tim Pendukung / Administrasi -->
                    <div class="org-tier">
                        <div class="org-node tier-support">
                            <div class="org-icon-badge">
                                <i data-lucide="clipboard-list" style="width: 20px; height: 20px;"></i>
                            </div>
                            <h3 class="org-title">Tim Pendukung / Administrasi</h3>
                            <div class="org-role-label">Paralegal, Arsiparis &amp; Layanan Klien</div>
                            <p class="org-desc">Mengelola verifikasi berkas, pencatatan jadwal, administrasi sistem informasi, dan pendukung operasional perkara.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================================================================
             8. ALUR PELAYANAN (Timeline Track)
             ==================================================================== -->
        <section class="workflow-section">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">
                        <i data-lucide="workflow" style="width: 14px; height: 14px;"></i>
                        <span>Alur Pelayanan</span>
                    </div>
                    <h2 class="section-title">Bagaimana Sahabat Hukum Bekerja?</h2>
                    <p class="section-subtitle">
                        Tahapan sistematis yang mempermudah Anda dari pengajuan konsultasi awal hingga penyelesaian perkara hukum secara tuntas.
                    </p>
                </div>

                <div class="timeline-track reveal-on-scroll">
                    <!-- Step 1 -->
                    <div class="timeline-step">
                        <div class="step-number-node">01</div>
                        <div class="step-num">LANGKAH 01</div>
                        <h4>Daftar / Masuk</h4>
                        <p>Buat akun baru atau masuk ke akun Anda untuk mengakses sistem secara aman.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="timeline-step">
                        <div class="step-number-node">02</div>
                        <div class="step-num">LANGKAH 02</div>
                        <h4>Ajukan Konsultasi</h4>
                        <p>Jelaskan duduk perkara hukum yang dihadapi dan unggah berkas awal pendukung.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="timeline-step">
                        <div class="step-number-node">03</div>
                        <div class="step-num">LANGKAH 03</div>
                        <h4>Konsultasi dengan Advokat</h4>
                        <p>Advokat terpercaya mendalami kasus dan menyusun alternatif strategi hukum terbaik.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="timeline-step">
                        <div class="step-number-node">04</div>
                        <div class="step-num">LANGKAH 04</div>
                        <h4>Penanganan Perkara</h4>
                        <p>Pelaksanaan pendampingan resmi di pengadilan maupun mediasi di luar persidangan.</p>
                    </div>

                    <!-- Step 5 -->
                    <div class="timeline-step">
                        <div class="step-number-node">05</div>
                        <div class="step-num">LANGKAH 05</div>
                        <h4>Pantau Perkembangan</h4>
                        <p>Pantau catatan tahapan, jadwal sidang, dan agenda terbaru langsung di dashboard.</p>
                    </div>

                    <!-- Step 6 -->
                    <div class="timeline-step">
                        <div class="step-number-node">06</div>
                        <div class="step-num">LANGKAH 06</div>
                        <h4>Kelola Dokumen &amp; Komunikasi</h4>
                        <p>Akses arsip dokumen resmi dan koordinasi interaktif dengan advokat penanggung jawab.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================================================================
             9. PLATFORM SAHABAT HUKUM (Dashboard Preview Showcase)
             ==================================================================== -->
        <section class="platform-section">
            <div class="container">
                <div class="platform-grid">
                    <div class="platform-info reveal-on-scroll">
                        <div class="section-tag">
                            <i data-lucide="layout-dashboard" style="width: 14px; height: 14px;"></i>
                            <span>Sistem Informasi Terintegrasi</span>
                        </div>
                        <h2>Platform Sahabat Hukum</h2>
                        <p class="platform-lead">
                            Sahabat Hukum bukan hanya website informasi, melainkan platform komprehensif untuk mengelola seluruh aktivitas hukum setelah Anda masuk ke akun. Rasakan kemudahan pelacakan perkara dan arsip hukum dalam satu tempat terpadu.
                        </p>

                        <div class="platform-features-grid">
                            <div class="pf-item">
                                <div class="pf-icon"><i data-lucide="message-square" style="width: 15px; height: 15px;"></i></div>
                                <div class="pf-text">
                                    <h4>Konsultasi</h4>
                                    <p>Pengajuan dan telaah hukum langsung.</p>
                                </div>
                            </div>
                            <div class="pf-item">
                                <div class="pf-icon"><i data-lucide="folder" style="width: 15px; height: 15px;"></i></div>
                                <div class="pf-text">
                                    <h4>Perkara</h4>
                                    <p>Manajemen berkas perkara &amp; sidang.</p>
                                </div>
                            </div>
                            <div class="pf-item">
                                <div class="pf-icon"><i data-lucide="file-check" style="width: 15px; height: 15px;"></i></div>
                                <div class="pf-text">
                                    <h4>Dokumen</h4>
                                    <p>Verifikasi &amp; proteksi berkas resmi.</p>
                                </div>
                            </div>
                            <div class="pf-item">
                                <div class="pf-icon"><i data-lucide="calendar" style="width: 15px; height: 15px;"></i></div>
                                <div class="pf-text">
                                    <h4>Jadwal</h4>
                                    <p>Agenda konsultasi dan persidangan.</p>
                                </div>
                            </div>
                            <div class="pf-item">
                                <div class="pf-icon"><i data-lucide="messages-square" style="width: 15px; height: 15px;"></i></div>
                                <div class="pf-text">
                                    <h4>Percakapan</h4>
                                    <p>Chat langsung klien dan advokat.</p>
                                </div>
                            </div>
                            <div class="pf-item">
                                <div class="pf-icon"><i data-lucide="bell" style="width: 15px; height: 15px;"></i></div>
                                <div class="pf-text">
                                    <h4>Notifikasi</h4>
                                    <p>Pemberitahuan perubahan status perkara.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                <i data-lucide="log-in" style="width: 17px; height: 17px;"></i> Masuk ke Platform
                            </a>
                        </div>
                    </div>

                    <!-- Platform Realistic Mockup Preview -->
                    <div class="reveal-on-scroll">
                        <div class="dashboard-mockup">
                            <div class="mockup-window-header">
                                <span class="window-dot dot-red"></span>
                                <span class="window-dot dot-amber"></span>
                                <span class="window-dot dot-green"></span>
                                <span class="mockup-window-title">sahabathukum.com/dashboard/klien</span>
                            </div>

                            <div class="mockup-body">
                                <div class="mockup-sidebar">
                                    <div class="mockup-menu-item active">
                                        <i data-lucide="layout-dashboard" style="width: 14px; height: 14px;"></i>
                                        <span>Beranda</span>
                                    </div>
                                    <div class="mockup-menu-item">
                                        <i data-lucide="message-square" style="width: 14px; height: 14px;"></i>
                                        <span>Konsultasi</span>
                                    </div>
                                    <div class="mockup-menu-item">
                                        <i data-lucide="folder" style="width: 14px; height: 14px;"></i>
                                        <span>Perkara</span>
                                    </div>
                                    <div class="mockup-menu-item">
                                        <i data-lucide="file-text" style="width: 14px; height: 14px;"></i>
                                        <span>Dokumen</span>
                                    </div>
                                    <div class="mockup-menu-item">
                                        <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                                        <span>Jadwal</span>
                                    </div>
                                    <div class="mockup-menu-item">
                                        <i data-lucide="message-circle" style="width: 14px; height: 14px;"></i>
                                        <span>Percakapan</span>
                                    </div>
                                </div>

                                <div class="mockup-content">
                                    <div class="mockup-stats-bar">
                                        <div class="m-stat-box">
                                            <div class="m-stat-label">Perkara Aktif</div>
                                            <div class="m-stat-val">3 Perkara</div>
                                        </div>
                                        <div class="m-stat-box">
                                            <div class="m-stat-label">Konsultasi</div>
                                            <div class="m-stat-val">2 Selesai</div>
                                        </div>
                                        <div class="m-stat-box">
                                            <div class="m-stat-label">Dokumen Sah</div>
                                            <div class="m-stat-val">12 Berkas</div>
                                        </div>
                                    </div>

                                    <div class="mockup-table-preview">
                                        <div class="m-table-header">Perkembangan Perkara Terbaru</div>
                                        <div class="m-table-row">
                                            <span style="font-weight: 600; color: #fff;">Sengketa Kontrak Kerjasama</span>
                                            <span class="m-pill pill-active">Proses Sidang</span>
                                        </div>
                                        <div class="m-table-row">
                                            <span style="font-weight: 600; color: #fff;">Klarifikasi Waris &amp; Harta Bersama</span>
                                            <span class="m-pill pill-done">Mediasi Berhasil</span>
                                        </div>
                                        <div class="m-table-row">
                                            <span style="font-weight: 600; color: #fff;">Pemberkasan Surat Kuasa Khusus</span>
                                            <span class="m-pill pill-done">Terverifikasi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================================================================
             10. ASISTEN HUKUM (Internal AI Feature Notice)
             ==================================================================== -->
        <section class="assistant-section">
            <div class="container assistant-container">
                <div class="assistant-tag">
                    <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
                    <span>Teknologi Hukum Cerdas</span>
                </div>
                <h2 class="assistant-title">Asisten Hukum</h2>
                <p class="assistant-subtitle">
                    Bantuan informasi hukum berbasis AI untuk membantu pengguna memahami informasi dan sumber hukum yang relevan.
                </p>

                <div class="assistant-features-grid reveal-on-scroll">
                    <div class="as-feature-card">
                        <div class="as-icon-box">
                            <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                        </div>
                        <h4>Mencari Informasi Hukum</h4>
                        <p>Penelusuran regulasi dan peraturan perundang-undangan Indonesia secara terstruktur.</p>
                    </div>

                    <div class="as-feature-card">
                        <div class="as-icon-box">
                            <i data-lucide="book-open" style="width: 18px; height: 18px;"></i>
                        </div>
                        <h4>Membantu Memahami Konteks</h4>
                        <p>Menjelaskan istilah, asas, dan konstruksi hukum dalam bahasa yang mudah dipahami.</p>
                    </div>

                    <div class="as-feature-card">
                        <div class="as-icon-box">
                            <i data-lucide="layers" style="width: 18px; height: 18px;"></i>
                        </div>
                        <h4>Menjelaskan Sumber Tersedia</h4>
                        <p>Sintesis informasi berdasarkan korpus hukum resmi dan basis pengetahuan terpercaya.</p>
                    </div>

                    <div class="as-feature-card">
                        <div class="as-icon-box">
                            <i data-lucide="file-badge" style="width: 18px; height: 18px;"></i>
                        </div>
                        <h4>Menampilkan Referensi</h4>
                        <p>Menyajikan rujukan pasal, undang-undang, serta yurisprudensi terkait secara akurat.</p>
                    </div>
                </div>

                <!-- Internal Authentication Notice -->
                <div class="assistant-auth-notice reveal-on-scroll">
                    <div class="notice-content">
                        <div class="notice-icon">
                            <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div class="notice-text">
                            <h5>Fitur Eksklusif Pengguna Terdaftar</h5>
                            <p>Asisten Hukum tersedia setelah pengguna masuk ke platform Sahabat Hukum.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('login') }}" class="btn btn-gold">
                            <i data-lucide="log-in" style="width: 15px; height: 15px;"></i> Masuk untuk Menggunakan Asisten Hukum
                        </a>
                    </div>
                </div>
            </div>
        </section>



        <!-- ====================================================================
             12. CALL TO ACTION (CTA Konsultasi)
             ==================================================================== -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-box reveal-on-scroll">
                    <h2>Siap Mendapatkan Pendampingan Hukum?</h2>
                    <p>
                        Mulai konsultasi dan kelola proses hukum Anda melalui Sahabat Hukum. Dapatkan kepastian langkah hukum bersama para praktisi terbaik.
                    </p>
                    <div class="cta-buttons">
                        <a href="{{ route('register') }}" class="btn btn-gold btn-lg">
                            <i data-lucide="user-plus" style="width: 17px; height: 17px;"></i> Daftar Sekarang
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">
                            <i data-lucide="log-in" style="width: 17px; height: 17px;"></i> Masuk
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ====================================================================
         13. FOOTER
         ==================================================================== -->
    <footer class="site-footer" id="kontak">
        <div class="container">
            <div class="footer-top">
                <!-- Col 1: Brand & Bio -->
                <div class="footer-brand-col">
                    <div class="navbar-brand">
                        <div class="brand-icon">
                            <i data-lucide="scale" style="width: 20px; height: 20px;"></i>
                        </div>
                        <div>
                            <div class="brand-title">Sahabat Hukum</div>
                            <div class="brand-desc">Sistem Manajemen Perkara &amp; Konsultasi</div>
                        </div>
                    </div>
                    <p class="footer-about-text">
                        Sistem Informasi Manajemen Perkara dan Layanan Konsultasi Hukum Berbasis Web. Memberikan layanan pendampingan hukum yang profesional, terpercaya, transparan, dan terstruktur bagi seluruh lapisan masyarakat.
                    </p>
                </div>

                <!-- Col 2: Navigation -->
                <div class="footer-col">
                    <h4>Navigasi</h4>
                    <ul class="footer-links">
                        <li><a href="#tentang" class="footer-link">Tentang Kami</a></li>
                        <li><a href="#layanan" class="footer-link">Layanan</a></li>
                        <li><a href="#advokat" class="footer-link">Tim Advokat</a></li>
                        <li><a href="#struktur" class="footer-link">Struktur Organisasi</a></li>
                        <li><a href="#kontak" class="footer-link">Kontak</a></li>
                    </ul>
                </div>

                <!-- Col 3: Contact Details -->
                <div class="footer-col">
                    <h4>Informasi Kontak</h4>
                    <ul class="footer-contact-list">
                        <li class="footer-contact-item">
                            <i data-lucide="map-pin" style="width: 17px; height: 17px;"></i>
                            <span>Gedung Sahabat Hukum, Lantai 4, Jl. Sudirman No. 88, Jakarta Pusat 10220</span>
                        </li>
                        <li class="footer-contact-item">
                            <i data-lucide="mail" style="width: 17px; height: 17px;"></i>
                            <span>kontak@sahabathukum.com</span>
                        </li>
                        <li class="footer-contact-item">
                            <i data-lucide="phone" style="width: 17px; height: 17px;"></i>
                            <span>+62 21 5550 8899 / +62 811 2233 4455</span>
                        </li>
                        <li class="footer-contact-item">
                            <i data-lucide="clock" style="width: 17px; height: 17px;"></i>
                            <span>Senin – Jumat: 08:30 – 17:00 WIB</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div>
                    &copy; {{ date('Y') }} Sahabat Hukum. Seluruh hak cipta dilindungi undang-undang.
                </div>
                <div class="footer-legal-links">
                    <a href="#beranda">Kebijakan Privasi</a>
                    <span>&bull;</span>
                    <a href="#beranda">Syarat &amp; Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Initialize Lucide Icons
            lucide.createIcons();

            // 2. Navbar Scroll Effect & Active Link Highlight
            const navbar = document.getElementById('siteNavbar');
            const navLinks = document.querySelectorAll('.nav-link');
            const sections = document.querySelectorAll('section[id], footer[id], body[id]');

            window.addEventListener('scroll', function () {
                if (window.scrollY > 30) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                let current = '';
                sections.forEach(function (section) {
                    const sectionTop = section.offsetTop - 120;
                    if (window.scrollY >= sectionTop) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(function (link) {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active');
                    }
                });
            }, { passive: true });

            // 3. Mobile Hamburger Menu Toggle
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const mobileNav = document.getElementById('mobileNav');

            if (hamburgerBtn && mobileNav) {
                hamburgerBtn.addEventListener('click', function () {
                    mobileNav.classList.toggle('open');
                });

                const mobileLinks = mobileNav.querySelectorAll('a');
                mobileLinks.forEach(function (link) {
                    link.addEventListener('click', function () {
                        mobileNav.classList.remove('open');
                    });
                });
            }

            // 4. Hero Slideshow: Continuous Pure-Fade Auto-Transition (4.5s)
            const slides = document.querySelectorAll('.hero-slide');
            const dots = document.querySelectorAll('.hero-dot');
            const prevBtn = document.getElementById('heroPrevBtn');
            const nextBtn = document.getElementById('heroNextBtn');

            let currentSlide = 0;
            let slideInterval = null;
            const slideDuration = 4500; // 4.5 seconds

            // Preload all slideshow images immediately into browser cache
            slides.forEach(function (slide) {
                const img = slide.querySelector('img');
                if (img && img.src) {
                    const preloader = new Image();
                    preloader.src = img.src;
                }
            });

            function showSlide(index) {
                if (slides.length === 0) return;
                currentSlide = (index + slides.length) % slides.length;

                slides.forEach(function (slide, i) {
                    if (i === currentSlide) {
                        slide.classList.add('active');
                    } else {
                        slide.classList.remove('active');
                    }
                });

                dots.forEach(function (dot, i) {
                    if (i === currentSlide) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
            }

            function nextSlide() {
                showSlide(currentSlide + 1);
            }

            function prevSlide() {
                showSlide(currentSlide - 1);
            }

            // Respect user prefers-reduced-motion preference
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function startSlideShow() {
                stopSlideShow();
                if (!prefersReducedMotion) {
                    slideInterval = setInterval(nextSlide, slideDuration);
                }
            }

            function stopSlideShow() {
                if (slideInterval) {
                    clearInterval(slideInterval);
                    slideInterval = null;
                }
            }

            function resetSlideTimer() {
                stopSlideShow();
                startSlideShow();
            }

            // Manual controls update slide immediately and reset timer properly
            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    nextSlide();
                    resetSlideTimer();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    prevSlide();
                    resetSlideTimer();
                });
            }

            dots.forEach(function (dot, i) {
                dot.addEventListener('click', function () {
                    showSlide(i);
                    resetSlideTimer();
                });
            });

            // Start auto slideshow (runs continuously without pausing on hover)
            startSlideShow();

            // 5. Tim Advokat Horizontal Carousel (Draggable mouse, swipe touch & arrow buttons)
            const teamTrack = document.getElementById('teamCarouselTrack');
            const teamPrevBtn = document.getElementById('teamPrevBtn');
            const teamNextBtn = document.getElementById('teamNextBtn');

            if (teamTrack) {
                let isDown = false;
                let startX = 0;
                let scrollLeft = 0;

                teamTrack.addEventListener('mousedown', function (e) {
                    isDown = true;
                    teamTrack.classList.add('dragging');
                    startX = e.pageX - teamTrack.offsetLeft;
                    scrollLeft = teamTrack.scrollLeft;
                });

                teamTrack.addEventListener('mouseleave', function () {
                    isDown = false;
                    teamTrack.classList.remove('dragging');
                });

                teamTrack.addEventListener('mouseup', function () {
                    isDown = false;
                    teamTrack.classList.remove('dragging');
                });

                teamTrack.addEventListener('mousemove', function (e) {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - teamTrack.offsetLeft;
                    const walk = (x - startX) * 1.5;
                    teamTrack.scrollLeft = scrollLeft - walk;
                });

                if (teamNextBtn) {
                    teamNextBtn.addEventListener('click', function () {
                        const cardWidth = teamTrack.querySelector('.lawyer-card')?.offsetWidth || 330;
                        teamTrack.scrollBy({ left: cardWidth + 24, behavior: 'smooth' });
                    });
                }

                if (teamPrevBtn) {
                    teamPrevBtn.addEventListener('click', function () {
                        const cardWidth = teamTrack.querySelector('.lawyer-card')?.offsetWidth || 330;
                        teamTrack.scrollBy({ left: -(cardWidth + 24), behavior: 'smooth' });
                    });
                }
            }

            // 6. Reveal on Scroll (IntersectionObserver)
            if ('IntersectionObserver' in window && !prefersReducedMotion) {
                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                document.querySelectorAll('.reveal-on-scroll').forEach(function (el) {
                    observer.observe(el);
                });
            } else {
                document.querySelectorAll('.reveal-on-scroll').forEach(function (el) {
                    el.classList.add('revealed');
                });
            }
        });
    </script>
</body>
</html>
