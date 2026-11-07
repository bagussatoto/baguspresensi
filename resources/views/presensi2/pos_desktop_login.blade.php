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
            padding: 24px 16px;
        }

        .pos-login-card {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 8px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .login-card-header {
            background: linear-gradient(135deg, var(--pos-green), var(--pos-green-dark));
            color: #ffffff;
            padding: 20px 24px;
            text-align: center;
        }

        .login-card-header h2 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        .login-card-header p {
            font-size: 12px;
            opacity: 0.9;
        }

        .shift-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.35);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            margin-top: 10px;
        }

        .login-card-body {
            padding: 24px 28px;
        }

        .form-group {
            margin-bottom: 16px;
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
            padding: 10px 14px;
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

        .pin-input-wrap {
            position: relative;
        }

        .form-input-pin {
            font-family: 'JetBrains Mono', monospace;
            font-size: 22px;
            letter-spacing: 6px;
            text-align: center;
            font-weight: 700;
        }

        /* ON-SCREEN NUMPAD GRID (3x4) */
        .numpad-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin: 14px 0 18px 0;
        }

        .btn-num {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            cursor: pointer;
            transition: all 0.1s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .btn-num:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }

        .btn-num:active {
            background: #cbd5e1;
            transform: translateY(1px);
        }

        .btn-num.clear {
            color: #dd4b39;
            font-size: 14px;
            font-weight: 800;
        }

        .btn-num.backspace {
            color: #f39c12;
            font-size: 16px;
        }

        /* AUTO CLOCK-IN BADGE */
        .auto-clockin-info {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
            padding: 9px 12px;
            border-radius: 5px;
            font-size: 11.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
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
                <p>Silakan pilih kasir dinas dan masukkan PIN untuk mulai transaksi</p>
                <div class="shift-pill-badge">
                    <span>🕒 Shift Aktif:</span>
                    <strong>{{ $activeShift->name ?? 'Shift ' . ($activeShift->id ?? 1) }}</strong>
                    <span>({{ substr($activeShift->start_time ?? '07:00', 0, 5) }} - {{ substr($activeShift->end_time ?? '15:00', 0, 5) }})</span>
                </div>
            </div>

            <div class="login-card-body">
                @if(isset($errors) && $errors->any())
                    <div class="alert-error">
                        ✕ {{ $errors->first() }}
                    </div>
                @endif
                @if(session('success'))
                    <div style="background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 10px 14px; border-radius: 5px; font-size: 12px; font-weight: 600; margin-bottom: 16px;">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('pos.login.post') }}" method="POST" id="form-pos-login">
                    @csrf

                    {{-- PILIH KASIR --}}
                    <div class="form-group">
                        <label class="form-label" for="employee_id">Nama Kasir Bertugas *</label>
                        <select name="employee_id" id="employee_id" class="form-select" required autofocus>
                            <option value="">-- Pilih Nama Kasir --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->code ?? 'OP' }}) — {{ $emp->branch->name ?? 'MAYA 1' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PIN KASIR --}}
                    <div class="form-group">
                        <label class="form-label" for="input-pin">PIN / Password Kasir *</label>
                        <div class="pin-input-wrap">
                            <input type="password" name="credential" id="input-pin" required
                                   class="form-input form-input-pin"
                                   placeholder="••••••" maxlength="16" autocomplete="current-password">
                        </div>
                    </div>

                    {{-- ON-SCREEN NUMPAD (TOUCH / MOUSE CLICK) --}}
                    <div class="numpad-grid">
                        <button type="button" class="btn-num" onclick="appendPin('1')">1</button>
                        <button type="button" class="btn-num" onclick="appendPin('2')">2</button>
                        <button type="button" class="btn-num" onclick="appendPin('3')">3</button>
                        <button type="button" class="btn-num" onclick="appendPin('4')">4</button>
                        <button type="button" class="btn-num" onclick="appendPin('5')">5</button>
                        <button type="button" class="btn-num" onclick="appendPin('6')">6</button>
                        <button type="button" class="btn-num" onclick="appendPin('7')">7</button>
                        <button type="button" class="btn-num" onclick="appendPin('8')">8</button>
                        <button type="button" class="btn-num" onclick="appendPin('9')">9</button>
                        <button type="button" class="btn-num clear" onclick="clearPin()">CLEAR</button>
                        <button type="button" class="btn-num" onclick="appendPin('0')">0</button>
                        <button type="button" class="btn-num backspace" onclick="backspacePin()">⌫</button>
                    </div>

                    {{-- INFORMASI AUTO CLOCK-IN --}}
                    <div class="auto-clockin-info">
                        <span style="font-size: 16px;">⏱️</span>
                        <span><strong>Otomatis Presensi Masuk (Clock In):</strong> Sistem langsung mencatatkan jam kedatangan kasir shift ini begitu login berhasil.</span>
                    </div>

                    {{-- TOMBOL SUBMIT --}}
                    <button type="submit" class="btn-submit-pos">
                        <span>🔓</span> Buka Kasir POS Desktop (Enter)
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

        // Numpad Handlers
        var pinInput = document.getElementById('input-pin');

        function appendPin(digit) {
            if (pinInput && pinInput.value.length < 16) {
                pinInput.value += digit;
                pinInput.focus();
            }
        }

        function clearPin() {
            if (pinInput) {
                pinInput.value = '';
                pinInput.focus();
            }
        }

        function backspacePin() {
            if (pinInput && pinInput.value.length > 0) {
                pinInput.value = pinInput.value.slice(0, -1);
                pinInput.focus();
            }
        }
    </script>
</body>
</html>
