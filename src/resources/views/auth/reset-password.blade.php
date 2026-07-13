<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — Sistem Lokasi Wisata</title>
    <meta name="description" content="Buat password baru akun Anda.">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="https://petawisata.my.id/reset-password">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/auth.css?v={{ filemtime(base_path('public/css/auth.css')) }}">
</head>
<body>
    <div class="side-visual">
        <div class="brand-mark">PETAWISATA</div>
        <h2>Password baru Anda.</h2>
        <p>Buat password kuat untuk mengamankan akun Petawisata Anda.</p>
    </div>

    <div class="card-wrapper">
        <div class="card">
            <div class="brand-name">PETAWISATA</div>
            <div class="card-heading">
                <h1>Password Baru.</h1>
                <p>Isi password baru untuk akun Anda.</p>
            </div>

            @if(session('error'))
                <div class="error-box">
                    <ul><li>{{ session('error') }}</li></ul>
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

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" class="form-input"
                               value="{{ $email ?? old('email') }}" readonly style="background:#e5e7eb; color:#64748b; cursor:not-allowed;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                               class="form-input has-toggle" placeholder="Minimal 6 karakter" required autofocus>
                        <button type="button" class="toggle-btn" onclick="togglePass('password',this)" aria-label="Tampilkan password">
                            <svg class="eye-on"  width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-input has-toggle" placeholder="Ulangi password baru" required>
                        <button type="button" class="toggle-btn" onclick="togglePass('password_confirmation',this)" aria-label="Tampilkan konfirmasi">
                            <svg class="eye-on"  width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Simpan Password Baru</button>
            </form>
        </div>
    </div>
<script>
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        const eyeOn = btn.querySelector('.eye-on');
        const eyeOff = btn.querySelector('.eye-off');
        if (input.type === 'password') {
            input.type = 'text';
            eyeOn.style.display = 'none';
            eyeOff.style.display = 'block';
        } else {
            input.type = 'password';
            eyeOn.style.display = 'block';
            eyeOff.style.display = 'none';
        }
    }
</script>
</body>
