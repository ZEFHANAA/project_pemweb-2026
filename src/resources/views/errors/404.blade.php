<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0c2d3a;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .container {
            text-align: center;
            max-width: 520px;
        }
        .code {
            font-size: clamp(80px, 20vw, 140px);
            font-weight: 800;
            color: #334155;
            line-height: 1;
            letter-spacing: -4px;
        }
        .map-emoji {
            color: #64748b;
            display: block;
            margin: 16px 0 8px;
        }
        h1 {
            font-size: 22px; font-weight: 700;
            color: #f1f5f9; margin-bottom: 10px;
        }
        p {
            font-size: 15px; color: #94a3b8; line-height: 1.7;
            margin-bottom: 32px;
        }
        .btn-home {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 28px; border-radius: 6px;
            background: #f1f5f9; color: #0c2d3a;
            font-size: 15px; font-weight: 600;
            text-decoration: none; transition: background .15s;
            font-family: 'Inter', sans-serif;
        }
        .btn-home:hover {
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">404</div>
        <span class="map-emoji"><svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 8 18"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg></span>
        <h1>Halaman Tidak Ditemukan</h1>
        <p>Sepertinya lokasi yang Anda cari tidak ada di peta kami.<br>Mungkin URL salah atau halaman sudah dipindahkan.</p>
        <a href="/" class="btn-home">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
