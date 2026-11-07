<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir POS Desktop — Maya Graha Kencana</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --pos-bg: #ecf0f5;
            --pos-green: #00a65a;
            --pos-green-dark: #008d4c;
            --pos-orange: #f39c12;
            --pos-cyan: #00c0ef;
            --pos-dark: #2c3e50;
            --pos-border: #cbd5e1;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background-color: var(--pos-bg);
            color: #333333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* TOP HEADER */
        .desktop-header {
            background: #ffffff;
            border-bottom: 1px solid #d2d6de;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--pos-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-clock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        /* MAIN LOGIN BOX */
        .login-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
        }

        .pos-login-card {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 8px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .login-card-header {
            background: linear-gradient(135deg, var(--pos-green), var(--pos-green-dark));
            color: #ffffff;
            padding: 22px 24px;
            text-align: center;
        }

        .login-card-header h2 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        .login-card-header p {
            font-size: 12.5px;
            opacity: 0.92;
        }

        .shift-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.38);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            margin-top: 12px;
        }

        .login-card-body {
            padding: 26px 28px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
        }

        .form-select, .form-input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 14px;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            transition: all 0.15s ease;
        }

        .form-select:focus, .form-input:focus {
            background: #ffffff;
            border-color: var(--pos-green);
            box-shadow: 0 0 0 3px rgba(0, 166, 90, 0.15);
        }

        .password-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle-btn {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle-btn:hover {
            color: #1e293b;
        }

        /* AUTO CLOCK-IN BADGE */
        .auto-clockin-info {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
            padding: 10px 14px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            line-height: 1.4;
        }

        /* SUBMIT BUTTON */
        .btn-submit-pos {
            width: 100%;
            background-color: var(--pos-green);
            color: #ffffff;
            border: none;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 700;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 2px 6px rgba(0, 166, 90, 0.35);
            transition: all 0.15s ease;
        }

        .btn-submit-pos:hover {
            background-color: var(--pos-green-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 166, 90, 0.45);
        }

        .btn-submit-pos:active {
            transform: translateY(1px);
        }

        /* ALERT ERRORS */
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        /* FOOTER BAR */
        .desktop-footer {
            background: #ffffff;
            border-top: 1px solid #d2d6de;
            padding: 12px 28px;
            text-align: center;
            font-size: 11.5px;
            color: #64748b;
        }

        .desktop-footer a {
            color: #0284c7;
            text-decoration: none;
            font-weight: 600;
        }

        .desktop-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    {{-- TOP DESKTOP HEADER --}}
    <header class="desktop-header">
        <div class="header-title">
            <span style="font-size: 22px;">🛒</span>
            <span>MAYA GRAHA KENCANA — Sistem Kasir POS Desktop</span>
        </div>
        <div class="header-clock" id="clock-display">
            {{ date('d M Y') }} • {{ date('H:i:s') }}
        </div>
    </header>

    {{-- MAIN LOGIN AREA --}}
    <main class="login-wrapper">
        <div class="pos-login-card">
            <div class="login-card-header">
                <h2>Login Kasir POS Desktop</h2>
                <p>Silakan pilih kasir dinas dan masukkan password untuk mulai transaksi</p>
                <div class="shift-pill-badge">
                    <span>🕒 Shift Aktif:</span>
                    <strong>{{ $activeShift->name ?? 'Shift ' . ($activeShift->id ?? 1) }}</strong>
                    <span>({{ substr($activeShift->start_time ?? '07:00', 0, 5) }} - {{ substr($activeShift->end_time ?? '15:00', 0, 5) }})</span>
                </div>
            </div>

            <div class="login-card-body">
                @if(isset($activeOnDutyCashier) && $activeOnDutyCashier && $activeOnDutyCashier->employee)
                    <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #92400e; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <span style="font-weight: 700;">👤 Kasir Sedang Dinas:</span>
                            <strong>{{ $activeOnDutyCashier->employee->name }}</strong>
                            <span style="color: #b45309;">(Shift {{ $activeOnDutyCashier->shift?->name ?? ($activeOnDutyCashier->shift_id ?? '1') }} &bull; Masuk {{ $activeOnDutyCashier->clock_in_at?->format('H:i') ?? '-' }})</span>
                        </div>
                        <a href="{{ route('presensi2.serah-terima') }}" style="color: #b45309; text-decoration: underline; font-weight: 700; font-size: 11.5px;">
                            Serah Terima Shift &rarr;
                        </a>
                    </div>
                @endif

                @if(isset($errors) && $errors->any())
                    <div class="alert-error">
                        @if(session('active_guard_error'))
                            <div style="font-weight: 800; font-size: 12.5px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; color: #991b1b;">
                                <span>🛡️</span> KEAMANAN ISO 27001 — PENGUNCIAN KASIR AKTIF
                            </div>
                            <div style="line-height: 1.45;">{{ $errors->first() }}</div>
                            <div style="margin-top: 10px; border-top: 1px dashed rgba(153, 27, 27, 0.3); padding-top: 8px; display: flex; justify-content: flex-end;">
                                <a href="{{ route('presensi2.serah-terima') }}" style="display: inline-flex; align-items: center; gap: 6px; background: #991b1b; color: #ffffff; padding: 6px 12px; border-radius: 4px; font-size: 11.5px; text-decoration: none; font-weight: 700;">
                                    <span>🤝</span> Buka Layar Serah Terima Kasir
                                </a>
                            </div>
                        @else
                            ✕ {{ $errors->first() }}
                        @endif
                    </div>
                @endif
                @if(session('success'))
                    <div style="background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 10px 14px; border-radius: 5px; font-size: 12px; font-weight: 600; margin-bottom: 16px;">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('pos.login.post') }}" method="POST" id="form-pos-login">
                    @csrf

                    {{-- NAMA KASIR BERTUGAS (INPUT TEKS BIASA) --}}
                    <div class="form-group">
                        <label class="form-label" for="employee_name">Nama Kasir Bertugas *</label>
                        <input type="text" name="employee_name" id="employee_name" class="form-input"
                               list="cashier-datalist"
                               placeholder="Ketik nama lengkap atau NIK kasir..."
                               value="{{ old('employee_name') }}"
                               required autofocus autocomplete="username">
                        <datalist id="cashier-datalist">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->name }}">{{ $emp->code ?? 'OP' }} — {{ $emp->branch->name ?? 'MAYA 1' }}</option>
                            @endforeach
                        </datalist>
                        <small style="color: #64748b; font-size: 11px; margin-top: 4px; display: block;">
                            Contoh: Ketik nama Anda (misal: Bagus Satoto) atau NIK kasir.
                        </small>
                    </div>

                    {{-- PASSWORD KASIR (DENGAN TOGGLE SHOW/HIDE) --}}
                    <div class="form-group">
                        <label class="form-label" for="input-password">Password Kasir *</label>
                        <div class="password-wrap">
                            <input type="password" name="credential" id="input-password" required
                                   class="form-input"
                                   placeholder="Masukkan password kasir..."
                                   autocomplete="current-password">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" title="Lihat/Sembunyikan Password">
                                <span id="toggle-eye-icon">👁️</span>
                            </button>
                        </div>
                    </div>

                    {{-- INFORMASI AUTO CLOCK-IN --}}
                    <div class="auto-clockin-info">
                        <span style="font-size: 18px;">⏱️</span>
                        <span><strong>Otomatis Presensi Masuk (Clock In):</strong> Sistem langsung mencatatkan jam kedatangan kasir shift ini begitu login berhasil.</span>
                    </div>

                    {{-- TOMBOL SUBMIT --}}
                    <button type="submit" class="btn-submit-pos">
                        <span>🔓</span> Masuk Kasir POS Desktop (Enter)
                    </button>
                </form>
            </div>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="desktop-footer">
        Maya Graha Kencana &copy; {{ date('Y') }} &bull; Butuh mode mobile? <a href="{{ route('presensi2.login') }}">Buka Portal Presensi Versi HP</a>
    </footer>

    {{-- SCRIPTS --}}
    <script>
        // Realtime Clock
        setInterval(function() {
            var now = new Date();
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            var dateStr = String(now.getDate()).padStart(2, '0') + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
            var timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');
            var el = document.getElementById('clock-display');
            if (el) el.innerText = dateStr + ' • ' + timeStr;
        }, 1000);

        // Toggle Password Show/Hide
        function togglePasswordVisibility() {
            var input = document.getElementById('input-password');
            var icon = document.getElementById('toggle-eye-icon');
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.innerText = '🙈';
            } else {
                input.type = 'password';
                if (icon) icon.innerText = '👁️';
            }
        }
    </script>
</body>
</html>
