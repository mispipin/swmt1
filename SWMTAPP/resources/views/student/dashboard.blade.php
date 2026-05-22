<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - SWMT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,600,1,0" rel="stylesheet">
    <style>
        :root {
            --ink: #1d2440;
            --muted: #5f6674;
            --line: rgba(17, 24, 39, 0.12);
            --card: rgba(255, 255, 255, 0.82);
            --accent: #074565;
            --accent-light: #0f86c3;
            --ok: #16a34a;
            --shadow: 0 22px 60px rgba(24, 35, 51, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Poppins, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 18% 12%, rgba(155, 216, 243, 0.55), rgba(155, 216, 243, 0) 40%),
                radial-gradient(circle at 84% 18%, rgba(247, 232, 133, 0.52), rgba(247, 232, 133, 0) 38%),
                linear-gradient(140deg, #f2f3ee 0%, #eef8ff 52%, #f7f3d8 100%);
            padding: 24px 16px;
        }

        .wrap {
            width: min(1040px, 100%);
            margin: 0 auto;
            animation: rise-in 0.5s ease-out both;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 12px 30px rgba(24, 35, 51, 0.07);
        }

        .title {
            margin: 0;
            font-size: clamp(1.3rem, 2.2vw, 1.9rem);
            letter-spacing: -0.02em;
            background: linear-gradient(120deg, #0d2f63 0%, #1f2d57 40%, #116ea6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .meta {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .logout-btn {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.8);
            color: #33435f;
            padding: 9px 14px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(32, 49, 77, 0.12);
            background: #ffffff;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .action-card {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 16px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(8px);
            padding: 18px;
            position: relative;
            overflow: hidden;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .action-card::after {
            content: "";
            position: absolute;
            width: 170px;
            height: 170px;
            right: -60px;
            top: -60px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(20, 160, 226, 0.16), rgba(20, 160, 226, 0));
            pointer-events: none;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 26px 54px rgba(24, 35, 51, 0.18);
            border-color: rgba(17, 77, 120, 0.22);
        }

        .action-card h2 {
            margin: 0 0 8px;
            font-size: 1.05rem;
        }

        .action-card p {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 10px;
            color: #fff;
            font: inherit;
            font-weight: 600;
            padding: 11px 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(180deg, #0a5a84 0%, var(--accent) 100%);
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -45%;
            width: 32%;
            height: 100%;
            transform: skewX(-25deg);
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.26), rgba(255,255,255,0));
            transition: left 0.45s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(7, 69, 101, 0.3);
            filter: brightness(1.03);
        }

        .btn:hover::before {
            left: 120%;
        }

        .btn.secondary {
            background: linear-gradient(180deg, #14a0e2 0%, var(--accent-light) 100%);
        }

        .history {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 16px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(8px);
            padding: 14px;
            position: relative;
            overflow: hidden;
        }

        .history::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0f86c3, #23b7ff, #6ad3ff);
        }

        .history-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }

        .history-title {
            margin: 0;
            font-size: 1.05rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .count {
            padding: 4px 9px;
            border-radius: 999px;
            background: rgba(15, 134, 195, 0.12);
            color: #0c6a97;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .history-table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(17, 24, 39, 0.08);
            background: rgba(255, 255, 255, 0.65);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            font-size: 0.88rem;
            vertical-align: top;
        }

        th {
            color: #324362;
            font-size: 0.8rem;
            font-weight: 600;
        }

        tbody tr {
            transition: background-color 0.18s ease;
        }

        tbody tr:hover {
            background: rgba(17, 134, 201, 0.07);
        }

        .score {
            font-weight: 700;
            color: var(--ok);
        }

        .pdf-link {
            text-decoration: none;
            color: #0f86c3;
            font-weight: 600;
            transition: color 0.18s ease;
        }

        .pdf-link:hover {
            color: #074565;
        }

        .empty {
            padding: 16px 8px;
            color: var(--muted);
            font-size: 0.9rem;
            text-align: center;
            min-height: 140px;
            display: grid;
            place-items: center;
            line-height: 1.6;
        }

        .alert {
            margin: 0 0 12px;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.9rem;
        }

        .alert.success {
            background: linear-gradient(90deg, rgba(25, 170, 88, 0.2), rgba(145, 204, 74, 0.2));
            color: #0f6a37;
            border: 1px solid rgba(25, 170, 88, 0.28);
        }

        .resume-card {
            margin: 0 0 14px;
            border-radius: 14px;
            border: 1px solid rgba(15, 134, 195, 0.22);
            background: linear-gradient(120deg, rgba(15, 134, 195, 0.14), rgba(17, 190, 145, 0.14));
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .resume-title {
            margin: 0 0 4px;
            font-size: 1rem;
            font-weight: 700;
            color: #0b4f75;
        }

        .resume-meta {
            margin: 0;
            font-size: 0.86rem;
            color: #1e5a78;
        }

        .resume-btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font: inherit;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
            background: linear-gradient(180deg, #0f86c3 0%, #085a84 100%);
            box-shadow: 0 10px 20px rgba(8, 90, 132, 0.22);
        }

        @keyframes rise-in {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 840px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 620px) {
            th, td {
                font-size: 0.82rem;
                padding: 8px 6px;
            }

            .history-head {
                align-items: flex-start;
                flex-direction: column;
                margin-bottom: 12px;
            }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="topbar">
            <div>
                <h1 class="title">Dashboard Siswa</h1>
                <p class="meta">Mulai tes baru atau cek riwayat hasil tes kamu.</p>
            </div>
            <form action="{{ route('student.logout') }}" method="post">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

        @if (session('success'))
            <p class="alert success">{{ session('success') }}</p>
        @endif

        @if (!empty($inProgressRegistration))
            <section class="resume-card" aria-label="Lanjutkan tes terakhir">
                <div>
                    <p class="resume-title">Ada tes yang belum selesai</p>
                    <p class="resume-meta">
                        Terakhir dibuat: {{ $inProgressRegistration->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <a class="resume-btn" href="{{ route('test.start', $inProgressRegistration) }}">Lanjutkan Tes Terakhir</a>
            </section>
        @endif

        <section class="grid" aria-label="Pilihan memulai tes">
            <article class="action-card">
                <h2>Mulai Dengan Kode Kelas</h2>
                <p>Masukkan kode dari guru, lalu lanjut pendaftaran sebelum tes.</p>
                <a class="btn" href="{{ route('student.start.with-code') }}">
                    <span class="material-symbols-rounded">key</span>
                    Masukkan Kode
                </a>
            </article>

            <article class="action-card">
                <h2>Tes Mandiri (Tanpa Kode)</h2>
                <p>Langsung isi pendaftaran tanpa kode kelas, kemudian lanjut tes.</p>
                <form action="{{ route('student.start.independent') }}" method="post">
                    @csrf
                    <button class="btn secondary" type="submit">
                        <span class="material-symbols-rounded">rocket_launch</span>
                        Mulai Tes Mandiri
                    </button>
                </form>
            </article>
        </section>

        <section class="history" aria-label="Riwayat pengerjaan siswa">
            <div class="history-head">
                <h2 class="history-title">
                    <span class="material-symbols-rounded" aria-hidden="true">history</span>
                    History Mengerjakan
                </h2>
                <span class="count">{{ $history->count() }} hasil</span>
            </div>

            @if ($history->isEmpty())
                <p class="empty">Belum ada riwayat tes selesai. Mulai tes dari pilihan di atas.</p>
            @else
                <div class="history-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Sekolah</th>
                                <th>Kelas</th>
                                <th>Skor</th>
                                <th>Waktu Test</th>
                                <th>PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->school }}</td>
                                    <td>{{ $item->class_name }}</td>
                                    <td class="score">{{ $item->total_poin }} poin</td>
                                    <td>{{ $item->tested_at ? $item->tested_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <a class="pdf-link" href="{{ route('test.result.pdf', $item) }}">Unduh PDF</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</body>
</html>
