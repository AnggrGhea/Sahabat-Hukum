<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Klien — Sahabat Hukum</title>
    <meta name="description" content="Daftarkan akun Klien Anda di Sahabat Hukum dan mulai proses konsultasi hukum secara terstruktur.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy:        #0f2557;
            --navy-mid:    #1a3a7a;
            --navy-light:  #2952a3;
            --gold:        #c9a84c;
            --gold-light:  #e8c97a;
            --white:       #ffffff;
            --gray-50:     #f8fafc;
            --gray-100:    #f1f5f9;
            --gray-200:    #e2e8f0;
            --gray-400:    #94a3b8;
            --gray-600:    #475569;
            --gray-800:    #1e293b;
            --red:         #dc2626;
            --red-bg:      #fef2f2;
            --green:       #16a34a;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background-color: var(--gray-50);
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            width: 40%;
            background: linear-gradient(160deg, var(--navy) 0%, var(--navy-mid) 60%, var(--navy-light) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .panel-left::before {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }
        .panel-left::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(201,168,76,0.08);
        }

        .brand { position: relative; z-index: 1; }

        .brand-logo {
            display: flex; align-items: center; gap: 0.875rem;
            margin-bottom: 3.5rem;
        }
        .brand-icon {
            width: 48px; height: 48px;
            background: rgba(201,168,76,0.2);
            border: 1px solid rgba(201,168,76,0.4);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .brand-icon svg { width: 26px; height: 26px; fill: var(--gold); }
        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.375rem; font-weight: 700;
            color: var(--white); line-height: 1.2;
        }
        .brand-name span {
            display: block;
            font-family: 'Inter', sans-serif;
            font-size: 0.7rem; font-weight: 400;
            letter-spacing: 0.12em; text-transform: uppercase;
            color: rgba(255,255,255,0.5); margin-top: 2px;
        }

        .panel-headline { position: relative; z-index: 1; }
        .divider-gold {
            width: 40px; height: 3px;
            background: var(--gold); border-radius: 2px;
            margin-bottom: 1.25rem;
        }
        .panel-headline h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700;
            color: var(--white); line-height: 1.3;
            margin-bottom: 1rem;
        }
        .panel-headline p {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.7; max-width: 280px;
        }

        .steps { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 1rem; }
        .step-item { display: flex; gap: 1rem; align-items: flex-start; }
        .step-num {
            width: 28px; height: 28px; border-radius: 50%;
            background: rgba(201,168,76,0.2);
            border: 1px solid rgba(201,168,76,0.5);
            color: var(--gold);
            font-size: 0.75rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .step-text h4 {
            font-size: 0.8125rem; font-weight: 600;
            color: rgba(255,255,255,0.85); margin-bottom: 0.125rem;
        }
        .step-text p { font-size: 0.75rem; color: rgba(255,255,255,0.45); }

        .panel-motto {
            position: relative; z-index: 1;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .panel-motto p {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.35);
            letter-spacing: 0.05em; font-style: italic;
        }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem;
            overflow-y: auto;
        }

        .form-container {
            width: 100%; max-width: 460px;
            animation: slideUp 0.5s ease both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-header { margin-bottom: 1.75rem; }
        .form-header h2 {
            font-size: 1.625rem; font-weight: 700;
            color: var(--gray-800); margin-bottom: 0.375rem;
        }
        .form-header p { font-size: 0.875rem; color: var(--gray-400); }

        /* Alert */
        .alert-error {
            background: var(--red-bg);
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 0.875rem 1rem;
            margin-bottom: 1.5rem;
        }
        .alert-error p { font-size: 0.8125rem; color: var(--red); line-height: 1.6; }

        /* Form rows */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        .form-group { margin-bottom: 1.125rem; }
        .form-group label {
            display: block; font-size: 0.8125rem; font-weight: 600;
            color: var(--gray-600); margin-bottom: 0.5rem; letter-spacing: 0.02em;
        }
        .input-wrapper { position: relative; }
        .input-icon {
            position: absolute; left: 0.875rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400); display: flex; align-items: center;
        }
        .input-icon svg { width: 15px; height: 15px; }

        .form-control {
            width: 100%;
            padding: 0.75rem 0.875rem 0.75rem 2.625rem;
            border: 1.5px solid var(--gray-200);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem; color: var(--gray-800);
            background: var(--white);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--navy-light);
            box-shadow: 0 0 0 3px rgba(41,82,163,0.1);
        }
        .form-control::placeholder { color: var(--gray-400); }

        .toggle-password {
            position: absolute; right: 0.875rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--gray-400);
            display: flex; align-items: center; padding: 0;
            transition: color 0.2s;
        }
        .toggle-password:hover { color: var(--navy); }
        .toggle-password svg { width: 15px; height: 15px; }

        /* Password strength */
        .password-hint {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            color: var(--gray-400);
        }

        /* Terms */
        .terms-check {
            display: flex; align-items: flex-start; gap: 0.75rem;
            margin-bottom: 1.25rem; margin-top: 0.25rem;
        }
        .terms-check input[type="checkbox"] {
            width: 16px; height: 16px; margin-top: 2px;
            accent-color: var(--navy); cursor: pointer; flex-shrink: 0;
        }
        .terms-check label {
            font-size: 0.8rem; color: var(--gray-600); line-height: 1.5; cursor: pointer;
        }
        .terms-check a { color: var(--navy-light); font-weight: 600; text-decoration: none; }

        /* Submit button */
        .btn-submit {
            width: 100%; padding: 0.875rem;
            background: var(--navy); color: var(--white);
            border: none; border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem; font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            display: flex; align-items: center; justify-content: center;
            gap: 0.5rem; letter-spacing: 0.02em;
        }
        .btn-submit:hover {
            background: var(--navy-mid);
            box-shadow: 0 4px 16px rgba(15,37,87,0.3);
        }
        .btn-submit:active { transform: scale(0.99); }
        .btn-submit svg { width: 16px; height: 16px; }

        /* Footer link */
        .form-footer {
            margin-top: 1.5rem; text-align: center;
            font-size: 0.8125rem; color: var(--gray-400);
        }
        .form-footer a {
            color: var(--navy-light); font-weight: 600;
            text-decoration: none; transition: color 0.2s;
        }
        .form-footer a:hover { color: var(--navy); }

        /* Responsive */
        @media (max-width: 900px) {
            .panel-left { display: none; }
            .panel-right { padding: 1.5rem; }
        }
        @media (max-width: 480px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="panel-left">
    <div class="brand">
        <div class="brand-logo">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 14l-3-3 1.41-1.41L11 12.17l4.59-4.58L17 9l-6 6z"/>
                </svg>
            </div>
            <div class="brand-name">
                Sahabat Hukum
                <span>Sistem Informasi Hukum</span>
            </div>
        </div>

        <div class="panel-headline">
            <div class="divider-gold"></div>
            <h1>Mulai Perjalanan Hukum Anda Bersama Kami</h1>
            <p>Daftar sekarang dan dapatkan akses ke layanan konsultasi hukum profesional.</p>
        </div>
    </div>

    <div class="steps">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-text">
                <h4>Buat Akun</h4>
                <p>Daftarkan diri sebagai Klien dalam hitungan menit.</p>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-text">
                <h4>Ajukan Konsultasi</h4>
                <p>Sampaikan permasalahan hukum Anda kepada Advokat.</p>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-text">
                <h4>Pantau Perkembangan</h4>
                <p>Monitor status perkara Anda secara real-time.</p>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-text">
                <h4>Asisten Hukum</h4>
                <p>Dapatkan informasi hukum awal dari AI Asisten kami.</p>
            </div>
        </div>
    </div>

    <div class="panel-motto">
        <p>"Fiat justitia ruat caelum" — Tegakkanlah keadilan walau langit runtuh.</p>
    </div>
</div>

<!-- RIGHT PANEL -->
<div class="panel-right">
    <div class="form-container">
        <div class="form-header">
            <h2>Buat Akun Baru</h2>
            <p>Pendaftaran khusus untuk Klien. Advokat didaftarkan oleh Admin.</p>
        </div>

        @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input id="name" type="text" name="name" class="form-control"
                        placeholder="Nama sesuai KTP"
                        value="{{ old('name') }}" required autocomplete="name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <input id="email" type="email" name="email" class="form-control"
                            placeholder="nama@email.com"
                            value="{{ old('email') }}" required autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Nomor Telepon</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.6 19.79 19.79 0 0 1 1.61 5a2 2 0 0 1 1.53-2H6a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 10a16 16 0 0 0 6.29 6.29l.62-.62a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </span>
                        <input id="phone" type="text" name="phone" class="form-control"
                            placeholder="08xxxxxxxxxx"
                            value="{{ old('phone') }}" required autocomplete="tel">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input id="password" type="password" name="password" class="form-control"
                            placeholder="Min. 8 karakter"
                            required minlength="8" autocomplete="new-password"
                            oninput="checkStrength(this.value)">
                        <button type="button" class="toggle-password" onclick="togglePwd('password','eyeIcon1')" aria-label="Tampilkan">
                            <svg id="eyeIcon1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <p class="password-hint" id="strengthText">Minimal 8 karakter</p>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </span>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                            placeholder="Ulangi kata sandi"
                            required minlength="8" autocomplete="new-password">
                        <button type="button" class="toggle-password" onclick="togglePwd('password_confirmation','eyeIcon2')" aria-label="Tampilkan">
                            <svg id="eyeIcon2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="registerBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                Daftar Sekarang
            </button>
        </form>

        <div class="form-footer">
            Sudah memiliki akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
    </div>
</div>

<script>
    function togglePwd(inputId, iconId) {
        const inp = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            `;
        } else {
            inp.type = 'password';
            icon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            `;
        }
    }

    function checkStrength(val) {
        const el = document.getElementById('strengthText');
        if (val.length === 0) {
            el.style.color = '#94a3b8';
            el.textContent = 'Minimal 8 karakter';
        } else if (val.length < 8) {
            el.style.color = '#dc2626';
            el.textContent = `Kurang ${8 - val.length} karakter lagi`;
        } else if (val.length < 12) {
            el.style.color = '#d97706';
            el.textContent = 'Kekuatan: Cukup';
        } else {
            el.style.color = '#16a34a';
            el.textContent = 'Kekuatan: Kuat ✓';
        }
    }
</script>

</body>
</html>
