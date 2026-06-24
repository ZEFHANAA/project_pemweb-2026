<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Sistem Lokasi Wisata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #059669;
            --primary-dark: #064e3b;
            --accent: #06b6d4;
            --text: #064e3b;
            --text-body: #1a2e1a;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #001a0d;
            overflow: hidden;
        }

        /* ===== LEFT PANEL ===== */
        .left-panel {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
            background: linear-gradient(135deg, #022c22 0%, #065f46 40%, #059669 70%, #06b6d4 130%);
            background-size: 300% 300%;
            animation: meshShift 12s ease infinite;
            overflow: hidden;
        }

        @keyframes meshShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .dot-grid {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.1) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        /* Glow orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: .5;
            pointer-events: none;
        }
        .orb-1 {
            width: 320px; height: 320px;
            background: radial-gradient(circle, #059669, transparent);
            top: -80px; left: -80px;
            animation: orbFloat 9s ease-in-out infinite;
        }
        .orb-2 {
            width: 240px; height: 240px;
            background: radial-gradient(circle, #06b6d4, transparent);
            bottom: -50px; right: -50px;
            animation: orbFloat 7s ease-in-out infinite reverse;
        }
        .orb-3 {
            width: 180px; height: 180px;
            background: radial-gradient(circle, #34d399, transparent);
            top: 45%; left: 35%;
            animation: orbFloat 11s ease-in-out infinite;
            animation-delay: -4s;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(20px, -25px) scale(1.06); }
            66%       { transform: translate(-12px, 12px) scale(.94); }
        }

        /* Floating pins */
        .pin {
            position: absolute;
            font-size: 26px;
            z-index: 2;
            filter: drop-shadow(0 6px 16px rgba(5,150,105,.7));
            animation: pinFloat 6s ease-in-out infinite;
        }
        .pin-1 { top: 14%; left: 12%; animation-delay: 0s; }
        .pin-2 { top: 55%; right: 10%; font-size: 22px; animation-delay: -2.5s; }
        .pin-3 { bottom: 18%; left: 22%; font-size: 20px; animation-delay: -4.5s; }

        @keyframes pinFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50%       { transform: translateY(-18px) rotate(-8deg); }
        }

        .lp-content {
            position: relative;
            z-index: 3;
            text-align: center;
        }

        .brand-logo {
            font-size: 56px;
            display: block;
            margin-bottom: 20px;
            animation: logoPulse 4s ease-in-out infinite;
            filter: drop-shadow(0 0 24px rgba(255,255,255,.5));
        }

        @keyframes logoPulse {
            0%, 100% { transform: scale(1) rotate(0deg); filter: drop-shadow(0 0 20px rgba(255,255,255,.4)); }
            50%       { transform: scale(1.1) rotate(5deg); filter: drop-shadow(0 0 35px rgba(52,211,153,.9)); }
        }

        .brand-title {
            font-size: 32px;
            font-weight: 900;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 14px;
            letter-spacing: -.5px;
        }

        .brand-sub {
            font-size: 15px;
            color: rgba(255,255,255,.75);
            max-width: 300px;
            line-height: 1.7;
            margin: 0 auto;
        }

        .steps-list {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            width: 100%;
            max-width: 300px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .step-num {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff;
            flex-shrink: 0;
            backdrop-filter: blur(8px);
        }

        .step-text {
            color: rgba(255,255,255,.85);
            font-size: 14px;
            line-height: 1.4;
            text-align: left;
        }

        /* ===== RIGHT PANEL ===== */
        .right-panel {
            width: 510px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 52px;
            background: #fff;
            position: relative;
            overflow-y: auto;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #059669, #34d399, #06b6d4, #059669);
            background-size: 200% 100%;
            animation: borderSlide 4s linear infinite;
        }

        @keyframes borderSlide {
            0%   { background-position: 0%; }
            100% { background-position: 200%; }
        }

        .right-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(5,150,105,.04) 0%, transparent 60%);
            pointer-events: none;
        }

        .form-wrapper {
            width: 100%;
            max-width: 390px;
            position: relative;
            z-index: 1;
            animation: slideIn .55s cubic-bezier(.22,1,.36,1) both;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-header { margin-bottom: 32px; }

        .hi-emoji {
            font-size: 38px;
            display: block;
            margin-bottom: 10px;
        }

        .form-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-body);
            margin-bottom: 8px;
            letter-spacing: -.4px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 14.5px;
        }

        /* Error */
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .error-box ul { list-style: none; color: #b91c1c; font-size: 13.5px; line-height: 1.6; }

        /* Form */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1a2e1a;
            margin-bottom: 7px;
            letter-spacing: .01em;
        }

        .input-wrapper { position: relative; }

        .input-icon {
            position: absolute;
            left: 13px; top: 50%;
            transform: translateY(-50%);
            font-size: 16px; color: #9ca3af;
            pointer-events: none; transition: color .2s;
        }

        .input-wrapper:focus-within .input-icon { color: var(--primary); }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1a2e1a;
            background: #f9fafb;
            transition: all .25s;
            outline: none;
        }

        .form-input::placeholder { color: #c4c9d4; }

        .form-input:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(5,150,105,.1);
        }

        .has-toggle { padding-right: 44px; }

        .toggle-password {
            position: absolute;
            right: 13px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; font-size: 16px;
            color: #9ca3af; padding: 4px;
            transition: color .2s;
        }
        .toggle-password:hover { color: var(--primary); }

        /* Password strength */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: all .4s ease;
            width: 0%;
        }

        .strength-label {
            font-size: 11.5px;
            margin-top: 4px;
            font-weight: 600;
        }

        /* Submit */
        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .25s;
            letter-spacing: .02em;
            position: relative;
            overflow: hidden;
            margin-top: 6px;
            color: #fff;
            background: linear-gradient(135deg, #059669 0%, #064e3b 100%);
        }

        .btn-submit-inner { position: relative; z-index: 1; }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #34d399, #06b6d4);
            opacity: 0;
            transition: opacity .35s;
        }

        .btn-submit:hover::before { opacity: 1; }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(5,150,105,.5);
        }

        .btn-submit:active { transform: translateY(0); }

        /* Auth links */
        .auth-links {
            text-align: center;
            margin-top: 24px;
        }

        .auth-links p { color: var(--text-muted); font-size: 14px; margin-bottom: 10px; }

        .auth-links a {
            color: #059669;
            font-weight: 600;
            text-decoration: none;
            transition: color .2s;
        }

        .auth-links a:hover { color: var(--primary-dark); text-decoration: underline; }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: #9ca3af !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            margin-top: 4px;
        }

        .back-link:hover { color: #059669 !important; text-decoration: none !important; }

        /* Responsive */
        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; }
            .left-panel { padding: 40px 30px; min-height: auto; flex: none; }
            .steps-list, .pin { display: none; }
            .brand-title { font-size: 24px; }
            .right-panel { width: 100%; padding: 36px 24px; min-height: auto; }
        }
    </style>
</head>
<body>

    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="dot-grid"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="pin pin-1">🌿</div>
        <div class="pin pin-2">📍</div>
        <div class="pin pin-3">🏖️</div>

        <div class="lp-content">
            <span class="brand-logo">🌿</span>
            <div class="brand-title">Bergabung Sekarang<br>& Mulai Jelajah!</div>
            <p class="brand-sub">Buat akun gratis dan mulai simpan destinasi wisata impian Anda.</p>

            <div class="steps-list">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-text">Isi form pendaftaran dengan data yang valid</div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-text">Masuk ke akun dan cari lokasi wisata</div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-text">Simpan & kelola lokasi favorit Anda</div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <div class="form-wrapper">
            <div class="form-header">
                <span class="hi-emoji">🚀</span>
                <h1>Buat Akun Baru</h1>
                <p>Gratis selamanya, tanpa syarat tersembunyi.</p>
            </div>

            @if ($errors->any())
                <div class="error-box">
                    <span style="font-size:18px;flex-shrink:0;">⚠️</span>
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
                    <div class="input-wrapper">
                        <input type="text" id="name" name="name" class="form-input"
                               value="{{ old('name') }}" placeholder="Nama lengkap Anda" required autofocus>
                        <span class="input-icon">👤</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" class="form-input"
                               value="{{ old('email') }}" placeholder="contoh@email.com" required>
                        <span class="input-icon">✉️</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password"
                               class="form-input has-toggle" placeholder="Minimal 8 karakter"
                               required oninput="checkStrength(this.value)">
                        <span class="input-icon">🔒</span>
                        <button type="button" class="toggle-password"
                                onclick="togglePassword('password', this)" aria-label="Tampilkan password">👁️</button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-label" id="strengthLabel" style="color:#9ca3af;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-input has-toggle" placeholder="Ulangi password Anda" required>
                        <span class="input-icon">🔑</span>
                        <button type="button" class="toggle-password"
                                onclick="togglePassword('password_confirmation', this)" aria-label="Tampilkan konfirmasi">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span class="btn-submit-inner">Buat Akun Sekarang →</span>
                </button>
            </form>

            <div class="auth-links">
                <p>Sudah punya akun? <a href="/login">Masuk di sini</a></p>
                <a href="/" class="back-link">← Kembali ke beranda</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }

        function checkStrength(val) {
            const fill  = document.getElementById('strengthFill');
            const label = document.getElementById('strengthLabel');
            if (!val) { fill.style.width = '0%'; label.textContent = ''; return; }
            let score = 0;
            if (val.length >= 8)  score++;
            if (val.length >= 12) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const levels = [
                { pct: '20%', color: '#ef4444', text: 'Sangat lemah' },
                { pct: '40%', color: '#f97316', text: 'Lemah' },
                { pct: '60%', color: '#eab308', text: 'Cukup' },
                { pct: '80%', color: '#22c55e', text: 'Kuat' },
                { pct: '100%', color: '#059669', text: '💪 Sangat kuat' },
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
