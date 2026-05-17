<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Pencarian Lokasi Wisata</title>
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
            background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #06b6d4 100%);
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
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
            font-size: 52px;
            margin-bottom: 24px;
            animation: pulse 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(255,255,255,0.3));
            position: relative; z-index: 1;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(5deg); }
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
            color: rgba(255,255,255,0.8);
            text-align: center;
            max-width: 300px;
            line-height: 1.7;
            position: relative; z-index: 1;
        }

        .steps-list {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: relative; z-index: 1;
            width: 100%;
            max-width: 300px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .step-num {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .step-text {
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            line-height: 1.4;
        }

        /* ===== RIGHT PANEL ===== */
        .right-panel {
            width: 500px;
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
            background: linear-gradient(180deg, rgba(5,150,105,0.04) 0%, transparent 40%);
            pointer-events: none;
        }

        .form-wrapper {
            width: 100%;
            max-width: 380px;
            position: relative; z-index: 1;
            animation: slideUp 0.5s ease-out both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header .hi-emoji {
            font-size: 32px;
            display: block;
            margin-bottom: 8px;
        }

        .form-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 14.5px;
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

        /* ===== FORM ===== */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 7px;
            letter-spacing: 0.01em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #9ca3af;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-wrapper:focus-within .input-icon { color: #059669; }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: #fff;
            transition: all 0.25s;
            outline: none;
        }

        .form-input::placeholder { color: #c4c9d4; }

        .form-input:focus {
            border-color: #059669;
            box-shadow: 0 0 0 4px rgba(5,150,105,0.1);
        }

        .has-toggle { padding-right: 44px; }

        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #9ca3af;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-password:hover { color: #059669; }

        .help-text {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 5px;
        }

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
            transition: all 0.4s ease;
            width: 0%;
        }

        .strength-label {
            font-size: 11.5px;
            margin-top: 4px;
            font-weight: 500;
        }

        /* ===== TERMS ===== */
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
            padding: 14px;
            background: #f0fdf4;
            border-radius: 10px;
            border: 1px solid #bbf7d0;
        }

        .terms-row input[type="checkbox"] {
            width: 16px; height: 16px;
            margin-top: 2px;
            accent-color: #059669;
            cursor: pointer;
            flex-shrink: 0;
        }

        .terms-row label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1.5;
        }

        /* ===== SUBMIT ===== */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #059669 0%, #064e3b 100%);
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
            box-shadow: 0 8px 24px rgba(5,150,105,0.45);
        }

        .btn-submit:active { transform: translateY(0); }

        /* ===== AUTH LINKS ===== */
        .auth-links {
            text-align: center;
            margin-top: 24px;
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
        }

        .back-link:hover { color: #059669 !important; text-decoration: none !important; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; }
            .left-panel { padding: 40px 30px; min-height: auto; flex: none; }
            .steps-list { display: none; }
            .right-panel { width: 100%; padding: 36px 24px; min-height: auto; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="brand-logo">🌿</div>
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
                    <span class="error-box-icon">⚠️</span>
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
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            value="{{ old('name') }}"
                            placeholder="Nama lengkap Anda"
                            required
                            autofocus
                        >
                        <span class="input-icon">👤</span>
                    </div>
                </div>

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
                            placeholder="Minimal 6 karakter"
                            required
                            oninput="checkStrength(this.value)"
                        >
                        <span class="input-icon">🔒</span>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Tampilkan password">👁️</button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-label" id="strengthLabel" style="color:#9ca3af;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-input has-toggle"
                            placeholder="Ulangi password Anda"
                            required
                        >
                        <span class="input-icon">🔑</span>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', this)" aria-label="Tampilkan konfirmasi password">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Buat Akun Sekarang →</button>
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
            const fill = document.getElementById('strengthFill');
            const label = document.getElementById('strengthLabel');
            if (!val) {
                fill.style.width = '0%';
                label.textContent = '';
                return;
            }
            let score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
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
