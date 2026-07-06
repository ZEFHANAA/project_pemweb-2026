<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sistem Lokasi Wisata</title>
    <meta name="description" content="Masuk ke akun Anda untuk mengakses fitur penyimpanan lokasi wisata.">
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

        /* ===== MAP-LIKE BACKGROUND ===== */
        .bg-map {
            position: fixed;
            inset: 0;
            z-index: 0;
            /* Topographic grid — mimics OSM tile style */
            background-color: #1a2332;
            background-image:
                /* Major grid lines */
                linear-gradient(rgba(37,99,235,.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37,99,235,.12) 1px, transparent 1px),
                /* Minor grid lines */
                linear-gradient(rgba(37,99,235,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37,99,235,.05) 1px, transparent 1px);
            background-size: 120px 120px, 120px 120px, 24px 24px, 24px 24px;
            animation: mapDrift 60s linear infinite;
        }
        @keyframes mapDrift {
            0%   { background-position: 0 0, 0 0, 0 0, 0 0; }
            100% { background-position: 120px 120px, 120px 120px, 24px 24px, 24px 24px; }
        }

        /* Atmospheric color blobs */
        .bg-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }
        .bg-blob-1 { width: 600px; height: 600px; background: rgba(37,99,235,.15);  top: -200px; left: -200px; animation: blobDrift 20s ease-in-out infinite; }
        .bg-blob-2 { width: 500px; height: 500px; background: rgba(124,58,237,.10); bottom: -150px; right: -150px; animation: blobDrift 15s ease-in-out infinite reverse; }
        .bg-blob-3 { width: 350px; height: 350px; background: rgba(16,185,129,.08); top: 40%; left: 35%; animation: blobDrift 25s ease-in-out infinite; animation-delay: -10s; }

        @keyframes blobDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -40px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(.96); }
        }

        /* Floating SVG pin decorations */
        .deco {
            position: fixed;
            z-index: 1;
            pointer-events: none;
            opacity: 0;
            animation: decoAppear 1s ease forwards, decoFloat 6s ease-in-out infinite;
        }
        .deco-1 { top: 15%; left: 8%;  animation-delay: .2s, .2s;  }
        .deco-2 { top: 25%; right: 10%; animation-delay: .5s, .5s; animation-duration: 1s, 8s; }
        .deco-3 { bottom: 20%; left: 12%; animation-delay: .8s, .8s; animation-duration: 1s, 7s; }
        .deco-4 { bottom: 30%; right: 8%; animation-delay: .3s, .3s; animation-duration: 1s, 9s; }
        .deco-5 { top: 60%; left: 22%; animation-delay: 1s, 1s; animation-duration: 1s, 10s; }

        @keyframes decoAppear { to { opacity: .3; } }
        @keyframes decoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        /* Overlay to darken and blur the background near center */
        .bg-vignette {
            position: fixed;
            inset: 0;
            z-index: 1;
            background: radial-gradient(ellipse 70% 70% at 50% 50%,
                transparent 0%,
                rgba(13,17,23,.4) 60%,
                rgba(13,17,23,.7) 100%
            );
        }

        /* ===== CENTERED CARD ===== */
        .card-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 16px;
            animation: cardIn .6s cubic-bezier(.22,1,.36,1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(32px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            padding: 40px 36px 36px;
            box-shadow:
                0 0 0 1px rgba(255,255,255,.15),
                0 24px 80px rgba(0,0,0,.45),
                0 8px 24px rgba(0,0,0,.25);
        }

        /* Brand at top */
        .card-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: #2563eb;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(37,99,235,.4);
        }

        .brand-name {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -.02em;
        }
        .brand-sub-name {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 400;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: #f3f4f6;
            margin: 0 -36px 28px;
        }

        /* Heading */
        .card-heading h1 {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -.4px;
            margin-bottom: 5px;
        }
        .card-heading p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        /* Error */
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 3px solid #ef4444;
            border-radius: 8px;
            padding: 11px 13px;
            margin-bottom: 20px;
        }
        .error-box ul { list-style: none; color: #b91c1c; font-size: 13px; line-height: 1.6; }
        .success-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3px solid #22c55e; border-radius: 8px; padding: 11px 13px; margin-bottom: 20px; color: #15803d; font-size: 13px; line-height: 1.6; }

        /* Form */
        .form-group { margin-bottom: 16px; }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: .01em;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #9ca3af;
            transition: color .2s;
            display: flex; align-items: center;
        }
        .input-wrap:focus-within .input-icon { color: #2563eb; }

        .form-input {
            width: 100%;
            padding: 11px 14px 11px 38px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #111827;
            background: #f9fafb;
            transition: all .2s;
            outline: none;
        }
        .form-input::placeholder { color: #d1d5db; }
        .form-input:focus {
            background: #fff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .has-toggle { padding-right: 42px; }

        .toggle-btn {
            position: absolute;
            right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; padding: 4px;
            color: #9ca3af;
            display: flex; align-items: center;
            transition: color .2s;
        }
        .toggle-btn:hover { color: #2563eb; }

        /* Submit */
        .btn-submit {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            font-size: 14.5px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            color: #fff;
            background: #2563eb;
            transition: all .22s;
            margin-top: 8px;
            letter-spacing: .01em;
        }
        .btn-submit:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(37,99,235,.4);
        }
        .btn-submit:active { transform: none; box-shadow: none; }

        /* Footer links */
        .card-footer {
            text-align: center;
            margin-top: 20px;
        }
        .card-footer p { font-size: 13.5px; color: #6b7280; margin-bottom: 8px; }
        .card-footer a { color: #2563eb; font-weight: 600; text-decoration: none; transition: color .2s; }
        .card-footer a:hover { color: #1d4ed8; text-decoration: underline; }
        .back-link {
            display: inline-flex; align-items: center; gap: 5px;
            color: #9ca3af !important;
            font-size: 12.5px !important;
            font-weight: 400 !important;
        }
        .back-link:hover { color: #2563eb !important; text-decoration: none !important; }

        /* Bottom tag */
        .map-credit {
            position: fixed;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            font-size: 11px;
            color: rgba(255,255,255,.3);
            white-space: nowrap;
        }

        @media (max-width: 480px) {
            .card { padding: 28px 22px 24px; }
            .divider { margin: 0 -22px 24px; }
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

    <!-- Floating pin decorations -->
    <div class="deco deco-1">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="rgba(37,99,235,.7)" stroke="rgba(37,99,235,.9)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>
    <div class="deco deco-2">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="rgba(124,58,237,.7)" stroke="rgba(124,58,237,.9)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>
    <div class="deco deco-3">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="rgba(16,185,129,.7)" stroke="rgba(16,185,129,.9)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>
    <div class="deco deco-4">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="rgba(37,99,235,.5)" stroke="rgba(37,99,235,.7)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>
    <div class="deco deco-5">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="rgba(245,158,11,.6)" stroke="rgba(245,158,11,.8)" stroke-width="1.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3" fill="rgba(255,255,255,.5)"/></svg>
    </div>

    <!-- Card -->
    <div class="card-wrapper">
        <div class="card">

            <!-- Brand -->
            <div class="card-brand">
                <div class="brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                </div>
                <div>
                    <div class="brand-name">Lokasi Wisata</div>
                    <div class="brand-sub-name">Sistem Pencarian & Peta</div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Heading -->
            <div class="card-heading">
                <h1>Selamat datang kembali</h1>
                <p>Masuk untuk melihat lokasi wisata yang Anda simpan.</p>
            </div>

            @if(session('success'))
                <div class="success-box">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error-box">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" class="form-input"
                               value="{{ old('email') }}" placeholder="contoh@email.com" required autofocus>
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>
                        <a href="{{ route('password.request') }}" style="font-size: 12.5px; font-weight: 600; color: #2563eb; text-decoration: none;">Lupa Password?</a>
                    </div>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                               class="form-input has-toggle" placeholder="Masukkan password" required>
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <button type="button" class="toggle-btn" onclick="togglePass('password',this)" aria-label="Tampilkan password">
                            <svg class="eye-on"  width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Masuk ke Akun</button>
            </form>

            <div class="card-footer">
                <p>Belum punya akun? <a href="/register">Daftar gratis</a></p>
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
    </script>
</body>
</html>
