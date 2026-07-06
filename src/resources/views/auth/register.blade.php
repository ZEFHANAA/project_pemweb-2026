<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Sistem Lokasi Wisata</title>
    <meta name="description" content="Buat akun baru untuk menyimpan dan mengelola lokasi wisata favorit Anda.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
            background: #0d1117;
        }

        /* Background same as login — consistent */
        .bg-map {
            position: fixed; inset: 0; z-index: 0;
            background-color: #1a2332;
            background-image:
                linear-gradient(rgba(37,99,235,.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37,99,235,.12) 1px, transparent 1px),
                linear-gradient(rgba(37,99,235,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37,99,235,.05) 1px, transparent 1px);
            background-size: 120px 120px, 120px 120px, 24px 24px, 24px 24px;
            animation: mapDrift 60s linear infinite;
        }
        @keyframes mapDrift {
            0%   { background-position: 0 0, 0 0, 0 0, 0 0; }
            100% { background-position: 120px 120px, 120px 120px, 24px 24px, 24px 24px; }
        }

        .bg-blob { position: fixed; border-radius: 50%; filter: blur(100px); pointer-events: none; z-index: 0; }
        .bg-blob-1 { width: 600px; height: 600px; background: rgba(16,185,129,.12); top: -200px; right: -150px; animation: blobDrift 22s ease-in-out infinite; }
        .bg-blob-2 { width: 500px; height: 500px; background: rgba(37,99,235,.12); bottom: -150px; left: -100px; animation: blobDrift 17s ease-in-out infinite reverse; }
        .bg-blob-3 { width: 300px; height: 300px; background: rgba(124,58,237,.08); top: 50%; left: 55%; animation: blobDrift 28s ease-in-out infinite; animation-delay: -12s; }

        @keyframes blobDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(24px, -32px) scale(1.04); }
            66% { transform: translate(-18px, 16px) scale(.97); }
        }

        .deco { position: fixed; z-index: 1; pointer-events: none; opacity: 0; animation: decoAppear 1s ease forwards, decoFloat 7s ease-in-out infinite; }
        .deco-1 { top: 12%; right: 8%;  animation-delay: .2s, .2s; }
        .deco-2 { top: 65%; left: 7%;  animation-delay: .5s, .5s; animation-duration: 1s, 8s; }
        .deco-3 { bottom: 15%; right: 15%; animation-delay: .8s, .8s; animation-duration: 1s, 6s; }
        .deco-4 { top: 40%; left: 5%; animation-delay: .4s, .4s; animation-duration: 1s, 10s; }

        @keyframes decoAppear { to { opacity: .3; } }
        @keyframes decoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        .bg-vignette {
            position: fixed; inset: 0; z-index: 1;
            background: radial-gradient(ellipse 70% 70% at 50% 50%,
                transparent 0%, rgba(13,17,23,.4) 60%, rgba(13,17,23,.7) 100%
            );
        }

        /* ===== CENTERED CARD ===== */
        .card-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 16px;
            animation: cardIn .6s cubic-bezier(.22,1,.36,1) both;
            /* Allow scrolling on small screens */
            max-height: 100vh;
            overflow-y: auto;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(32px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .card {
            background: rgba(255,255,255,.97);
            border-radius: 20px;
            padding: 36px 36px 32px;
            box-shadow:
                0 0 0 1px rgba(255,255,255,.15),
                0 24px 80px rgba(0,0,0,.45),
                0 8px 24px rgba(0,0,0,.25);
        }

        /* Brand */
        .card-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
        .brand-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: #2563eb;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(37,99,235,.4);
        }
        .brand-name { font-size: 14px; font-weight: 700; color: #111827; letter-spacing: -.02em; }
        .brand-sub-name { font-size: 11px; color: #9ca3af; font-weight: 400; }

        .divider { height: 1px; background: #f3f4f6; margin: 0 -36px 24px; }

        /* Heading */
        .card-heading h1 { font-size: 21px; font-weight: 800; color: #111827; letter-spacing: -.4px; margin-bottom: 4px; }
        .card-heading p { font-size: 13.5px; color: #6b7280; line-height: 1.5; margin-bottom: 22px; }

        /* Error */
        .error-box {
            background: #fef2f2; border: 1px solid #fecaca;
            border-left: 3px solid #ef4444; border-radius: 8px;
            padding: 10px 12px; margin-bottom: 18px;
        }
        .error-box ul { list-style: none; color: #b91c1c; font-size: 12.5px; line-height: 1.6; }

        /* Form */
        .form-group { margin-bottom: 14px; }

        .form-label {
            display: block; font-size: 12.5px; font-weight: 600;
            color: #374151; margin-bottom: 5px; letter-spacing: .01em;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%); pointer-events: none;
            color: #9ca3af; transition: color .2s; display: flex; align-items: center;
        }
        .input-wrap:focus-within .input-icon { color: #2563eb; }

        .form-input {
            width: 100%; padding: 10px 14px 10px 38px;
            border: 1.5px solid #e5e7eb; border-radius: 9px;
            font-size: 13.5px; font-family: 'Inter', sans-serif;
            color: #111827; background: #f9fafb; transition: all .2s; outline: none;
        }
        .form-input::placeholder { color: #d1d5db; }
        .form-input:focus { background: #fff; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .has-toggle { padding-right: 40px; }

        .toggle-btn {
            position: absolute; right: 10px; top: 50%;
            transform: translateY(-50%); background: none; border: none;
            cursor: pointer; padding: 4px; color: #9ca3af;
            display: flex; align-items: center; transition: color .2s;
        }
        .toggle-btn:hover { color: #2563eb; }

        /* Password strength */
        .strength-bar { height: 3px; border-radius: 2px; background: #e5e7eb; margin-top: 7px; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 2px; transition: all .4s ease; width: 0%; }
        .strength-label { font-size: 11px; margin-top: 3px; font-weight: 600; color: #9ca3af; }

        /* Submit */
        .btn-submit {
            width: 100%; padding: 12px; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 700; font-family: 'Inter', sans-serif;
            cursor: pointer; color: #fff; background: #2563eb;
            transition: all .22s; margin-top: 6px; letter-spacing: .01em;
        }
        .btn-submit:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(37,99,235,.4); }
        .btn-submit:active { transform: none; box-shadow: none; }

        /* Footer */
        .card-footer { text-align: center; margin-top: 18px; }
        .card-footer p { font-size: 13px; color: #6b7280; margin-bottom: 7px; }
        .card-footer a { color: #2563eb; font-weight: 600; text-decoration: none; transition: color .2s; }
        .card-footer a:hover { color: #1d4ed8; text-decoration: underline; }
        .back-link {
            display: inline-flex; align-items: center; gap: 5px;
            color: #9ca3af !important; font-size: 12px !important; font-weight: 400 !important;
        }
        .back-link:hover { color: #2563eb !important; text-decoration: none !important; }

        .map-credit {
            position: fixed; bottom: 12px; left: 50%; transform: translateX(-50%);
            z-index: 10; font-size: 11px; color: rgba(255,255,255,.3); white-space: nowrap;
        }

        @media (max-width: 480px) {
            .card { padding: 28px 20px 24px; }
            .divider { margin: 0 -20px 22px; }
        }
    </style>
</head>
<body>

    <!-- Background -->
    <div class="bg-map"></div>
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>
    <div class="bg-vignette"></div>

    <!-- Floating pins -->
    <div class="deco deco-1">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="rgba(16,185,129,.7)" stroke="rgba(16,185,129,.9)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>
    <div class="deco deco-2">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="rgba(37,99,235,.7)" stroke="rgba(37,99,235,.9)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>
    <div class="deco deco-3">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="rgba(245,158,11,.6)" stroke="rgba(245,158,11,.8)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>
    <div class="deco deco-4">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="rgba(124,58,237,.6)" stroke="rgba(124,58,237,.8)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>

    <!-- Card -->
    <div class="card-wrapper">
        <div class="card">

            <div class="card-brand">
                <div class="brand-icon">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                </div>
                <div>
                    <div class="brand-name">Lokasi Wisata</div>
                    <div class="brand-sub-name">Sistem Pencarian & Peta</div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="card-heading">
                <h1>Buat akun baru</h1>
                <p>Isi data di bawah untuk mulai menggunakan aplikasi.</p>
            </div>

            @if ($errors->any())
                <div class="error-box">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/register" id="registerForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <div class="input-wrap">
                        <input type="text" id="name" name="name" class="form-input"
                               value="{{ old('name') }}" placeholder="Nama lengkap Anda" required autofocus>
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" class="form-input"
                               value="{{ old('email') }}" placeholder="contoh@email.com" required>
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                               class="form-input has-toggle" placeholder="Minimal 8 karakter"
                               required oninput="checkStrength(this.value)">
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <button type="button" class="toggle-btn" onclick="togglePass('password',this)" aria-label="Tampilkan password">
                            <svg class="eye-on"  width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-label" id="strengthLabel"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-input has-toggle" placeholder="Ulangi password" required>
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </span>
                        <button type="button" class="toggle-btn" onclick="togglePass('password_confirmation',this)" aria-label="Tampilkan konfirmasi">
                            <svg class="eye-on"  width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Buat Akun Sekarang</button>
            </form>

            <div class="card-footer">
                <p>Sudah punya akun? <a href="/login">Masuk di sini</a></p>
                <a href="/" class="back-link">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Kembali ke beranda
                </a>
            </div>
        </div>
    </div>

    <p class="map-credit">Powered by OpenStreetMap & Nominatim API</p>

    <script>
        function togglePass(id, btn) {
            const inp = document.getElementById(id);
            const on = inp.type === 'text';
            inp.type = on ? 'password' : 'text';
            btn.querySelector('.eye-on').style.display  = on ? 'block' : 'none';
            btn.querySelector('.eye-off').style.display = on ? 'none' : 'block';
        }

        function checkStrength(val) {
            const fill = document.getElementById('strengthFill');
            const label = document.getElementById('strengthLabel');
            if (!val) { fill.style.width = '0%'; label.textContent = ''; return; }
            let score = 0;
            if (val.length >= 8)  score++;
            if (val.length >= 12) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const levels = [
                { pct:'20%', color:'#ef4444', text:'Sangat lemah' },
                { pct:'40%', color:'#f97316', text:'Lemah' },
                { pct:'60%', color:'#eab308', text:'Cukup' },
                { pct:'80%', color:'#22c55e', text:'Kuat' },
                { pct:'100%',color:'#2563eb', text:'Sangat kuat' },
            ];
            const lvl = levels[Math.min(score - 1, 4)] || levels[0];
            fill.style.width = lvl.pct;
            fill.style.background = lvl.color;
            label.style.color = lvl.color;
            label.textContent = lvl.text;
        }
    </script>
</body>
</html>
