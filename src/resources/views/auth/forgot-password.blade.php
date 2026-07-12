<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — Sistem Lokasi Wisata</title>
    <meta name="description" content="Kirim link reset password ke email akun Anda.">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="https://petawisata.my.id/forgot-password">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/auth.css?v={{ filemtime(base_path('public/css/auth.css')) }}">
</head>
<body>
    <div class="side-visual">
        <div class="brand-mark">PETAWISATA</div>
        <h2>Lupa password? Tenang.</h2>
        <p>Kami akan kirim link reset ke email Anda. Cek kotak masuk beberapa menit lagi.</p>
    </div>

    <div class="card-wrapper">
        <div class="card">
            <div class="brand-name">PETAWISATA</div>
            <div class="card-heading">
                <h1>Lupa Password.</h1>
                <p>Masukkan email akun Anda, kami kirim link reset.</p>
            </div>

            @if(session('error'))
                <div class="error-box">
                    <ul><li>{{ session('error') }}</li></ul>
                </div>
            @endif
            @if(session('success'))
                <div class="success-box">{{ session('success') }}</div>
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

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" class="form-input"
                               value="{{ old('email') }}" placeholder="contoh@email.com" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Kirim Link Reset</button>
            </form>

            <div class="card-footer">
                <a href="{{ route('login') }}" class="back-link">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Kembali ke halaman Masuk
                </a>
            </div>
        </div>
    </div>
</body>
