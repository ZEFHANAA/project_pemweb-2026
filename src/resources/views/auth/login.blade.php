<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pencarian Lokasi Wisata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --accent: #06b6d4;
            --success: #10b981;
            --error: #ef4444;
            --text: #1e1b4b;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --bg-card: rgba(255,255,255,0.95);
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0f0c29;
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
            padding: 60px 40px;
            background: linear-gradient(135deg, #1a1a6e 0%, #4f46e5 50%, #06b6d4 100%);
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -150px; left: -150px;
            animation: float 8s ease-in-out infinite;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(6,182,212,0.15);
            bottom: -80px; right: -80px;
            animation: float 6s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        .brand-logo {
            font-size: 48px;
            margin-bottom: 24px;
            animation: pulse 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(255,255,255,0.3));
            position: relative; z-index: 1;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .brand-title {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            text-align: center;
            margin-bottom: 12px;
            line-height: 1.2;
            position: relative; z-index: 1;
        }

        .brand-sub {
            font-size: 15px;
            color: rgba(255,255,255,0.75);
            text-align: center;
            max-width: 300px;
            line-height: 1.6;
            position: relative; z-index: 1;
        }

        .feature-list {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: relative; z-index: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.85);
            font-size: 14px;
        }

        .feature-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,0.15);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            backdrop-filter: blur(4px);
        }

        /* ===== RIGHT PANEL ===== */
        .right-panel {
            width: 480px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 50px;
            background: #fafafa;
            position: relative;
            overflow-y: auto;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(180deg, rgba(79,70,229,0.04) 0%, transparent 40%);
            pointer-events: none;
        }

        .form-wrapper {
            width: 100%;
            max-width: 360px;
            position: relative; z-index: 1;
        }

        .form-header {
            margin-bottom: 36px;
        }

        .form-header h1 {
            font-size: 30px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 15px;
        }

        .form-header .hi-emoji {
            font-size: 32px;
            display: block;
            margin-bottom: 8px;
        }

        /* ===== ERROR BOX ===== */
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid var(--error);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .error-box-icon { font-size: 18px; flex-shrink: 0; }

        .error-box ul {
            list-style: none;
            padding: 0;
            color: #b91c1c;
            font-size: 13.5px;
            line-height: 1.6;
        }

        /* ===== FORM GROUPS ===== */
        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 17px;
            color: #9ca3af;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14.5px;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: #fff;
            transition: all 0.25s;
            outline: none;
        }

        .form-input::placeholder { color: #c4c9d4; }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
        }

        .form-input:focus + .input-icon,
        .input-wrapper:focus-within .input-icon { color: var(--primary); }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 17px;
            color: #9ca3af;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-password:hover { color: var(--primary); }

        /* Password field needs extra right padding */
        .has-toggle { padding-right: 46px; }

        /* ===== SUBMIT BUTTON ===== */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            letter-spacing: 0.02em;
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }

        .btn-submit:hover::before { left: 100%; }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(79,70,229,0.45);
        }

        .btn-submit:active { transform: translateY(0); }

        /* ===== DIVIDER ===== */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0;
            color: #d1d5db;
            font-size: 13px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ===== AUTH LINKS ===== */
        .auth-links {
            text-align: center;
            margin-top: 28px;
        }

        .auth-links p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .auth-links a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .auth-links a:hover { color: var(--primary-dark); text-decoration: underline; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted) !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            margin-top: 6px;
        }

        .back-link:hover { color: var(--primary) !important; text-decoration: none !important; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; }
            .left-panel { padding: 40px 30px; min-height: auto; flex: none; }
            .feature-list { display: none; }
            .brand-title { font-size: 22px; }
            .right-panel { width: 100%; padding: 40px 28px; min-height: auto; }
        }

        /* ===== ANIMATIONS ===== */
        .form-wrapper {
            animation: slideUp 0.5s ease-out both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="brand-logo">🗺️</div>
        <div class="brand-title">Jelajahi Wisata<br>Nusantara</div>
        <p class="brand-sub">Temukan, simpan, dan kelola lokasi wisata favorit Anda dengan mudah.</p>

        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-icon">🔍</div>
                <span>Cari lokasi menggunakan OpenStreetMap</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📍</div>
                <span>Simpan lokasi favorit ke akun Anda</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">✏️</div>
                <span>Edit dan kelola daftar lokasi kapan saja</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📥</div>
                <span>Export data lokasi ke format CSV</span>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <div class="form-wrapper">
            <div class="form-header">
                <span class="hi-emoji">👋</span>
                <h1>Selamat datang!</h1>
                <p>Masuk ke akun Anda untuk melanjutkan.</p>
            </div>

            @if ($errors->any())
                <div class="error-box">
                    <span class="error-box-icon">⚠️</span>
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
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            value="{{ old('email') }}"
                            placeholder="contoh@email.com"
                            required
                            autofocus
                        >
                        <span class="input-icon">✉️</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input has-toggle"
                            placeholder="Masukkan password Anda"
                            required
                        >
                        <span class="input-icon">🔒</span>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Tampilkan/sembunyikan password">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Masuk Sekarang →</button>
            </form>

            <div class="auth-links">
                <p>Belum punya akun? <a href="/register">Daftar gratis</a></p>
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
    </script>
</body>
</html>
