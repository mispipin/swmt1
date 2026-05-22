<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masukkan Kode Kelas - SWMT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1d2440;
            --muted: #5f6674;
            --line: rgba(17, 24, 39, 0.12);
            --card: rgba(255, 255, 255, 0.86);
            --accent: #074565;
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
                #f2f3ee;
            display: grid;
            place-items: center;
            padding: 16px;
        }

        .card {
            width: min(460px, 100%);
            background: var(--card);
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 26px 72px rgba(24, 35, 51, 0.14);
            backdrop-filter: blur(9px);
            padding: 24px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(1.4rem, 3.4vw, 1.9rem);
            line-height: 1.1;
        }

        .subtitle {
            margin: 0 0 18px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.76);
            outline: none;
            font: inherit;
            color: #1f2739;
            padding: 11px 12px;
            margin-bottom: 14px;
        }

        input:focus {
            border-color: rgba(15, 134, 195, 0.48);
            box-shadow: 0 0 0 3px rgba(15, 134, 195, 0.14);
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            border-radius: 10px;
            color: #fff;
            font: inherit;
            font-weight: 600;
            padding: 10px 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #0a5a84 0%, var(--accent) 100%);
        }

        .btn.secondary {
            background: rgba(255, 255, 255, 0.84);
            color: #33435f;
            border: 1px solid var(--line);
        }

        .alert {
            margin: 0 0 14px;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.9rem;
            line-height: 1.4;
            background: rgba(220, 64, 64, 0.12);
            color: #8e2432;
            border: 1px solid rgba(220, 64, 64, 0.28);
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Masukkan Kode Kelas</h1>
        <p class="subtitle">Setelah ini kamu akan diarahkan ke halaman pendaftaran, lalu ke langkah tes dan mulai mengerjakan.</p>

        @if ($errors->any())
            <p class="alert">{{ $errors->first() }}</p>
        @endif

        <form action="{{ route('student.start.with-code.submit') }}" method="post">
            @csrf
            <label for="class_code">Kode Kelas</label>
            <input id="class_code" name="class_code" type="text" value="{{ old('class_code') }}" placeholder="Contoh: AB123" required>

            <div class="actions">
                <button class="btn" type="submit">Lanjut ke Pendaftaran</button>
                <a class="btn secondary" href="{{ route('student.dashboard') }}">Kembali Dashboard</a>
            </div>
        </form>
    </main>
</body>
</html>
