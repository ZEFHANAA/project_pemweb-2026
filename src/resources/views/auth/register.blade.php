<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Sistem Lokasi Wisata</title>
    <meta name="description" content="Buat akun baru untuk menyimpan dan mengelola lokasi wisata favorit Anda.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://petawisata.my.id/register">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Daftar — Petawisata">
    <meta property="og:description" content="Buat akun Petawisata untuk menyimpan lokasi wisata favorit.">
    <meta property="og:url" content="https://petawisata.my.id/register">
    <meta property="og:site_name" content="Petawisata">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/auth.css?v={{ filemtime(base_path('public/css/auth.css')) }}">
</head>
<body>
    <div class="side-visual">
        <div class="brand-mark">PETAWISATA</div>
        <h2>Rencana perjalanan Anda,<br>simpan di sini.</h2>
        <p>Buat akun untuk menyimpan lokasi wisata favorit dan akses dari mana saja.</p>
    </div>

    <div class="card-wrapper">
        <div class="card">
            <div class="brand-name">PETAWISATA</div>
            <div class="card-heading">
                <h1>Daftar.</h1>
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
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" class="form-input"
                               value="{{ old('email') }}" placeholder="contoh@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                               class="form-input has-toggle" placeholder="Minimal 8 karakter"
                               required oninput="checkStrength(this.value)">
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

<script>
    // Password strength gauge — register-only
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
            { pct:'100%',color:'#0e7490', text:'Sangat kuat' },
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