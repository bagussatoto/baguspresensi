@extends('layouts.presensi2')

@section('title', 'Serah Terima Kas & Shift')

@section('content')
<div class="serah-terima-page">
    <style>
        .st-card {
            background: var(--bg-card);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid var(--border-glass);
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.4);
        }
        .st-stat-card {
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .st-stat-card:hover {
            transform: translateY(-2px);
        }
        .st-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border-glass);
            background: rgba(15, 23, 42, 0.65);
        }
        .st-custom-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            color: var(--text-main);
        }
        .st-custom-table th {
            padding: 12px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-glass);
            white-space: nowrap;
        }
        .st-custom-table td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
            font-size: 12.5px;
        }
        .st-custom-table tr:last-child td {
            border-bottom: none;
        }
        .st-custom-table tr:hover td {
            background: rgba(255, 255, 255, 0.025);
        }
        .shift-badge-group {
            display: inline-flex;
            gap: 4px;
            align-items: center;
            justify-content: center;
        }
        .shift-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .shift-pill.active-amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.45);
            border: 1px solid #f59e0b;
        }
        .shift-pill.active-cyan {
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.45);
            border: 1px solid #0ea5e9;
        }
        .shift-pill.dimmed {
            background: rgba(255, 255, 255, 0.04);
            color: #64748b;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .action-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 30px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .action-icon-btn:hover {
            transform: scale(1.06);
            opacity: 0.92;
        }
    </style>

    @php
        $currentDepositStatus = $salesSummary->deposit_status ?? 'unsubmitted';
        $currentHandoverStatus = $salesSummary?->handover_status ?? 'completed';
        $defaultDepositRecipient = $salesSummary?->handoverRecipientName() ?: ($nextShiftLabel ?? 'Kasir Shift Selanjutnya');
        $handoverAmountVal = (float) ($salesSummary?->handoverAmount() ?? $cashInDrawer);
        $retainedDrawerVal = (float) ($salesSummary?->retained_drawer_cash ?? 0);
        $isAlreadyDeposited = $salesSummary?->hasBeenDeposited() ?? false;
        $totalDrawerPhysical = $handoverAmountVal + $retainedDrawerVal;
    @endphp

    {{-- TOP BAR & HEADER SERAH TERIMA --}}
    <div class="st-card" style="border-color: rgba(14, 165, 233, 0.35); background: linear-gradient(135deg, rgba(14, 165, 233, 0.08), rgba(99, 102, 241, 0.06)); padding: 20px 22px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #0284c7, #0ea5e9); display: flex; align-items: center; justify-content: center; font-size: 26px; color: #fff; box-shadow: 0 6px 18px rgba(14, 165, 233, 0.4); flex-shrink: 0;">
                    🤝
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #38bdf8; font-weight: 700;">
                            Protokol Kasir • Handover & Penyetoran Kas Shift
                        </span>
                        <span class="badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.4); font-size: 10px;">
                            Serah Terima Shift
                        </span>
                    </div>
                    <h2 style="font-size: 20px; font-weight: 800; color: #fff; margin: 3px 0 0 0;">
                        Serah Terima & Histori Setor Kasir
                    </h2>
                    <span style="font-size: 12px; color: var(--text-muted); display: block; margin-top: 2px;">
                        Outlet: <strong>{{ strtoupper($currentEmployee->branch->name ?? 'MAYA 1') }}</strong> • Tanggal: <strong>{{ $workDate->format('d/m/Y') }}</strong> • Shift: <strong>{{ $activeShift->name ?? 'Shift' }}</strong> • Operator: <strong>{{ $currentEmployee->name }}</strong>
                    </span>
                </div>
            </div>

            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <span style="font-size: 11.5px; color: var(--text-sub); margin-right: 4px;">
                    penjualan &gt; <strong style="color: #38bdf8;">{{ $activeShift?->id ?? 'Shift' }}</strong>
                </span>
                <a href="{{ route('presensi2.penjualan') }}" class="btn" style="background: rgba(255, 255, 255, 0.08); border: 1px solid var(--border-glass); color: #e2e8f0; padding: 8px 13px; font-size: 12px; font-weight: 600; text-decoration: none;">
                    <span>🛒</span> Kasir
                </a>
                <a href="{{ route('presensi2.rekap', ['summary_id' => $salesSummary?->id]) }}" class="btn" style="background: rgba(255, 255, 255, 0.08); border: 1px solid var(--border-glass); color: #e2e8f0; padding: 8px 13px; font-size: 12px; font-weight: 600; text-decoration: none;">
                    <span>📊</span> Rekap
                </a>
                <button type="button" onclick="openModalSetorKas({{ $salesSummary?->id ?? 'null' }}, {{ (int) $totalSiapSetor }})" class="btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); border: 1px solid #d97706; color: #ffffff; padding: 8px 14px; font-size: 12px; font-weight: 700; cursor: pointer;">
                    <span>🏦</span> Setor Kas
                </button>
            </div>
        </div>
    </div>

    {{-- BANNER DUAL-CONTROL: PERMINTAAN PERSETUJUAN PENYESUAIAN BILLING MATI LISTRIK --}}
    @if($salesSummary && $salesSummary->isBillingAdjustmentPending() && $currentEmployee->canApproveBillingAdjustment())
        <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.45); border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; color: #fde047; box-shadow: 0 4px 16px rgba(0,0,0,0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="font-weight: 800; font-size: 14px; display: flex; align-items: center; gap: 8px; color: #fbbf24;">
                        <span style="font-size: 18px;">⚠️</span>
                        <span>PERSETUJUAN ADMIN: Pengajuan Kompensasi Mati Listrik Kasir</span>
                        <span style="font-size: 10px; background: #b45309; color: #ffffff; padding: 2px 8px; border-radius: 9999px; font-weight: 700;">Anti-Fraud Dual Control</span>
                    </div>
                    <div style="font-size: 12px; color: #cbd5e1; margin-top: 6px; line-height: 1.5;">
                        Kasir <strong>{{ $salesSummary->employee?->name ?? 'Kasir Shift' }}</strong> mengajukan pemotongan:
                        <strong style="color: #f87171;">-Rp {{ number_format((float) $salesSummary->billing_adjustment_amount, 0, ',', '.') }}</strong>
                        ({{ $salesSummary->billingAdjustmentReasonLabel() }})
                        @if($salesSummary->billing_blackout_minutes) &bull; Durasi: <strong>{{ $salesSummary->billing_blackout_minutes }} mnt</strong> @endif
                        @if($salesSummary->billing_blackout_pcs) &bull; <strong>{{ $salesSummary->billing_blackout_pcs }} PC</strong> @endif
                    </div>
                    @if($salesSummary->billing_adjustment_notes)
                        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px; font-style: italic;">
                            "{{ $salesSummary->billing_adjustment_notes }}"
                        </div>
                    @endif
                    <div style="font-size: 11px; color: #f59e0b; margin-top: 4px;">
                        <em>*Demi keamanan kas dari indikasi kecurangan ("maling"), potongan billing kasir BELUM aktif sampai disetujui Admin/Supervisor.</em>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <form action="{{ route('presensi2.penjualan.penyesuaian-billing.approve', $salesSummary->id) }}" method="POST" onsubmit="return confirm('Setujui pemotongan billing internet Rp {{ number_format((float) $salesSummary->billing_adjustment_amount, 0, ',', '.') }} untuk kasir {{ $salesSummary->employee?->name }}?')">
                        @csrf
                        <button type="submit" style="padding: 9px 16px; background: #16a34a; color: #fff; border: none; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(22, 163, 74, 0.4);">
                            <span>✓</span> Setujui Pemotongan
                        </button>
                    </form>
                    <form action="{{ route('presensi2.penjualan.penyesuaian-billing.reject', $salesSummary->id) }}" method="POST" onsubmit="return confirm('Tolak pengajuan penyesuaian billing ini? Kasir wajib mempertanggungjawabkan omset penuh tanpa potongan.')">
                        @csrf
                        <button type="submit" style="padding: 9px 16px; background: #dc2626; color: #fff; border: none; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <span>✕</span> Tolak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- KARTU KPI RINGKASAN KAS LACI --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="st-stat-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05)); border-color: rgba(16, 185, 129, 0.35);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <span style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700; letter-spacing: 0.04em;">Kas Diserahterimakan</span>
                <span style="font-size: 18px;">💵</span>
            </div>
            <div style="margin-top: 10px;">
                <div style="font-size: 22px; font-weight: 800; color: #34d399; font-family: monospace;">
                    Rp {{ number_format($handoverAmountVal, 0, ',', '.') }}
                </div>
                <span style="font-size: 11px; color: var(--text-muted);">Uang fisik laci shift diserahkan</span>
            </div>
        </div>

        <div class="st-stat-card" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(14, 165, 233, 0.05)); border-color: rgba(14, 165, 233, 0.35);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <span style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700; letter-spacing: 0.04em;">Modal Laci Ditinggal</span>
                <span style="font-size: 18px;">🪙</span>
            </div>
            <div style="margin-top: 10px;">
                <div style="font-size: 22px; font-weight: 800; color: #38bdf8; font-family: monospace;">
                    Rp {{ number_format($retainedDrawerVal, 0, ',', '.') }}
                </div>
                <span style="font-size: 11px; color: var(--text-muted);">Modal receh kembalian di laci</span>
            </div>
        </div>

        <div class="st-stat-card" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(6, 182, 212, 0.05)); border-color: rgba(6, 182, 212, 0.35);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <span style="font-size: 11px; text-transform: uppercase; color: #22d3ee; font-weight: 700; letter-spacing: 0.04em;">Akumulasi Siap Setor</span>
                <span style="font-size: 18px;">📦</span>
            </div>
            <div style="margin-top: 10px;">
                <div style="font-size: 22px; font-weight: 800; color: #22d3ee; font-family: monospace;">
                    Rp {{ number_format($totalSiapSetor, 0, ',', '.') }}
                </div>
                <span style="font-size: 11px; color: var(--text-muted);">{{ $readyToDepositSummaries->count() }} shift siap disetor ke bank</span>
            </div>
        </div>

        <div class="st-stat-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05)); border-color: rgba(245, 158, 11, 0.35);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <span style="font-size: 11px; text-transform: uppercase; color: #fbbf24; font-weight: 700; letter-spacing: 0.04em;">Status Setoran Kas</span>
                <span style="font-size: 18px;">🏦</span>
            </div>
            <div style="margin-top: 10px;">
                @if($isAlreadyDeposited)
                    <div style="font-size: 15px; font-weight: 800; color: #34d399;">
                        ✅ Sudah Disetor
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted);">{{ $salesSummary->depositMethodLabel() }}</span>
                @else
                    <div style="font-size: 15px; font-weight: 800; color: #fbbf24;">
                        ⏳ Di Laci / Siap Setor
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted);">Belum disetor ke bank/kolektor</span>
                @endif
            </div>
        </div>
    </div>

    {{-- BAGIAN 1: TABEL SERAH TERIMA SHIFT (PENDING / AKTIF HARI INI) --}}
    <div class="st-card" style="border-color: rgba(14, 165, 233, 0.35); padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #0284c7, #0ea5e9); padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px; color: #fff;">🤝</span>
                <div>
                    <h3 style="margin: 0; font-size: 14.5px; font-weight: 800; color: #ffffff; letter-spacing: 0.02em;">
                        Serah Terima Shift
                    </h3>
                    <span style="font-size: 11px; color: #e0f2fe;">
                        Shift aktif dan penyerahan kasir yang sedang berjalan
                    </span>
                </div>
            </div>
            <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-weight: 700; font-size: 11px; border: 1px solid rgba(255, 255, 255, 0.35);">
                {{ $todayHandovers->count() > 0 ? $todayHandovers->count() . ' Shift Berjalan' : 'Status: Lengkap' }}
            </span>
        </div>

        <div class="st-table-wrapper" style="border: none; border-radius: 0;">
            <table class="st-custom-table">
                <thead style="background: rgba(2, 132, 199, 0.12);">
                    <tr>
                        <th style="width: 44px; text-align: center;">No</th>
                        <th style="text-align: left;">Tanggal</th>
                        <th style="text-align: left;">Invoice</th>
                        <th style="text-align: left;">Penyerah</th>
                        <th style="text-align: left;">Penerima</th>
                        <th style="text-align: center;">Payment</th>
                        <th style="text-align: right;">Bruto</th>
                        <th style="text-align: right;">Netto</th>
                        <th style="text-align: center; width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($todayHandovers->isNotEmpty())
                        @foreach($todayHandovers as $idx => $th)
                            @php
                                $thDate = $th->work_date ? $th->work_date->format('Y-m-d') : date('Y-m-d');
                                $thTime = ($th->handover_at ?? $th->updated_at)?->format('H:i:s') ?? date('H:i:s');
                                $thBruto = (float) ($th->total_revenue ?? 0);
                                $thNetto = (float) $th->handoverAmount();
                            @endphp
                            <tr>
                                <td style="text-align: center; font-weight: 700; color: #38bdf8;">{{ $idx + 1 }}</td>
                                <td style="font-family: monospace; font-size: 12px; color: #cbd5e1;">
                                    <strong>{{ $thDate }}</strong><br>
                                    <span style="font-size: 11px; color: var(--text-sub);">{{ $thTime }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('presensi2.serah-terima', ['summary_id' => $th->id]) }}" style="color: #38bdf8; font-weight: 700; font-family: monospace; text-decoration: none;">
                                        {{ $th->invoiceNumber() }}
                                    </a>
                                </td>
                                <td>
                                    <strong style="color: #f1f5f9;">{{ strtoupper($th->employee->name ?? 'OP') }}</strong>
                                    <span style="display: block; font-size: 10.5px; color: var(--text-muted);">Shift {{ $th->shiftNumber() }}</span>
                                </td>
                                <td>
                                    <strong style="color: #c4b5fd;">{{ $th->handoverRecipientName() }}</strong>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 10.5px;">
                                        tunai
                                    </span>
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 700; color: #94a3b8;">
                                    Rp {{ number_format($thBruto, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 800; color: #34d399;">
                                    Rp {{ number_format($thNetto, 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    <a href="{{ route('presensi2.rekap', ['summary_id' => $th->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Print Slip Rekap" class="action-icon-btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff;">
                                        🖨️
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 24px; color: var(--text-sub); font-size: 13px;">
                                <div style="display: inline-flex; align-items: center; gap: 8px; color: #94a3b8;">
                                    <span>✨</span>
                                    <span>kosong (Semua serah terima shift telah tercatat dan masuk ke antrean Siap Setor)</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- BAGIAN 2: TABEL SIAP SETOR (CYAN THEME DENGAN TOTAL & ACTION BUTTONS) --}}
    <div class="st-card" style="border-color: rgba(6, 182, 212, 0.45); padding: 0; overflow: hidden; margin-top: 24px;">
        <div style="background: linear-gradient(135deg, #0891b2, #06b6d4); padding: 13px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px; color: #fff;">📥</span>
                <div>
                    <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: 0.02em;">
                        Siap Setor
                    </h3>
                    <span style="font-size: 11px; color: #cffafe;">
                        Daftar kasir shift yang sudah serah terima dan terkumpul di kasir, siap disetorkan
                    </span>
                </div>
            </div>
            <span class="badge" style="background: rgba(255, 255, 255, 0.22); color: #ffffff; font-weight: 700; font-size: 11.5px; border: 1px solid rgba(255, 255, 255, 0.35);">
                Total {{ $readyToDepositSummaries->count() }} Shift Siap Setor
            </span>
        </div>

        <div class="st-table-wrapper" style="border: none; border-radius: 0;">
            <table class="st-custom-table">
                <thead style="background: rgba(6, 182, 212, 0.12);">
                    <tr>
                        <th style="width: 44px; text-align: center;">No</th>
                        <th style="text-align: left;">Tanggal</th>
                        <th style="text-align: center; width: 60px;">Shif</th>
                        <th style="text-align: left;">Invoice</th>
                        <th style="text-align: left;">Penyerah</th>
                        <th style="text-align: left;">Penerima</th>
                        <th style="text-align: center;">Payment</th>
                        <th style="text-align: right;">Nilai</th>
                        <th style="text-align: center; width: 90px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if($readyToDepositSummaries->isNotEmpty())
                        @foreach($readyToDepositSummaries as $idx => $rtd)
                            @php
                                $rtdDateStr = $rtd->work_date ? $rtd->work_date->format('Y-m-d') : date('Y-m-d');
                                $rtdTimeStr = ($rtd->handover_at ?? $rtd->closed_at ?? $rtd->updated_at)?->format('H:i:s') ?? '07:00:00';
                                $rtdNilai = $rtd->handoverAmount();
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="text-align: center; font-weight: 700; color: #22d3ee;">{{ $idx + 1 }}</td>
                                <td style="font-family: monospace; font-size: 12px; color: #cbd5e1; white-space: nowrap;">
                                    <strong style="color: #f8fafc;">{{ $rtdDateStr }}</strong><br>
                                    <span style="font-size: 11px; color: #94a3b8;">{{ $rtdTimeStr }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="shift-pill active-cyan" style="width: 24px; height: 24px; font-size: 11.5px;">
                                        {{ $rtd->shiftNumber() }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('presensi2.serah-terima', ['summary_id' => $rtd->id]) }}" style="color: #38bdf8; font-weight: 700; font-family: monospace; text-decoration: none;">
                                        {{ $rtd->invoiceNumber() }}
                                    </a>
                                </td>
                                <td>
                                    <strong style="color: #f1f5f9; display: block; font-size: 12.5px;">{{ strtoupper($rtd->employee->name ?? 'Kasir') }}</strong>
                                    <span style="font-size: 10.5px; color: var(--text-sub);">Kasir Shift {{ $rtd->shiftNumber() }}</span>
                                </td>
                                <td>
                                    <strong style="color: #38bdf8; display: block; font-size: 12.5px;">{{ $rtd->handoverRecipientName() }}</strong>
                                </td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                                        tunai
                                    </span>
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 800; font-size: 13.5px; color: #34d399; white-space: nowrap;">
                                    Rp {{ number_format($rtdNilai, 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    <a href="{{ route('presensi2.rekap', ['summary_id' => $rtd->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Print Slip Rekap Shift" class="action-icon-btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);">
                                        🖨️
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        {{-- FOOTER BARIS TOTAL NILAI REKAP --}}
                        <tr style="background: rgba(8, 145, 178, 0.15); border-top: 2px solid rgba(6, 182, 212, 0.35);">
                            <td colspan="7" style="text-align: center; padding: 14px 16px; font-weight: 800; font-size: 13px; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em;">
                                Total Nilai Rekap / Siap Setor
                            </td>
                            <td style="text-align: right; padding: 14px 16px; font-family: monospace; font-weight: 900; font-size: 16px; color: #38bdf8; white-space: nowrap;">
                                Rp {{ number_format($totalSiapSetor, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 22px; color: var(--text-muted);">
                                Belum ada shift yang menunggu penyetoran kas.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- BOTTOM ACTION BUTTONS UNTUK SIAP SETOR --}}
        <div style="background: rgba(15, 23, 42, 0.9); padding: 14px 18px; border-top: 1px solid var(--border-glass); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <button type="button" onclick="triggerHardwarePrint()" class="btn" style="background: linear-gradient(135deg, #059669, #10b981); color: #fff; font-weight: 700; font-size: 12.5px; padding: 9px 16px; border: 1px solid rgba(16, 185, 129, 0.5); display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 3px 12px rgba(16, 185, 129, 0.35);">
                <span>🖨️</span> Print Hardware
            </button>

            <button type="button" onclick="openModalSetorKas({{ $salesSummary?->id ?? 'null' }}, {{ (int) $totalSiapSetor }})" class="btn" style="background: linear-gradient(135deg, #f43f5e, #e11d48); color: #fff; font-weight: 700; font-size: 13px; padding: 10px 22px; border: 1px solid #e11d48; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(225, 29, 72, 0.45); cursor: pointer;">
                <span>🏦</span> Setor Kas Sekarang
            </button>
        </div>
    </div>

    {{-- BAGIAN 3: TABEL HISTORY SETOR (AMBER / ORANGE THEME DENGAN BADGE SHIFT 1-4) --}}
    <div class="st-card" style="border-color: rgba(245, 158, 11, 0.45); padding: 0; overflow: hidden; margin-top: 24px;">
        <div style="background: linear-gradient(135deg, #d97706, #f59e0b); padding: 13px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px; color: #fff;">🏦</span>
                <div>
                    <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: 0.02em;">
                        History setor
                    </h3>
                    <span style="font-size: 11px; color: #fef3c7;">
                        Riwayat kasir yang telah melakukan penyetoran uang kas ke rekening bank / kolektor
                    </span>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" onclick="openModalSetorKas({{ $salesSummary?->id ?? 'null' }}, {{ (int) $handoverAmountVal }})"
                        style="padding: 6px 14px; background: #ffffff; color: #d97706; font-size: 11.5px; font-weight: 800; border: none; border-radius: 6px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    + Input Setor Baru
                </button>
                <span class="badge" style="background: rgba(255, 255, 255, 0.22); color: #ffffff; font-weight: 700; font-size: 11px; border: 1px solid rgba(255, 255, 255, 0.35);">
                    Total {{ $historyDepositSummaries->count() }} Data Riwayat
                </span>
            </div>
        </div>

        <div class="st-table-wrapper" style="border: none; border-radius: 0;">
            <table class="st-custom-table">
                <thead style="background: rgba(245, 158, 11, 0.12);">
                    <tr>
                        <th style="width: 44px; text-align: center;">No</th>
                        <th style="text-align: left;">Tanggal</th>
                        <th style="text-align: left;">Nomer</th>
                        <th style="text-align: left;">Outlet</th>
                        <th style="text-align: center; width: 140px;">Shif</th>
                        <th style="text-align: left;">Penyetor</th>
                        <th style="text-align: right;">Nilai</th>
                        <th style="text-align: center; width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if($historyDepositSummaries->isNotEmpty())
                        @foreach($historyDepositSummaries as $idx => $hs)
                            @php
                                $hsDateStr = ($hs->deposited_at ?? $hs->closed_at ?? $hs->work_date)?->format('Y-m-d') ?? now()->toDateString();
                                $hsTimeStr = ($hs->deposited_at ?? $hs->closed_at)?->format('H:i:s') ?? '00:55:00';
                                $hsShiftNum = $hs->shiftNumber();
                                $hsNilai = (float) ($hs->deposited_amount ?? $hs->handoverAmount());
                                $dateShiftMap = $shiftSummariesByDate[$hsDateStr] ?? [];
                                $isDeposited = $hs->hasBeenDeposited();
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="text-align: center; font-weight: 700; color: #fbbf24;">{{ $idx + 1 }}</td>
                                <td style="font-family: monospace; font-size: 12px; color: #cbd5e1; white-space: nowrap;">
                                    <strong style="color: #f8fafc;">{{ $hsDateStr }}</strong><br>
                                    <span style="font-size: 11px; color: #94a3b8;">{{ $hsTimeStr }}</span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <span style="color: #38bdf8; font-weight: 700; font-family: monospace; font-size: 12.5px;">
                                        {{ $hs->depositReceiptNumber() }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <strong style="color: #e2e8f0; font-size: 12.5px;">
                                        {{ strtoupper($hs->branch->name ?? $currentEmployee->branch->name ?? 'MAYA 1') }}
                                    </strong>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <div class="shift-badge-group">
                                        @for($s = 1; $s <= 4; $s++)
                                            @php
                                                $isCurrentShift = ($hsShiftNum === $s);
                                                $hasShiftOnDate = isset($dateShiftMap[$s]);
                                            @endphp
                                            @if($isCurrentShift)
                                                <span class="shift-pill active-amber" title="Shift {{ $s }} (Disetor)">
                                                    {{ $s }}
                                                </span>
                                            @elseif($hasShiftOnDate)
                                                <span class="shift-pill" style="background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4);" title="Shift {{ $s }} pada tanggal ini">
                                                    {{ $s }}
                                                </span>
                                            @else
                                                <span class="shift-pill dimmed">
                                                    {{ $s }}
                                                </span>
                                            @endif
                                        @endfor
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: #f1f5f9; display: block; font-size: 12.5px;">
                                        {{ strtoupper($hs->deposit_verified_by ?: ($hs->employee->name ?? 'OP')) }}
                                    </strong>
                                    <span style="font-size: 10.5px; color: var(--text-sub);">
                                        {{ $hs->depositMethodLabel() }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 800; font-size: 13.5px; color: #fbbf24; white-space: nowrap;">
                                    Rp {{ number_format($hsNilai, 0, ',', '.') }}
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 5px; align-items: center; justify-content: center;">
                                        <a href="{{ route('presensi2.rekap', ['summary_id' => $hs->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Cetak Bukti Setor" class="action-icon-btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff;">
                                            🖨️
                                        </a>
                                        <button type="button" onclick="openModalSetorKas({{ $hs->id }}, {{ (int) $hsNilai }})" title="Detail Setoran Kasir" class="action-icon-btn" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff;">
                                            🚗
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 22px; color: var(--text-muted);">
                                Belum ada riwayat setoran kas yang disetorkan ke bank.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- BAGIAN 4: FORMULIR INPUT SERAH TERIMA SHIFT & CHECK-OUT PULANG --}}
    <div class="st-card" style="border-color: rgba(16, 185, 129, 0.4); background: linear-gradient(135deg, rgba(16, 185, 129, 0.06), rgba(14, 165, 233, 0.04)); margin-top: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>📝</span> Formulir Serah Terima Kasir Shift Aktif
                </h3>
                <span style="font-size: 11.5px; color: var(--text-muted);">
                    Hitung fisik uang di laci dan cantumkan nama kasir shift selanjutnya sebelum mengakhiri shift.
                </span>
            </div>
        </div>

        <form action="{{ route('presensi2.serah-terima.store') }}" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; align-items: end;">
            @csrf
            <input type="hidden" name="summary_id" value="{{ $salesSummary?->id }}">
            <input type="hidden" name="action_type" value="handover">

            <div>
                <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 5px; font-weight: 600;">Nominal Kas Diserahkan (Rp) *</label>
                <input type="number" name="deposited_amount" required min="0" step="100"
                       value="{{ (int) $handoverAmountVal }}"
                       style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(16, 185, 129, 0.5); border-radius: 8px; padding: 10px 12px; color: #34d399; font-weight: 700; font-size: 13.5px; font-family: monospace; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 5px; font-weight: 600;">Modal Laci Ditinggal (Rp)</label>
                <input type="number" name="retained_drawer_cash" min="0" step="100"
                       value="{{ (int) $retainedDrawerVal }}"
                       style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid var(--border-glass); border-radius: 8px; padding: 10px 12px; color: #fff; font-size: 13.5px; font-family: monospace; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 5px; font-weight: 600;">Shift Penerima / Kasir Berikutnya *</label>
                <input type="text" name="deposit_recipient" required placeholder="Contoh: Shift 2 / Grace"
                       value="{{ $defaultDepositRecipient }}"
                       style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid var(--border-glass); border-radius: 8px; padding: 10px 12px; color: #fff; font-size: 13px; outline: none;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 5px; font-weight: 600;">Catatan / Pesan Shift (Opsional)</label>
                <input type="text" name="deposit_notes" placeholder="Pesan serah terima kasir..."
                       value="{{ $salesSummary?->handover_notes ?? $salesSummary?->deposit_notes ?? '' }}"
                       style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid var(--border-glass); border-radius: 8px; padding: 10px 12px; color: #fff; font-size: 13px; outline: none;">
            </div>

            <div style="grid-column: 1 / -1; background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-glass); border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0; color: #e2e8f0; font-size: 12.5px;">
                    <input type="checkbox" name="auto_clock_out" value="1" checked style="width: 18px; height: 18px; accent-color: #10b981; cursor: pointer;">
                    <span><strong>Selesai Serah Terima</strong>: Otomatis lakukan <strong>Check Out Presensi Pulang</strong></span>
                </label>

                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="submit" class="btn btn-emerald" style="padding: 11px 18px; font-size: 13px; font-weight: 700;">
                        <span>🤝</span> Simpan Serah Terima Shift
                    </button>
                    <button type="button" onclick="openModalSetorKas({{ $salesSummary?->id ?? 'null' }}, {{ (int) $handoverAmountVal }})" class="btn" style="padding: 11px 14px; font-size: 13px; font-weight: 700; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: 1px solid #d97706; white-space: nowrap;">
                        <span>🏦</span> Setor Kas
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- MODAL INPUT SETOR KAS (BANK / KOLEKTOR / BRANKAS) --}}
    <div id="modal-setor-kas" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #0f172a; border: 1px solid rgba(245, 158, 11, 0.5); border-radius: 16px; width: 100%; max-width: 480px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.6);">
            <div style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;">🏦</span>
                    <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #fff;">Setor Kas Shift (Bank / Kolektor)</h4>
                </div>
                <button type="button" onclick="closeModalSetorKas()" style="background: transparent; border: none; color: #fff; font-size: 18px; cursor: pointer; font-weight: 700;">✕</button>
            </div>
            <form action="{{ route('presensi2.serah-terima.store') }}" method="POST" style="padding: 18px 20px;">
                @csrf
                <input type="hidden" name="action_type" value="setor">
                <input type="hidden" name="summary_id" id="modal-setor-summary-id" value="{{ $salesSummary?->id }}">

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 5px; font-weight: 600;">Metode Setoran Kas *</label>
                    <select name="deposit_method" required style="width: 100%; background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 9px 12px; color: #fff; font-size: 13px; outline: none;">
                        <option value="bank_transfer">Transfer Rekening Bank</option>
                        <option value="cash_collector">Tunai ke Kolektor / SPV</option>
                        <option value="safe_dropbox">Brankas / Drop Box Outlet</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 5px; font-weight: 600;">Nominal Uang Disetor (Rp) *</label>
                    <input type="number" name="deposited_amount" id="modal-setor-amount" required min="0" step="100"
                           value="{{ (int) $handoverAmountVal }}"
                           style="width: 100%; background: #1e293b; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 8px; padding: 9px 12px; color: #fbbf24; font-weight: 700; font-size: 14px; font-family: monospace; outline: none;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 5px; font-weight: 600;">Penerima / Nomor Rekening Tujuan *</label>
                    <input type="text" name="deposit_recipient" id="modal-setor-recipient" required placeholder="Contoh: BCA MayaNet 12345678 / Kolektor Pak Budi"
                           value="BCA MayaNet Pusat"
                           style="width: 100%; background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 9px 12px; color: #fff; font-size: 12.5px; outline: none;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 5px; font-weight: 600;">Catatan / Keterangan Setoran (Opsional)</label>
                    <input type="text" name="deposit_notes" placeholder="No referensi transfer / catatan..."
                           style="width: 100%; background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 9px 12px; color: #fff; font-size: 12.5px; outline: none;">
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="closeModalSetorKas()" style="padding: 10px 16px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.15); background: #334155; color: #fff; font-size: 12px; font-weight: 600; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit" style="padding: 10px 18px; border-radius: 8px; border: 1px solid #d97706; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-size: 12.5px; font-weight: 700; cursor: pointer;">
                        🏦 Konfirmasi Setor Kas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModalSetorKas(summaryId, defaultAmount) {
        var modal = document.getElementById('modal-setor-kas');
        if (!modal) return;
        if (summaryId) {
            var sumIdInput = document.getElementById('modal-setor-summary-id');
            if (sumIdInput) sumIdInput.value = summaryId;
        }
        if (defaultAmount !== undefined && defaultAmount !== null && defaultAmount > 0) {
            var amountInput = document.getElementById('modal-setor-amount');
            if (amountInput) amountInput.value = defaultAmount;
        }
        modal.style.display = 'flex';
    }

    function closeModalSetorKas() {
        var modal = document.getElementById('modal-setor-kas');
        if (modal) modal.style.display = 'none';
    }

    function triggerHardwarePrint() {
        window.location.href = '{{ route("presensi2.rekap", ["summary_id" => $salesSummary?->id, "auto_print" => 1, "print_type" => "hardware"]) }}';
    }
</script>
@endsection
