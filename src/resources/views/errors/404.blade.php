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
            background: #0f172a;
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
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            letter-spacing: -4px;
        }
        .map-emoji {
            font-size: 64px;
            display: block;
            margin: 16px 0 8px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-10px); }
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
            padding: 12px 28px; border-radius: 10px;
            background: #3b82f6; color: #fff;
            font-size: 15px; font-weight: 600;
            text-decoration: none; transition: all .2s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-home:hover {
            background: #60a5fa;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59,130,246,.4);
        }
        .dots {
            display: flex; gap: 8px; justify-content: center; margin-top: 40px;
        }
        .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #334155; animation: pulse 1.5s ease-in-out infinite;
        }
        .dot:nth-child(2) { animation-delay: .2s; }
        .dot:nth-child(3) { animation-delay: .4s; }
        @keyframes pulse {
            0%, 100% { opacity: .3; transform: scale(1); }
            50%       { opacity: 1; transform: scale(1.3); background: #3b82f6; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">404</div>
        <span class="map-emoji">🗺️</span>
        <h1>Halaman Tidak Ditemukan</h1>
        <p>Sepertinya lokasi yang Anda cari tidak ada di peta kami.<br>Mungkin URL salah atau halaman sudah dipindahkan.</p>
        <a href="/" class="btn-home">
            🏠 Kembali ke Beranda
        </a>
        <div class="dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
</body>
</html>
