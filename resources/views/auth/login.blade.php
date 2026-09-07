<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sahabat Hukum</title>
    <meta name="description" content="Masuk ke Sahabat Hukum — Sistem Informasi Manajemen Perkara dan Layanan Konsultasi Hukum.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy:       #1a2744;
            --navy-mid:   #243357;
            --navy-light: #2952a3;
            --gold:       #c9a84c;
            --white:      #ffffff;
            --cream:      #f7f6f2;
            --gray-100:   #f1f5f9;
            --gray-200:   #e2e8f0;
            --gray-400:   #9ca3af;
            --gray-600:   #4b5563;
            --gray-800:   #1e293b;
            --red:        #dc2626;
            --red-bg:     #fef2f2;
        }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--cream);
        }

        /* LEFT PANEL */
        .panel-left {
            width: 42%;
            background: var(--navy);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 28px 36px;
        }
        .panel-left::before {
            content:''; position:absolute;
            top:-100px; right:-100px;
            width:350px; height:350px;
            border-radius:50%;
            background:rgba(255,255,255,0.03);
        }
        .panel-left::after {
            content:''; position:absolute;
            bottom:-60px; left:-60px;
            width:250px; height:250px;
            border-radius:50%;
            background:rgba(201,168,76,0.06);
        }

        .pl-brand {
            position:relative; z-index:1;
            display:flex; align-items:center; gap:12px;
        }
        .pl-icon {
            width:40px; height:40px;
            background:rgba(201,168,76,0.18);
            border:1px solid rgba(201,168,76,0.35);
            border-radius:9px;
            display:flex; align-items:center; justify-content:center;
        }
        .pl-icon svg { width:22px; height:22px; fill:var(--gold); }
        .pl-brand-text .pl-name {
            font-size:.8125rem; font-weight:700;
            color:var(--white); letter-spacing:.06em;
            text-transform:uppercase;
        }
        .pl-brand-text .pl-sub {
            font-size:.65rem; color:rgba(255,255,255,.4);
            margin-top:1px; letter-spacing:.02em;
        }

        .pl-main {
            position:relative; z-index:1;
        }
        .pl-main h1 {
            font-size:1.875rem; font-weight:700;
            color:var(--white); line-height:1.25;
            margin-bottom:.875rem;
        }
        .pl-main p {
            font-size:.875rem; color:rgba(255,255,255,.55);
            line-height:1.7; max-width:300px;
        }

        .pl-divider {
            position:relative; z-index:1;
            width:36px; height:2px;
            background:var(--gold); border-radius:2px;
            margin: 0 0 20px;
        }

        .pl-quote {
            position:relative; z-index:1;
        }
        .pl-quote .quote-text {
            font-style:italic; font-size:.875rem;
            color:var(--gold); margin-bottom:4px;
        }
        .pl-quote .quote-sub {
            font-size:.75rem; color:rgba(255,255,255,.35);
        }

        .pl-footer {
            position:relative; z-index:1;
            font-size:.7rem; color:rgba(255,255,255,.25);
        }

        /* RIGHT PANEL */
        .panel-right {
            flex:1;
            display:flex; align-items:center; justify-content:center;
            padding:40px 48px;
            background:var(--cream);
        }
        .form-wrap {
            width:100%; max-width:400px;
            animation: fadeUp .4s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .form-wrap h2 {
            font-size:1.625rem; font-weight:700;
            color:var(--gray-800); margin-bottom:6px;
        }
        .form-wrap .sub {
            font-size:.875rem; color:var(--gray-400);
            margin-bottom:28px;
        }

        .alert-err {
            background:var(--red-bg);
            border:1px solid #fecaca; border-radius:8px;
            padding:.75rem 1rem; margin-bottom:1.25rem;
            font-size:.8rem; color:var(--red); line-height:1.5;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: .75rem 1rem; margin-bottom: 1.25rem;
            font-size: .8rem; color: #166534; line-height: 1.5;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success svg {
            width: 18px; height: 18px; flex-shrink: 0; fill: #16a34a;
        }

        .form-group { margin-bottom:18px; }
        .form-group label {
            display:block; font-size:.8rem; font-weight:600;
            color:var(--gray-600); margin-bottom:6px;
        }
        .inp-wrap { position:relative; }
        .inp-wrap input {
            width:100%;
            padding:10px 14px;
            border:1.5px solid var(--gray-200);
            border-radius:8px;
            font-family:'Inter',sans-serif;
            font-size:.875rem; color:var(--gray-800);
            background:var(--white); outline:none;
            transition:border-color .2s, box-shadow .2s;
        }
        .inp-wrap input:focus {
            border-color:#2952a3;
            box-shadow:0 0 0 3px rgba(41,82,163,.1);
        }
        .inp-wrap input::placeholder { color:var(--gray-400); }

        .pw-row {
            display:flex; align-items:center;
            justify-content:space-between; margin-bottom:6px;
        }
        .pw-row label { margin-bottom:0; }
        .lupa-link { font-size:.78rem; color:var(--navy-light); font-weight:500; }
        .lupa-link:hover { text-decoration:underline; }

        .eye-btn {
            position:absolute; right:12px; top:50%;
            transform:translateY(-50%);
            background:none; border:none; cursor:pointer;
            color:var(--gray-400); display:flex;
            align-items:center; padding:0;
            transition:color .15s;
        }
        .eye-btn:hover { color:var(--navy); }
        .eye-btn svg { width:16px; height:16px; }

        /* Demo box */
        .demo-box {
            border:1px solid var(--gray-200);
            border-radius:8px; padding:12px 14px;
            margin-bottom:20px; background:var(--white);
        }
        .demo-label {
            font-size:.65rem; font-weight:700;
            letter-spacing:.08em; text-transform:uppercase;
            color:var(--gray-400); margin-bottom:8px;
        }
        .demo-btns { display:flex; gap:6px; }
        .demo-btn {
            flex:1; padding:7px 10px;
            border:1.5px solid var(--gray-200);
            border-radius:6px;
            font-family:'Inter',sans-serif;
            font-size:.8rem; font-weight:600;
            cursor:pointer; background:var(--white);
            color:var(--gray-600);
            transition:all .15s;
        }
        .demo-btn:hover { border-color:var(--navy); color:var(--navy); }
        .demo-btn.active {
            background:var(--navy); color:var(--white);
            border-color:var(--navy);
        }

        /* Submit */
        .btn-masuk {
            width:100%; padding:11px;
            background:var(--navy); color:var(--white);
            border:none; border-radius:8px;
            font-family:'Inter',sans-serif;
            font-size:.9rem; font-weight:600;
            cursor:pointer; display:flex;
            align-items:center; justify-content:center;
            gap:8px; letter-spacing:.02em;
            transition:background .2s, box-shadow .2s;
        }
        .btn-masuk:hover {
            background:var(--navy-mid);
            box-shadow:0 4px 14px rgba(26,39,68,.35);
        }
        .btn-masuk svg { width:16px; height:16px; }

        .form-footer {
            margin-top:20px; text-align:center;
            font-size:.8125rem; color:var(--gray-400);
        }
        .form-footer a { color:var(--navy-light); font-weight:600; }
        .form-footer a:hover { text-decoration:underline; }

        @media (max-width:768px) {
            .panel-left { display:none; }
            .panel-right { padding:24px 20px; }
        }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="panel-left">
    <div class="pl-brand">
        <div class="pl-icon">
            <svg viewBox="0 0 24 24"><path d="M12 3L2 7l2 .8V18c0 .6.4 1 1 1h2v1h10v-1h2c.6 0 1-.4 1-1V7.8L22 7 12 3zm-2 14H6v-7.6l4 1.6V17zm8 0h-4v-6l4-1.6V17zM12 11.2L4.8 8.4 12 5.6l7.2 2.8L12 11.2z"/></svg>
        </div>
        <div class="pl-brand-text">
            <div class="pl-name">Sahabat Hukum</div>
            <div class="pl-sub">Sistem Informasi Manajemen Perkara</div>
        </div>
    </div>

    <div class="pl-main">
        <div class="pl-divider"></div>
        <h1>Akses Sistem<br>Layanan Hukum</h1>
        <p>Platform terintegrasi untuk manajemen perkara, konsultasi hukum, dan pengelolaan dokumen secara profesional.</p>
    </div>

    <div class="pl-quote">
        <div class="quote-text">"Fiat justitia ruat caelum"</div>
        <div class="quote-sub">Tegakkanlah keadilan meskipun langit runtuh</div>
    </div>

    <div class="pl-footer">© 2025 Sahabat Hukum. Hak Cipta Dilindungi.</div>
</div>

<!-- RIGHT PANEL -->
<div class="panel-right">
    <div class="form-wrap">
        <h2>Selamat Datang Kembali</h2>
        <p class="sub">Masuk ke akun Anda untuk melanjutkan.</p>

        @if (session('success'))
        <div class="alert-success">
            <svg viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        @if ($errors->any())
        <div class="alert-err">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
        @endif

        @if (session('error'))
        <div class="alert-err">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email">Alamat Email</label>
                <div class="inp-wrap">
                    <input id="email" type="email" name="email"
                        placeholder="nama@email.com"
                        value="{{ old('email', session('registered_email')) }}" required autofocus autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <div class="pw-row">
                    <label for="password">Kata Sandi</label>
                    <a href="#" class="lupa-link">Lupa Kata Sandi?</a>
                </div>
                <div class="inp-wrap">
                    <input id="password" type="password" name="password"
                        placeholder="Masukkan kata sandi" required
                        style="padding-right:40px" autocomplete="current-password">
                    <button type="button" class="eye-btn" onclick="toggleEye()" id="eyeBtn">
                        <svg id="eyeIco" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Demo box -->
            <div class="demo-box">
                <div class="demo-label">Demonstrasi — Masuk sebagai:</div>
                <div class="demo-btns">
                    <button type="button" class="demo-btn" id="demoKlien"
                        onclick="setDemo('klien@sahabathukum.test','password','klien')">Klien</button>
                    <button type="button" class="demo-btn" id="demoAdvokat"
                        onclick="setDemo('advokat@sahabathukum.test','password','advokat')">Advokat</button>
                    <button type="button" class="demo-btn" id="demoAdmin"
                        onclick="setDemo('admin@sahabathukum.test','password','admin')">Admin</button>
                </div>
            </div>

            <button type="submit" class="btn-masuk">
                Masuk
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </button>
        </form>

        <div class="form-footer">
            Belum memiliki akun? <a href="{{ route('register') }}">Daftar</a>
        </div>
    </div>
</div>

<script>
    // Set demo credentials
    function setDemo(email, pw, role) {
        var emailInput = document.getElementById('email');
        var pwInput = document.getElementById('password');
        if (emailInput) emailInput.value = email;
        if (pwInput) pwInput.value = pw;

        var roles = ['demoKlien', 'demoAdvokat', 'demoAdmin'];
        for (var i = 0; i < roles.length; i++) {
            var btn = document.getElementById(roles[i]);
            if (btn) btn.classList.remove('active');
        }
        var activeBtn = document.getElementById('demo' + role.charAt(0).toUpperCase() + role.slice(1));
        if (activeBtn) activeBtn.classList.add('active');
    }

    // Set demo as default only if email is empty and no alert exists
    window.onload = function() {
        var emailInput = document.getElementById('email');
        var hasAlert = document.querySelector('.alert-err, .alert-success');
        if (!hasAlert && emailInput && !emailInput.value) {
            setDemo('klien@sahabathukum.test', 'password', 'klien');
        } else {
            var roles = ['demoKlien', 'demoAdvokat', 'demoAdmin'];
            for (var i = 0; i < roles.length; i++) {
                var btn = document.getElementById(roles[i]);
                if (btn) btn.classList.remove('active');
            }
        }
    };

    // Toggle password
    function toggleEye() {
        var inp = document.getElementById('password');
        var ico = document.getElementById('eyeIco');
        if (inp.type === 'password') {
            inp.type = 'text';
            ico.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            inp.type = 'password';
            ico.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }
</script>
</body>
</html>
