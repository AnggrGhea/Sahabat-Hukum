<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Sahabat Hukum</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/admin.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background-color: var(--color-gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .error-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 3rem 2rem;
            max-width: 450px;
            width: 100%;
        }
        .error-icon {
            width: 64px;
            height: 64px;
            background-color: #fee2e2;
            color: #991b1b;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-gray-dark);
            margin-bottom: 0.5rem;
        }
        .error-desc {
            color: var(--color-gray-text);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<div class="error-card">
    <div class="error-icon">
        <i data-lucide="shield-alert" style="width: 32px; height: 32px;"></i>
    </div>
    <h1 class="error-title">Akses Ditolak</h1>
    <p class="error-desc">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    
    <a href="{{ Auth::check() ? (Auth::user()->isAdmin() ? '/admin' : (Auth::user()->isAdvokat() ? '/advokat' : '/klien')) : '/login' }}" class="btn btn-primary" style="display: inline-flex;">
        <i data-lucide="home"></i> Kembali ke Beranda
    </a>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>
