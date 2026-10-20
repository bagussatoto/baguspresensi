<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Pengaturan Enterprise Presensi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --header-blue: #2c7ea1;
            --header-dark-blue: #236784;
            --subnav-blue: #277799;
            --subnav-active: #195673;
            --body-bg: #f4f6f9;
            --content-border: #d2d6de;
            --text-dark: #333333;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--body-bg); color: var(--text-dark); }
        .main-header { background: linear-gradient(to bottom, var(--header-blue), var(--header-dark-blue)); color: #fff; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; }
        .sub-navbar { background-color: var(--subnav-blue); padding: 0 20px; display: flex; overflow-x: auto; white-space: nowrap; }
        .subnav-item { color: #eaf2f8; text-decoration: none; padding: 10px 14px; font-size: 13px; font-weight: 500; text-transform: uppercase; border-right: 1px solid rgba(255,255,255,0.1); }
        .subnav-item:hover { background: rgba(0,0,0,0.15); color: #fff; }
        .subnav-item.active { background-color: var(--subnav-active); color: #fff; font-weight: 700; }
        .container { max-width: 1200px; margin: 24px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 8px; border: 1px solid var(--content-border); box-shadow: 0 2px 6px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 16px 20px; background: #fafbfc; border-bottom: 1px solid var(--content-border); display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .badge-tag { background: #0284c7; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 700; }
        .setting-row { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: center; }
        .setting-row:last-child { border-bottom: none; }
        .setting-label { font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .setting-desc { font-size: 12px; color: #64748b; line-height: 1.4; }
        .setting-key { font-family: monospace; font-size: 11px; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 3px; display: inline-block; margin-top: 4px; }
        .input-text, .select-box { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; background: #fff; }
        .input-text:focus, .select-box:focus { border-color: #0284c7; }
        .btn-submit { background: #059669; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(5,150,105,0.3); }
        .btn-submit:hover { background: #047857; }
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    @include('admin.partials.erp_navbar')

    <div class="container" style="padding-top: 100px;">
        <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
            <a href="{{ route('admin.presensi') }}" style="padding: 7px 14px; background: #e0f2fe; color: #0369a1; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">📅 Presensi Hari Ini</a>
            <a href="{{ route('admin.presensi.rekap') }}" style="padding: 7px 14px; background: #e0f2fe; color: #0369a1; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">📊 Rekapitulasi Bulanan</a>
            <a href="{{ route('admin.presensi.approval') }}" style="padding: 7px 14px; background: #e0f2fe; color: #0369a1; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">📝 Approval Cuti & Lembur</a>
            <a href="{{ route('admin.presensi.audit-logs') }}" style="padding: 7px 14px; background: #e0f2fe; color: #0369a1; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">🛡️ Audit Trail</a>
            <a href="{{ route('admin.presensi.pengaturan') }}" style="padding: 7px 14px; background: #0284c7; color: #ffffff; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 700;">⚙️ Pengaturan Presensi</a>
        </div>
        @if(session('success'))
            <div class="alert-success">
                ✨ {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.presensi.pengaturan.update') }}">
            @csrf

            @foreach($settingsGrouped as $groupName => $settings)
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>⚙️</span>
                            <span style="text-transform: uppercase;">Kategori: {{ $groupName }}</span>
                        </div>
                        <span class="badge-tag">{{ count($settings) }} Pengaturan</span>
                    </div>

                    @foreach($settings as $setting)
                        <div class="setting-row">
                            <div>
                                <div class="setting-label">{{ $setting->label }}</div>
                                <div class="setting-desc">{{ $setting->description }}</div>
                                <div class="setting-key">{{ $setting->key }}</div>
                            </div>
                            <div>
                                @if($setting->type === 'boolean')
                                    <select name="settings[{{ $setting->key }}]" class="select-box">
                                        <option value="1" {{ $setting->value == '1' || $setting->value === 'true' ? 'selected' : '' }}>Aktif (Enabled)</option>
                                        <option value="0" {{ $setting->value == '0' || $setting->value === 'false' ? 'selected' : '' }}>Nonaktif (Disabled)</option>
                                    </select>
                                @elseif($setting->options && is_array($setting->options))
                                    <select name="settings[{{ $setting->key }}]" class="select-box">
                                        @foreach($setting->options as $opt)
                                            <option value="{{ $opt }}" {{ $setting->value == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @elseif($setting->type === 'integer')
                                    <input type="number" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="input-text">
                                @else
                                    <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="input-text">
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <div style="text-align: right; margin-bottom: 40px;">
                <button type="submit" class="btn-submit">
                    💾 Simpan Semua Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>
</body>
</html>
