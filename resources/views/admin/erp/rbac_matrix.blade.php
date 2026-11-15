@extends('admin.dashboard.layout')

@section('title', 'Manajemen Matriks Hak Akses RBAC Granular')

@section('content')
<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Manajemen Matriks Hak Akses RBAC Granular</h1>
        <p class="hero-desc-text">
            Konfigurasi izin operasi spesifik (<strong>Create, Read, Update, Delete, Approve</strong>) per jabatan dan modul ERP sesuai standar <em>Matriks_RBAC_ERP.md</em>, prinsip <em>Least Privilege</em>, dan pemisahan tugas (<em>Segregation of Duties</em>).
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.erp.rbac-matrix', ['export' => 'csv']) }}" class="btn-action-sm" style="padding: 10px 16px; font-size: 13px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; text-decoration: none;">
            <span>📥</span> Unduh CSV
        </a>
        <a href="{{ route('admin.karyawan.access-review') }}" class="btn-action-sm" style="padding: 10px 16px; font-size: 13px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8; text-decoration: none;">
            <span>👥</span> Tinjauan Akses Staf
        </a>
        <a href="{{ route('admin.erp') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 16px; font-size: 13px;">
            <span>🏛️</span> Pusat Kendali ERP
        </a>
    </div>
</div>

@if(session('success'))
<div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
    ✅ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fb7185; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
    ⚠️ {{ session('error') }}
</div>
@endif

<!-- BANNER KEBIJAKAN KEAMANAN SOD -->
<div style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 14px; padding: 16px 20px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <span style="font-size: 28px;">🛡️</span>
        <div>
            <h3 style="margin: 0 0 4px; font-size: 15px; font-weight: 700; color: #fff;">
                Proteksi Segregation of Duties (SoD) &amp; Auditor Read-Only Aktif
            </h3>
            <p style="margin: 0; font-size: 12px; color: #cbd5e1;">
                Peran <strong>Auditor Kepatuhan</strong> terkunci secara sistem pada mode <em>Read-Only</em> (tidak dapat diberi izin Create, Update, Delete, maupun Approve). Seluruh perubahan matriks tercatat ke Audit Log WORM.
            </p>
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" onclick="filterRoleGroup('all', this)" class="rbac-tab-btn active-tab" style="padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.4); background: rgba(56, 189, 248, 0.25); color: #fff; font-size: 12px; font-weight: 600; cursor: pointer;">Semua Jabatan ({{ count($roles) }})</button>
        <button type="button" onclick="filterRoleGroup('admin', this)" class="rbac-tab-btn" style="padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.15); background: rgba(255, 255, 255, 0.05); color: #cbd5e1; font-size: 12px; font-weight: 600; cursor: pointer;">Admin &amp; Pusat ({{ count($adminRoles) }})</button>
        <button type="button" onclick="filterRoleGroup('employee', this)" class="rbac-tab-btn" style="padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.15); background: rgba(255, 255, 255, 0.05); color: #cbd5e1; font-size: 12px; font-weight: 600; cursor: pointer;">Operasional Cabang ({{ count($employeeRoles) }})</button>
    </div>
</div>

<!-- FORM MATRIKS RBAC -->
<form id="rbacMatrixForm" method="POST" action="{{ route('admin.erp.rbac-matrix.update') }}">
    @csrf

    @foreach($roles as $roleKey => $roleMeta)
    @php
        $isAuditor = $roleKey === 'auditor';
        $isSuperAdmin = $roleKey === 'super_admin';
    @endphp
    <div class="dashboard-section-card rbac-role-card" data-role-type="{{ $roleMeta['type'] }}" style="margin-bottom: 22px;">
        <div class="section-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div class="section-header-left">
                <div class="section-header-icon" style="background: {{ $roleMeta['type'] === 'admin' ? 'rgba(168, 85, 247, 0.18); color: #c084fc;' : 'rgba(16, 185, 129, 0.18); color: #34d399;' }}">
                    {{ $roleMeta['type'] === 'admin' ? '👑' : '👤' }}
                </div>
                <div>
                    <h3 class="section-title" style="display: flex; align-items: center; gap: 8px;">
                        {{ $roleMeta['label'] }}
                        <span style="font-size: 10px; padding: 2px 8px; border-radius: 999px; background: rgba(255,255,255,0.08); color: #94a3b8; font-family: monospace;">{{ $roleKey }}</span>
                        @if($isAuditor)
                        <span style="font-size: 10px; padding: 2px 8px; border-radius: 999px; background: rgba(244, 63, 94, 0.2); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.35);">🔒 Read-Only Lock</span>
                        @endif
                    </h3>
                    <span class="section-subtitle">Kategori: {{ $roleMeta['badge'] }} &bull; Atur hak akses CRUD &amp; Approval untuk {{ count($modules) }} modul ERP</span>
                </div>
            </div>

            @if(!$isAuditor)
            <div style="display: flex; gap: 6px;">
                <button type="button" onclick="toggleRolePreset('{{ $roleKey }}', 'all')" class="btn-action-sm" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 11px; padding: 5px 10px; cursor: pointer;">Pilih Semua</button>
                <button type="button" onclick="toggleRolePreset('{{ $roleKey }}', 'readonly')" class="btn-action-sm" style="background: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 11px; padding: 5px 10px; cursor: pointer;">Read-Only</button>
                <button type="button" onclick="toggleRolePreset('{{ $roleKey }}', 'none')" class="btn-action-sm" style="background: rgba(244, 63, 94, 0.12); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); font-size: 11px; padding: 5px 10px; cursor: pointer;">Kosongkan</button>
            </div>
            @endif
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-data-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 110px;">Kode</th>
                        <th>Modul ERP</th>
                        <th style="width: 90px; text-align: center;">Create (C)</th>
                        <th style="width: 90px; text-align: center;">Read (R)</th>
                        <th style="width: 90px; text-align: center;">Update (U)</th>
                        <th style="width: 90px; text-align: center;">Delete (D)</th>
                        <th style="width: 95px; text-align: center;">Approve (A)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules as $modKey => $modMeta)
                    @php
                        $perm = $matrix[$roleKey][$modKey] ?? ['can_create' => false, 'can_read' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false];
                    @endphp
                    <tr>
                        <td style="font-family: monospace; color: #38bdf8; font-weight: 700;">{{ $modMeta['code'] }}</td>
                        <td>
                            <div style="font-weight: 600; color: #f8fafc;">{{ $modMeta['label'] }}</div>
                            <div style="font-size: 11px; color: #94a3b8;">{{ $modMeta['desc'] }}</div>
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox"
                                class="perm-cb role-{{ $roleKey }}"
                                data-action="can_create"
                                name="permissions[{{ $roleKey }}][{{ $modKey }}][can_create]"
                                value="1"
                                {{ !empty($perm['can_create']) ? 'checked' : '' }}
                                {{ $isAuditor ? 'disabled' : '' }}
                                style="width: 17px; height: 17px; accent-color: #10b981; cursor: {{ $isAuditor ? 'not-allowed' : 'pointer' }};">
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox"
                                class="perm-cb role-{{ $roleKey }}"
                                data-action="can_read"
                                name="permissions[{{ $roleKey }}][{{ $modKey }}][can_read]"
                                value="1"
                                {{ !empty($perm['can_read']) ? 'checked' : '' }}
                                style="width: 17px; height: 17px; accent-color: #38bdf8; cursor: pointer;">
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox"
                                class="perm-cb role-{{ $roleKey }}"
                                data-action="can_update"
                                name="permissions[{{ $roleKey }}][{{ $modKey }}][can_update]"
                                value="1"
                                {{ !empty($perm['can_update']) ? 'checked' : '' }}
                                {{ $isAuditor ? 'disabled' : '' }}
                                style="width: 17px; height: 17px; accent-color: #fbbf24; cursor: {{ $isAuditor ? 'not-allowed' : 'pointer' }};">
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox"
                                class="perm-cb role-{{ $roleKey }}"
                                data-action="can_delete"
                                name="permissions[{{ $roleKey }}][{{ $modKey }}][can_delete]"
                                value="1"
                                {{ !empty($perm['can_delete']) ? 'checked' : '' }}
                                {{ $isAuditor ? 'disabled' : '' }}
                                style="width: 17px; height: 17px; accent-color: #f43f5e; cursor: {{ $isAuditor ? 'not-allowed' : 'pointer' }};">
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox"
                                class="perm-cb role-{{ $roleKey }}"
                                data-action="can_approve"
                                name="permissions[{{ $roleKey }}][{{ $modKey }}][can_approve]"
                                value="1"
                                {{ !empty($perm['can_approve']) ? 'checked' : '' }}
                                {{ $isAuditor ? 'disabled' : '' }}
                                style="width: 17px; height: 17px; accent-color: #a855f7; cursor: {{ $isAuditor ? 'not-allowed' : 'pointer' }};">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <!-- ACTION BAR SIMPAN -->
    <div class="dashboard-section-card" style="padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; position: sticky; bottom: 16px; z-index: 50; border: 1px solid rgba(56, 189, 248, 0.35); background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px);">
        <div style="font-size: 13px; color: #cbd5e1;">
            Pastikan perubahan hak akses telah sesuai dengan SOP Pemisahan Tugas (<em>Segregation of Duties</em>) sebelum menyimpan.
        </div>
        <div style="display: flex; gap: 12px;">
            <button type="button" onclick="if(confirm('Kembalikan seluruh matriks RBAC ke standar default SoD ISO 27001?')) document.getElementById('resetRbacForm').submit();" class="btn-action-sm" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); padding: 10px 18px; cursor: pointer;">
                <span>🔄</span> Reset ke Default SoD
            </button>
            <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 24px; font-weight: 700; cursor: pointer;">
                <span>💾</span> Simpan Matriks RBAC
            </button>
        </div>
    </div>
</form>

<form id="resetRbacForm" method="POST" action="{{ route('admin.erp.rbac-matrix.reset') }}" style="display: none;">
    @csrf
</form>

<script>
function filterRoleGroup(type, btn) {
    document.querySelectorAll('.rbac-tab-btn').forEach(el => {
        el.style.background = 'rgba(255, 255, 255, 0.05)';
        el.style.borderColor = 'rgba(255, 255, 255, 0.15)';
        el.style.color = '#cbd5e1';
    });
    btn.style.background = 'rgba(56, 189, 248, 0.25)';
    btn.style.borderColor = 'rgba(56, 189, 248, 0.4)';
    btn.style.color = '#fff';

    document.querySelectorAll('.rbac-role-card').forEach(card => {
        if (type === 'all' || card.dataset.roleType === type) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function toggleRolePreset(roleKey, mode) {
    const checkboxes = document.querySelectorAll(`.role-${roleKey}`);
    checkboxes.forEach(cb => {
        if (cb.disabled) return;
        if (mode === 'all') {
            cb.checked = true;
        } else if (mode === 'readonly') {
            cb.checked = cb.dataset.action === 'can_read';
        } else {
            cb.checked = false;
        }
    });
}
</script>
@endsection
