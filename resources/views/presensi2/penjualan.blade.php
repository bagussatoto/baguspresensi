@extends('layouts.presensi2')

@section('title', 'Halaman Penjualan & Kasir Shift')

@section('content')
<div class="penjualan-page">
    <style>
        @media (max-width: 992px) {
            .pos-order-grid {
                grid-template-columns: 1fr !important;
            }
        }
        .pos-terlaris-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 10px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            background: #ffffff;
        }
        .pos-terlaris-item:hover {
            background-color: #f1f5f9;
        }
        .pos-terlaris-name {
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }
        .badge-cyan-stock {
            background: #00c0ef;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
        }
        .badge-red-price {
            background: #dd4b39;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: monospace;
        }
        .pos-cat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
            color: #334155;
            transition: background 0.15s;
        }
        .pos-cat-row:hover {
            background: #f8fafc;
        }
        .btn-pos-open {
            background: #00a65a;
            color: #ffffff;
            border: none;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 3px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: filter 0.15s;
        }
        .btn-pos-open:hover {
            filter: brightness(1.1);
        }
        .scroll-chips::-webkit-scrollbar {
            height: 4px;
        }
        .scroll-chips::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    {{-- HEADER OPERASIONAL SHIFT --}}
    <div class="glass-card" style="border-color: rgba(16, 185, 129, 0.25); padding: 18px 20px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #059669, #10b981); display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);">
                    🛒
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">Kasir & Operasional Shift</span>
                    <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin-top: 2px;">
                        Halaman Penjualan
                    </h2>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('presensi2.rekap') }}" class="btn" style="padding: 8px 16px; font-size: 13px; font-weight: 700; background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4); border-radius: 10px; width: auto; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                    <span>📊</span> REKAP
                </a>
                <div style="text-align: right;">
                    <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); font-size: 12px; padding: 4px 12px;">
                        Operator: {{ $currentEmployee->name }}
                    </span>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Shift: <strong style="color: #34d399;">{{ $activeShift->name ?? 'Shift Aktif' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- METRIK RINGKASAN PENDAPATAN SHIFT BERJALAN --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border-glass);">
            <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 14px; padding: 12px 14px;">
                <span style="font-size: 11px; color: var(--text-muted);">Total Pendapatan Shift</span>
                <div id="top-stat-total" style="font-size: 22px; font-weight: 800; color: #34d399; font-family: 'Outfit', monospace; margin-top: 2px;">
                    Rp {{ number_format($totalFnbIncome + $totalInternetIncome, 0, ',', '.') }}
                </div>
            </div>
            <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 14px; padding: 12px 14px;">
                <span style="font-size: 11px; color: var(--text-muted);">1. Total Makanan & Minuman</span>
                <div id="top-stat-fnb" style="font-size: 20px; font-weight: 800; color: #fbbf24; font-family: 'Outfit', monospace; margin-top: 2px;">
                    Rp {{ number_format($totalFnbIncome, 0, ',', '.') }}
                </div>
            </div>
            <div style="background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.2); border-radius: 14px; padding: 12px 14px;">
                <span style="font-size: 11px; color: var(--text-muted);">2. Total Pendapatan Internet</span>
                <div id="top-stat-internet" style="font-size: 20px; font-weight: 800; color: #22d3ee; font-family: 'Outfit', monospace; margin-top: 2px;">
                    Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- TOAST NOTIFIKASI POS --}}
    <div id="pos-toast" style="display: none; position: fixed; top: 24px; right: 24px; z-index: 999999; max-width: 420px; padding: 14px 18px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); animation: fadeInDown 0.3s ease;"></div>

    {{-- 1. ORDERAN POS KASIR OPERASIONAL --}}
    <div class="glass-card" style="padding: 20px; margin-bottom: 16px; border-color: rgba(0, 166, 90, 0.35);">
        
        {{-- TOP POS HEADER: Centered "Orderan POS" & Right "penjualan > 1;" --}}
        <div style="position: relative; display: flex; align-items: center; justify-content: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border-glass);">
            <h2 style="font-size: 22px; font-weight: 700; color: #ffffff; margin: 0; letter-spacing: 0.5px;">
                Orderan POS
            </h2>
            <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); font-size: 11px; font-family: monospace; color: #94a3b8; display: flex; align-items: center; gap: 4px;">
                <span>🛒</span>
                <span>penjualan &gt; 1;</span>
            </div>
        </div>

        {{-- MAIN 2-COLUMN GRID --}}
        <div class="pos-order-grid">
            
            {{-- KOLOM KIRI (Search, Quick Buttons, Terlaris, Status Penjualan) --}}
            <div style="display: flex; flex-direction: column; gap: 10px;">
                
                {{-- 1. PENCARIAN PRODUK --}}
                <div style="display: flex; background: #ffffff; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.25); border: 1px solid #cbd5e1;">
                    <input type="text" id="pos-search" oninput="filterPosMenu()" placeholder="cari produk" 
                           style="flex: 1; border: none; padding: 8px 12px; font-size: 13px; color: #1e293b; outline: none; background: #ffffff;">
                    <button type="button" onclick="filterPosMenu()" 
                            style="border: none; background: #f8fafc; border-left: 1px solid #e2e8f0; padding: 0 14px; cursor: pointer; color: #475569; font-size: 13px;">
                        🔍
                    </button>
                </div>

                {{-- 2. 2 TOMBOL RINGKAS: DATA BILLING & DATA PRINT --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                    {{-- 1. Data & Kunci Billing (Orange/Green Unified) --}}
                    <button type="button" onclick="openModalBilling()" 
                            style="background: {{ $isBillingLocked ? '#b45309' : '#00a65a' }}; color: #ffffff; border: none; padding: 8px 10px; font-size: 11.5px; font-weight: 700; border-radius: 4px; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.3; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.2); transition: filter 0.15s;"
                            onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <span>{{ $isBillingLocked ? '🔒' : '💻' }}</span>
                            <span>{{ $isBillingLocked ? 'Billing Terkunci' : 'Data & Kunci Billing' }}</span>
                        </span>
                        <span style="font-size: 10px; opacity: 0.95; font-family: monospace;">
                            Rp {{ number_format($totalInternetIncome, 0, ',', '.') }} ({{ $occupiedPcCount ?? 0 }}/{{ $totalPcCount ?? 15 }} PC)
                        </span>
                    </button>

                    {{-- 2. Data & Kunci Print Kertas (Cyan Unified) --}}
                    <button type="button" onclick="openModalPrintKertas()" 
                            style="background: {{ $isPrintLocked ? '#0891b2' : '#00c0ef' }}; color: #ffffff; border: none; padding: 8px 10px; font-size: 11.5px; font-weight: 700; border-radius: 4px; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.3; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.2); transition: filter 0.15s;"
                            onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <span>{{ $isPrintLocked ? '🔒' : '🖨️' }}</span>
                            <span>{{ $isPrintLocked ? 'Print Terkunci' : 'Data & Kunci Print' }}</span>
                        </span>
                        <span style="font-size: 10px; opacity: 0.95; font-family: monospace;">
                            {{ $totalPrintPages }} lbr (Rp {{ number_format($totalPrintIncome, 0, ',', '.') }})
                        </span>
                    </button>
                </div>

                {{-- 3. TERLARIS BOX --}}
                <div style="background: #ffffff; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.15); border: 1px solid rgba(0,0,0,0.12);">
                    <div style="background: #dff0d8; color: #3c763d; font-weight: 700; font-size: 12.5px; padding: 8px 12px; border-bottom: 1px solid #d6e9c6;">
                        Terlaris
                    </div>
                    <div id="pos-terlaris-list" style="max-height: 360px; overflow-y: auto;">
                        @forelse($fnbProducts as $fp)
                        @php
                            $catCode = $fp->product?->category?->code ?? 'CAT-OTHER';
                            $stockCount = (int) ($fp->stock_qty ?? 20);
                            $formattedPrice = number_format($fp->base_price, 0, ',', '.');
                        @endphp
                        <div class="pos-terlaris-item" 
                             id="terlaris-item-{{ $fp->id }}"
                             data-id="{{ $fp->id }}"
                             data-name="{{ $fp->name }}"
                             data-cat="{{ $catCode }}"
                             onclick="addToCart({{ $fp->id }})"
                             title="Klik untuk tambah ke Keranjang Belanja">
                            <span class="pos-terlaris-name">{{ $fp->name }}</span>
                            <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                                <span class="badge-cyan-stock" title="Stok Tersedia">{{ $stockCount }}</span>
                                <span class="badge-red-price" title="Harga Satuan">{{ $formattedPrice }}</span>
                            </div>
                        </div>
                        @empty
                        <div style="padding: 16px; text-align: center; color: #94a3b8; font-size: 12px;">
                            Tidak ada produk terdaftar.
                        </div>
                        @endforelse

                        <div id="pos-search-empty-notice" style="display: none; padding: 16px; text-align: center; color: #94a3b8; font-size: 12px;">
                            Produk tidak ditemukan.
                        </div>
                    </div>
                </div>


            </div>

            {{-- KOLOM KANAN: KERANJANG BELANJA BOX --}}
            <div id="keranjang-belanja-box" style="background: #ffffff; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.25); border: 1px solid #00a65a; display: flex; flex-direction: column; min-height: 480px;">
                
                {{-- HEADER KERANJANG: Solid Green (#00a65a) --}}
                <div style="background: #00a65a; color: #ffffff; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700;">
                        <span style="font-size: 18px;">🛒</span>
                        <span>Keranjang Belanja</span>
                        <span id="cart-item-count-badge" style="background: rgba(255,255,255,0.25); font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 600;">0 item</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" onclick="toggleCartBody()" title="Minimize / Expand" 
                                style="background: none; border: none; color: #ffffff; font-size: 16px; font-weight: bold; cursor: pointer; padding: 0 4px; line-height: 1;">
                            ─
                        </button>
                        <button type="button" onclick="clearCart()" title="Kosongkan Keranjang" 
                                style="background: none; border: none; color: #ffffff; font-size: 14px; font-weight: bold; cursor: pointer; padding: 0 4px; line-height: 1;">
                            ✕
                        </button>
                    </div>
                </div>

                {{-- BODY KERANJANG --}}
                <div id="cart-body-content" style="flex: 1; padding: 16px; background: #ffffff; display: flex; flex-direction: column;">
                    
                    {{-- DATA KOSONG PLACEHOLDER (MATCHING USER SCREENSHOT) --}}
                    <div id="cart-empty-placeholder" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 240px; color: #64748b; font-size: 14px;">
                        <div style="font-size: 40px; margin-bottom: 8px; opacity: 0.5;">🛒</div>
                        <div style="font-weight: 600; color: #475569; font-size: 15px;">data kosong</div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Pilih menu di daftar terlaris atau status penjualan di sebelah kiri</div>
                    </div>

                    {{-- CART TABLE & CHECKOUT DETAILS (WHEN ITEMS EXIST) --}}
                    <div id="pos-cart-rows" style="display: none; flex: 1;">
                        <div style="overflow-x: auto; margin-bottom: 14px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #1e293b;">
                                <thead>
                                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                                        <th style="padding: 8px 10px; text-align: left;">Produk</th>
                                        <th style="padding: 8px 10px; text-align: center; width: 110px;">Qty</th>
                                        <th style="padding: 8px 10px; text-align: right; width: 100px;">Harga</th>
                                        <th style="padding: 8px 10px; text-align: right; width: 110px;">Subtotal</th>
                                        <th style="padding: 8px 8px; text-align: center; width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-table-tbody"></tbody>
                            </table>
                        </div>

                        {{-- TARGET PELANGGAN & METODE PEMBAYARAN (RINGKAS) --}}
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-top: auto;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Pelanggan / Unit PC:</label>
                                    <div style="display: flex; gap: 4px;">
                                        <select id="pos-pc-quick-select" onchange="selectPcTarget(this.value)" style="width: 92px; padding: 6px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11.5px; color: #0284c7; font-weight: 700; background: #e0f2fe; cursor: pointer;">
                                            <option value="Pelanggan Umum">Umum</option>
                                            @forelse($pcStations as $st)
                                                <option value="{{ $st->station_code }}">{{ $st->station_code }}</option>
                                            @empty
                                                @for($i = 1; $i <= 10; $i++)
                                                    @php $pcCode = 'PC-' . str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                                    <option value="{{ $pcCode }}">{{ $pcCode }}</option>
                                                @endfor
                                            @endforelse
                                        </select>
                                        <input type="text" id="pos-customer-input" value="Pelanggan Umum" placeholder="Nama / PC..." style="flex: 1; min-width: 0; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; color: #0f172a; background: #ffffff;">
                                    </div>
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Metode Pembayaran:</label>
                                    <select id="pos-payment-select" onchange="onPaymentMethodChanged(this.value)" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; color: #0f172a; font-weight: 600; background: #ffffff;">
                                        <option value="cash">💵 Tunai (Cash)</option>
                                        <option value="qris">📱 QRIS</option>
                                    </select>
                                </div>
                            </div>

                            {{-- RINGKASAN TAGIHAN & TOMBOL TOGGLE DISKON OPSIONAL --}}
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px 10px; margin-bottom: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 12px; font-weight: 700; color: #1e293b;">Total Tagihan:</span>
                                        <button type="button" id="btn-toggle-discount" onclick="togglePosDiscountBox()" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 4px; cursor: pointer;">
                                            🏷️ + Diskon
                                        </button>
                                    </div>
                                    <div style="text-align: right;">
                                        <span id="pos-gross-display" style="display: none; font-size: 11px; color: #94a3b8; text-decoration: line-through; margin-right: 4px;">Rp 0</span>
                                        <strong id="pos-total-display" style="color: #059669; font-size: 15px; font-family: monospace;">Rp 0</strong>
                                    </div>
                                </div>

                                {{-- PANEL DISKON OPSIONAL (DEFAULT TERSEMBUNYI AGAR BERSIH) --}}
                                <div id="pos-discount-collapsible" style="display: none; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #cbd5e1;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 11px; font-weight: 600; color: #475569;">Diskon (%):</span>
                                            <input type="number" id="pos-discount-percent" min="0" max="100" value="0" oninput="calculateDiscountAndChange()" placeholder="0%" style="width: 64px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; font-weight: 700; color: #dc2626; background: #ffffff;">
                                            <span id="pos-discount-amount-text" style="font-size: 11px; color: #dc2626; font-weight: 700;">-Rp 0</span>
                                        </div>
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 9.5px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                            Maks Mandiri: {{ (float) ($currentEmployee->max_discount_approval_percent ?? 0) }}%
                                        </span>
                                    </div>

                                    {{-- PROMPT DUAL CONTROL SUPERVISOR PIN JIKA MELEBIHI BATAS KASIR --}}
                                    <div id="pos-supervisor-discount-box" style="display: none; margin-top: 6px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 5px; padding: 7px 8px;">
                                        <div style="font-size: 10.5px; font-weight: 700; color: #991b1b; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                                            <span>🛡️</span> Melebihi Batas Kasir — Masukkan PIN Supervisor:
                                        </div>
                                        <input type="password" id="pos-supervisor-discount-pin" maxlength="6" placeholder="6-digit PIN Supervisor..." style="width: 100%; padding: 5px 8px; border: 1px solid #f87171; border-radius: 4px; font-size: 12px; font-weight: bold; background: #ffffff; color: #1e293b;">
                                    </div>
                                </div>
                            </div>

                            {{-- CASH CALCULATOR --}}
                            <div id="cash-calculator-section" style="display: block; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 5px; padding: 8px 10px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="font-size: 11px; font-weight: 600; color: #065f46;">Tunai Diterima:</span>
                                    <div style="display: flex; gap: 3px;">
                                        <button type="button" onclick="setCashPreset('exact')" style="background: #059669; color: #ffffff; border: none; border-radius: 3px; padding: 2px 7px; font-size: 10px; font-weight: 700; cursor: pointer;">Uang Pas</button>
                                        <button type="button" onclick="setCashPreset(10000)" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 3px; padding: 2px 6px; font-size: 10px; cursor: pointer;">10k</button>
                                        <button type="button" onclick="setCashPreset(20000)" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 3px; padding: 2px 6px; font-size: 10px; cursor: pointer;">20k</button>
                                        <button type="button" onclick="setCashPreset(50000)" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 3px; padding: 2px 6px; font-size: 10px; cursor: pointer;">50k</button>
                                        <button type="button" onclick="setCashPreset(100000)" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 3px; padding: 2px 6px; font-size: 10px; cursor: pointer;">100k</button>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="number" id="pos-cash-amount" oninput="calculateChange()" placeholder="Nominal Rp..." style="flex: 1; padding: 5px 8px; border: 1px solid #6ee7b7; border-radius: 4px; font-size: 12px; font-weight: 700; color: #065f46; background: #ffffff;">
                                    <div style="font-size: 11.5px; white-space: nowrap;">
                                        <span style="color: #047857; font-weight: 600;">Kembali:</span>
                                        <strong id="pos-change-text" style="color: #059669; font-size: 13px; font-family: monospace;">Rp 0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER KERANJANG: Solid Green (#00a65a) --}}
                <div style="background: #00a65a; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    {{-- Tombol clear (Amber/Orange) --}}
                    <button type="button" onclick="clearCart()" 
                            style="background: #f39c12; color: #ffffff; border: none; padding: 8px 16px; font-size: 12px; font-weight: 700; border-radius: 3px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.2); transition: filter 0.15s;"
                            onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
                        <span>🔄</span> clear
                    </button>

                    {{-- Tombol Simpan Order Penjualan (Dark Green) --}}
                    <button type="button" id="btn-submit-pos" onclick="submitPosOrder()" 
                            style="background: #008d4c; color: #ffffff; border: 1px solid rgba(255,255,255,0.25); padding: 8px 20px; font-size: 12px; font-weight: 700; border-radius: 3px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.25); transition: filter 0.15s;"
                            onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
                        <span id="btn-submit-pos-text">Simpan Order Penjualan</span>
                    </button>
                </div>

            </div>

        </div>

        {{-- BOTTOM ACCORDION: HISTORY PENJUALAN POS --}}
        <div style="margin-top: 14px; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
            {{-- Cyan Header Banner --}}
            <div onclick="toggleHistoryAccordion()" 
                 style="background: #00c0ef; color: #ffffff; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none;">
                <div style="font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <span>History penjualan <strong>pos</strong></span>
                    <span class="badge" style="background: rgba(255,255,255,0.25); color: #ffffff; font-size: 11px; padding: 1px 7px; border-radius: 10px;">{{ $fnbSales->count() }} transaksi shift ini</span>
                </div>
                <div id="history-toggle-icon" style="background: rgba(255,255,255,0.25); width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; line-height: 1;">
                    +
                </div>
            </div>

            {{-- Collapsible Content: History Table --}}
            <div id="history-pos-accordion-body" style="display: none; background: #ffffff; border: 1px solid #00c0ef; border-top: none; padding: 14px;">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left; color: #1e293b;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0; color: #475569; background: #f8fafc;">
                                <th style="padding: 8px 10px;">Jam</th>
                                <th style="padding: 8px 10px;">Invoice (Klik Detail)</th>
                                <th style="padding: 8px 10px;">Item Pesanan</th>
                                <th style="padding: 8px 10px;">Pelanggan</th>
                                <th style="padding: 8px 10px; text-align: right;">Total</th>
                                <th style="padding: 8px 10px; text-align: center;">Bayar / Status</th>
                                <th style="padding: 8px 10px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="fnb-sales-tbody">
                            @forelse($fnbSales as $sale)
                            @php
                                $isCancelled = $sale->payment_status === 'cancelled';
                            @endphp
                            <tr id="sale-row-{{ $sale->id }}" style="border-bottom: 1px solid #f1f5f9; {{ $isCancelled ? 'opacity: 0.65; background: #fff1f2;' : '' }}">
                                <td style="padding: 9px 10px; color: #64748b; white-space: nowrap;">
                                    {{ $sale->created_at->format('H:i') }}
                                </td>
                                <td style="padding: 9px 10px;">
                                    <button type="button" 
                                        onclick='openInvoiceModal(@json($sale))'
                                        title="Klik untuk membuka rincian / batalkan pesanan"
                                        style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0284c7; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                        <span>📄</span>
                                        <span style="{{ $isCancelled ? 'text-decoration: line-through; color: #ef4444;' : '' }}">{{ $sale->invoice_number }}</span>
                                    </button>
                                </td>
                                <td style="padding: 9px 10px; color: #1e293b;">
                                    @foreach($sale->items as $item)
                                        <div style="{{ $isCancelled ? 'text-decoration: line-through; color: #94a3b8;' : '' }}">{{ $item->qty }}x {{ $item->item_name }}</div>
                                    @endforeach
                                </td>
                                <td style="padding: 9px 10px; color: #475569;">
                                    {{ $sale->customer_name }}
                                </td>
                                <td style="padding: 9px 10px; text-align: right; font-weight: 700; color: {{ $isCancelled ? '#94a3b8' : '#059669' }};">
                                    <span style="{{ $isCancelled ? 'text-decoration: line-through;' : '' }}">
                                        Rp {{ number_format($sale->total_fnb, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td style="padding: 9px 10px; text-align: center;">
                                    @if($isCancelled)
                                        <span class="badge" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-size: 10px; font-weight: 700; padding: 2px 7px;">
                                            DIBATALKAN
                                        </span>
                                    @else
                                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 10px; text-transform: uppercase; padding: 2px 7px; font-weight: 700;">
                                            {{ $sale->payment_method }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 9px 10px; text-align: center;">
                                    @if($isCancelled)
                                        <button type="button" onclick='openInvoiceModal(@json($sale))' class="btn" style="padding: 3px 8px; font-size: 11px; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; border-radius: 4px;">
                                            Rincian
                                        </button>
                                    @else
                                        <button type="button" onclick='openInvoiceModal(@json($sale))' class="btn" style="padding: 3px 8px; font-size: 11px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 4px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                            <span>✖</span> Batalkan
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr id="fnb-empty-row">
                                <td colspan="7" style="text-align: center; padding: 20px; color: #64748b; font-size: 12px; background: #f8fafc;">
                                    Belum ada transaksi penjualan pada shift ini. Gunakan form <strong>Orderan POS</strong> di atas untuk mencatat.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- 4 & 5. TOMBOL SELESAI & CHECK OUT PRESENSI --}}
    <div class="glass-card" style="padding: 24px; border-color: rgba(139, 92, 246, 0.3); background: rgba(139, 92, 246, 0.05); text-align: center;">
        <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 6px;">
            🏁 Penutupan Shift & Check Out Presensi
        </h3>
        <p style="font-size: 12px; color: var(--text-muted); max-width: 540px; margin: 0 auto 20px auto;">
            Ketika jam kerja shift telah selesai dan pergantian shift tiba, silakan klik <strong>4. Selesai (Rekap Shift)</strong> lalu lanjutkan dengan <strong>5. Check Out Presensi</strong> untuk konfirmasi kepulangan.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; max-width: 600px; margin: 0 auto;">
            {{-- 4. TOMBOL SELESAI (REKAP SHIFT) --}}
            <button type="button" onclick="toggleForm('modal-rekap-selesai')" class="btn" style="padding: 16px; font-size: 14px; font-weight: 700; background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; box-shadow: 0 4px 16px rgba(14, 165, 233, 0.4);">
                <span>📊</span> 4. Selesai (Rekap Shift)
            </button>

            {{-- 5. TOMBOL CEK OUT PRESENSI --}}
            @if($attendanceToday && !$attendanceToday->clock_out_at)
            <button type="button" onclick="submitAttendanceCheckOut()" id="btn-clock-out-penjualan" class="btn btn-rose" style="padding: 16px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 16px rgba(239, 68, 68, 0.4);">
                <span>🏁</span> 5. Check Out Presensi
            </button>
            @elseif($attendanceToday && $attendanceToday->clock_out_at)
            <button type="button" disabled class="btn" style="padding: 16px; font-size: 13px; background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                <span>✅</span> Sudah Check Out ({{ $attendanceToday->clock_out_at->format('H:i') }})
            </button>
            @else
            <a href="{{ route('presensi2.scan') }}" class="btn btn-emerald" style="padding: 16px; font-size: 14px; font-weight: 700;">
                <span>🔐</span> Check-In Presensi
            </a>
            @endif

            {{-- TOMBOL KE HALAMAN REKAP --}}
            <a href="{{ route('presensi2.rekap') }}" class="btn" style="padding: 14px; font-size: 13.5px; font-weight: 700; background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.4); box-shadow: 0 4px 16px rgba(14, 165, 233, 0.2); grid-column: 1 / -1; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span>📊</span> Buka Halaman Rekapitulasi Lengkap & Riwayat Shift
            </a>
        </div>

        {{-- MODAL REKAP TUTUP SHIFT (HIDDEN) --}}
        <div id="modal-rekap-selesai" style="display: none; margin-top: 20px; text-align: left; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(14, 165, 233, 0.4); border-radius: 16px; padding: 20px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #38bdf8; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                <span>📁 Rekapitulasi Kasir & Selesai Shift</span>
                <button type="button" onclick="toggleForm('modal-rekap-selesai')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 16px;">✖</button>
            </h4>
            <form action="{{ route('presensi2.penjualan.selesai') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 14px;">
                    <div style="background: rgba(0, 0, 0, 0.3); padding: 10px 12px; border-radius: 10px;">
                        <span style="font-size: 11px; color: var(--text-muted);">1. Omset F&B (Net):</span>
                        <div style="font-size: 15px; font-weight: 700; color: #fbbf24;">Rp {{ number_format($totalFnbIncome, 0, ',', '.') }}</div>
                        @if(($totalDiscountAmount ?? 0) > 0)
                            <span style="font-size: 10px; color: #f87171;">Diskon: -Rp {{ number_format($totalDiscountAmount, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    <div style="background: rgba(0, 0, 0, 0.3); padding: 10px 12px; border-radius: 10px;">
                        <span style="font-size: 11px; color: var(--text-muted);">2. Pendapatan Internet:</span>
                        <div style="font-size: 15px; font-weight: 700; color: #22d3ee;">Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}</div>
                    </div>
                    <div style="background: rgba(0, 0, 0, 0.3); padding: 10px 12px; border-radius: 10px;">
                        <span style="font-size: 11px; color: var(--text-muted);">💳 Non-Tunai (QRIS):</span>
                        <div style="font-size: 15px; font-weight: 700; color: #c4b5fd;">Rp {{ number_format($totalNonCashAmount ?? 0, 0, ',', '.') }}</div>
                        <span style="font-size: 10px; color: #94a3b8;">Masuk Rek. QRIS</span>
                    </div>
                </div>
                <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 12px; margin-bottom: 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #c4b5fd; margin-bottom: 8px;">
                        📦 Audit Perbandingan Stok (Sebelumnya vs Sesudahnya):
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; font-size: 11px;">
                        <div style="background: rgba(0,0,0,0.3); padding: 6px 10px; border-radius: 6px;">
                            <span style="color: var(--text-muted); display: block;">Kertas A4 Sisa:</span>
                            <span style="color: #34d399; font-weight: 700;">{{ $stockComparisons['kertas'][0]['sesudahnya'] ?? 0 }} Lembar</span>
                        </div>
                        <div style="background: rgba(0,0,0,0.3); padding: 6px 10px; border-radius: 6px;">
                            <span style="color: var(--text-muted); display: block;">Kertas F4 Sisa:</span>
                            <span style="color: #34d399; font-weight: 700;">{{ $stockComparisons['kertas'][1]['sesudahnya'] ?? 0 }} Lembar</span>
                        </div>
                        <div style="background: rgba(0,0,0,0.3); padding: 6px 10px; border-radius: 6px;">
                            <span style="color: var(--text-muted); display: block;">Makanan Terjual:</span>
                            <span style="color: #fbbf24; font-weight: 700;">{{ $stockComparisons['total_makanan_terjual'] ?? 0 }} pcs</span>
                        </div>
                        <div style="background: rgba(0,0,0,0.3); padding: 6px 10px; border-radius: 6px;">
                            <span style="color: var(--text-muted); display: block;">Minuman Terjual:</span>
                            <span style="color: #fbbf24; font-weight: 700;">{{ $stockComparisons['total_minuman_terjual'] ?? 0 }} pcs</span>
                        </div>
                    </div>
                </div>

                {{-- BLIND CASH COUNT SECTION (SoD & ISO 27001 AUDIT) --}}
                <div style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 10px; padding: 12px; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 12px; font-weight: 700; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                            <span>🛡️</span> Blind Cash Count (Hitung Kas Laci Mandiri)
                        </span>
                        <span class="badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; font-size: 10px;">
                            SoD / Audit ISO 27001
                        </span>
                    </div>
                    <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 10px; line-height: 1.4;">
                        Hitung seluruh uang fisik kas di laci secara mandiri tanpa bocoran nominal sistem. Anda dapat menginput langsung total kas atau memanfaatkan kalkulator rincian pecahan uang di bawah.
                    </p>

                    {{-- Toggle Kalkulator Pecahan Uang --}}
                    <div style="margin-bottom: 10px;">
                        <button type="button" onclick="toggleDenomCalculator()" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border-glass); border-radius: 6px; padding: 6px 12px; color: #38bdf8; font-size: 11.5px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <span>💵</span> <span id="toggle-denom-label">Buka Kalkulator Rincian Pecahan Uang</span>
                        </button>
                    </div>

                    {{-- Grid Pecahan Uang (Default Tersembunyi) --}}
                    <div id="denom-calculator-grid" style="display: none; background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-glass); border-radius: 8px; padding: 10px; margin-bottom: 12px;">
                        <div style="font-size: 11px; font-weight: 700; color: #94a3b8; margin-bottom: 8px;">
                            Masukkan Jumlah Lembar / Keping:
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px; font-size: 11px;">
                            <div>
                                <label style="color: #f87171; display: block; margin-bottom: 2px;">Rp 100.000:</label>
                                <input type="number" id="denom_100k" min="0" value="0" oninput="recalcBlindCash()" style="width: 100%; padding: 6px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-glass); border-radius: 6px; color: #fff; font-size: 12px;">
                            </div>
                            <div>
                                <label style="color: #60a5fa; display: block; margin-bottom: 2px;">Rp 50.000:</label>
                                <input type="number" id="denom_50k" min="0" value="0" oninput="recalcBlindCash()" style="width: 100%; padding: 6px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-glass); border-radius: 6px; color: #fff; font-size: 12px;">
                            </div>
                            <div>
                                <label style="color: #34d399; display: block; margin-bottom: 2px;">Rp 20.000:</label>
                                <input type="number" id="denom_20k" min="0" value="0" oninput="recalcBlindCash()" style="width: 100%; padding: 6px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-glass); border-radius: 6px; color: #fff; font-size: 12px;">
                            </div>
                            <div>
                                <label style="color: #fbbf24; display: block; margin-bottom: 2px;">Rp 10.000:</label>
                                <input type="number" id="denom_10k" min="0" value="0" oninput="recalcBlindCash()" style="width: 100%; padding: 6px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-glass); border-radius: 6px; color: #fff; font-size: 12px;">
                            </div>
                            <div>
                                <label style="color: #e879f9; display: block; margin-bottom: 2px;">Rp 5.000:</label>
                                <input type="number" id="denom_5k" min="0" value="0" oninput="recalcBlindCash()" style="width: 100%; padding: 6px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-glass); border-radius: 6px; color: #fff; font-size: 12px;">
                            </div>
                            <div>
                                <label style="color: #94a3b8; display: block; margin-bottom: 2px;">Rp 2.000:</label>
                                <input type="number" id="denom_2k" min="0" value="0" oninput="recalcBlindCash()" style="width: 100%; padding: 6px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-glass); border-radius: 6px; color: #fff; font-size: 12px;">
                            </div>
                            <div>
                                <label style="color: #cbd5e1; display: block; margin-bottom: 2px;">Rp 1.000:</label>
                                <input type="number" id="denom_1k" min="0" value="0" oninput="recalcBlindCash()" style="width: 100%; padding: 6px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-glass); border-radius: 6px; color: #fff; font-size: 12px;">
                            </div>
                            <div>
                                <label style="color: #e2e8f0; display: block; margin-bottom: 2px;">Total Koin (Rp):</label>
                                <input type="number" id="denom_coins" min="0" value="0" oninput="recalcBlindCash()" style="width: 100%; padding: 6px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border-glass); border-radius: 6px; color: #fff; font-size: 12px;">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #fff; display: block; margin-bottom: 4px;">Total Kas Fisik di Laci (Rp):</label>
                        <input type="number" name="blind_cash_count" id="blind_cash_count" placeholder="Masukkan total uang fisik kas di laci..." required style="width: 100%; padding: 10px; background: rgba(10, 14, 26, 0.85); border: 1px solid #38bdf8; border-radius: 8px; color: #38bdf8; font-size: 15px; font-weight: 800;">
                        <input type="hidden" name="cash_in_drawer" id="cash_in_drawer_hidden" value="0">
                        <input type="hidden" name="cash_denominations" id="cash_denominations" value="">
                    </div>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 4px;">Catatan Serah Terima ke Operator Shift Berikutnya:</label>
                    <textarea name="handover_notes" rows="2" placeholder="Catatan shift (misal: modal kembalian Rp 100.000 utuh, fisik sisa Indomie 15 pcs sesuai)..." style="width: 100%; padding: 10px; background: rgba(10, 14, 26, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 12px;"></textarea>
                </div>
                <button type="submit" class="btn btn-emerald" style="width: 100%; padding: 14px; font-size: 14px; font-weight: 700;">
                    ✅ Simpan Rekapitulasi Selesai Shift
                </button>
            </form>
        </div>

        {{-- RESULT BOX CHECKOUT --}}
        <div id="checkout-result-box" style="display: none; margin-top: 14px; padding: 12px; border-radius: 12px;"></div>
    </div>

    {{-- 1. MODAL TERPADU: DATA & KUNCI BILLING SHIFT --}}
    <div id="modal-billing" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #ffffff; border-radius: 8px; width: 100%; max-width: 580px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; display: flex; flex-direction: column; color: #1e293b;">
            <div style="background: {{ $isBillingLocked ? '#b45309' : '#00a65a' }}; color: #ffffff; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px;">
                    <span>{{ $isBillingLocked ? '🔒' : '💻' }}</span> Data & Kunci Billing Shift
                </div>
                <button type="button" onclick="closeModal('modal-billing')" style="background: none; border: none; color: #ffffff; font-size: 18px; font-weight: bold; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 18px; max-height: 75vh; overflow-y: auto;">
                {{-- RINGKASAN DATA BILLING & PC --}}
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px;">
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 10px; text-align: center;">
                        <span style="font-size: 11px; color: #065f46; display: block;">Total Omset Billing:</span>
                        <strong style="font-size: 15px; color: #059669;">Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}</strong>
                    </div>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 10px; text-align: center;">
                        <span style="font-size: 11px; color: #991b1b; display: block;">PC Sedang Main:</span>
                        <strong style="font-size: 15px; color: #dc2626;">{{ $occupiedPcCount ?? 0 }} Unit</strong>
                    </div>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; text-align: center;">
                        <span style="font-size: 11px; color: #166534; display: block;">PC Tersedia:</span>
                        <strong style="font-size: 15px; color: #15803d;">{{ $availablePcCount ?? 0 }} Unit</strong>
                    </div>
                </div>

                {{-- SECTION KUNCI DATA TUTUP SHIFT BILLING --}}
                <div style="background: {{ $isBillingLocked ? '#fef3c7' : '#f8fafc' }}; border: 1px solid {{ $isBillingLocked ? '#f59e0b' : '#e2e8f0' }}; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="font-weight: 700; font-size: 13px; color: {{ $isBillingLocked ? '#92400e' : '#1e293b' }}; display: flex; align-items: center; gap: 6px;">
                            <span>{{ $isBillingLocked ? '🔒' : '🔓' }}</span> Status Kunci Billing Shift
                        </div>
                        <span style="font-size: 10.5px; padding: 2px 8px; border-radius: 9999px; font-weight: 700; background: {{ $isBillingLocked ? '#dc2626' : '#16a34a' }}; color: #ffffff;">
                            {{ $isBillingLocked ? 'TERKUNCI' : 'BELUM DIKUNCI' }}
                        </span>
                    </div>

                    @if($isBillingLocked)
                        <div style="background: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #fde68a; font-size: 12px; color: #1e293b; margin-bottom: 10px;">
                            <div>Total Billing Dikunci: <strong style="color: #b45309; font-size: 14px;">Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}</strong></div>
                            <div style="font-size: 10.5px; color: #6b7280; margin-top: 2px;">Waktu Kunci: {{ $salesSummary?->locked_billing_at ? \Carbon\Carbon::parse($salesSummary->locked_billing_at)->format('H:i - d/m/Y') : '-' }}</div>
                        </div>
                        <button type="button" onclick="submitToggleLockBilling('unlock')" 
                                style="width: 100%; background: #ffffff; color: #dc2626; border: 1px solid #dc2626; padding: 7px 12px; font-size: 11.5px; font-weight: 700; border-radius: 4px; cursor: pointer;">
                            🔓 Buka Kunci Billing (Izinkan Perubahan)
                        </button>
                    @else
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="number" id="input-lock-billing-amount" value="{{ (int) $totalInternetIncome }}" min="0" style="flex: 1; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; font-weight: 700; color: #0f172a;">
                            <button type="button" onclick="submitToggleLockBilling('lock')" 
                                    style="background: #f39c12; color: #ffffff; border: none; padding: 8px 14px; font-size: 11.5px; font-weight: 700; border-radius: 4px; cursor: pointer; white-space: nowrap;">
                                🔒 Kunci Billing Shift
                            </button>
                        </div>
                    @endif
                </div>

                <div style="font-weight: 700; font-size: 12px; margin-bottom: 8px; color: #334155;">Status Real-time Unit PC Cabang:</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 8px;">
                    @foreach($pcStations as $pc)
                    @php $isOcc = $pc->status === 'occupied'; @endphp
                    <div style="padding: 8px; border: 1px solid {{ $isOcc ? '#f87171' : '#cbd5e1' }}; border-radius: 6px; background: {{ $isOcc ? '#fff1f2' : '#f8fafc' }}; text-align: center;">
                        <div style="font-weight: 700; font-size: 12px; color: {{ $isOcc ? '#dc2626' : '#1e293b' }};">{{ $pc->station_code }}</div>
                        <div style="font-size: 10px; font-weight: 600; color: {{ $isOcc ? '#dc2626' : '#15803d' }}; margin: 2px 0;">
                            {{ $isOcc ? '🔴 ' . ($pc->current_user_name ?? 'Aktif') : '🟢 Standby' }}
                        </div>
                        <div style="font-size: 10px; color: #64748b; font-family: monospace;">
                            {{ $isOcc ? 'Rp ' . number_format($pc->billing_amount, 0, ',', '.') : 'Rp 0' }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- 2. MODAL TERPADU: DATA & KUNCI PRINT KERTAS --}}
    <div id="modal-print-kertas" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #ffffff; border-radius: 8px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; display: flex; flex-direction: column; color: #1e293b;">
            <div style="background: {{ $isPrintLocked ? '#0891b2' : '#00c0ef' }}; color: #ffffff; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px;">
                    <span>{{ $isPrintLocked ? '🔒' : '🖨️' }}</span> Data & Kunci Print Kertas Shift
                </div>
                <button type="button" onclick="closeModal('modal-print-kertas')" style="background: none; border: none; color: #ffffff; font-size: 18px; font-weight: bold; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 18px; max-height: 75vh; overflow-y: auto;">
                {{-- STATUS PRINTER & KERTAS --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px;">
                        <span style="font-size: 11px; color: #166534; display: block;">Status Printer Kasir:</span>
                        <strong style="font-size: 13px; color: #15803d;">🟢 Siap Digunakan (Online)</strong>
                    </div>
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px;">
                        <span style="font-size: 11px; color: #1e40af; display: block;">Sisa Kertas A4 / F4:</span>
                        <strong style="font-size: 13px; color: #2563eb;">{{ $stockComparisons['kertas'][0]['sesudahnya'] ?? 475 }} A4 / {{ $stockComparisons['kertas'][1]['sesudahnya'] ?? 490 }} F4</strong>
                    </div>
                </div>

                {{-- SECTION KUNCI DATA TUTUP SHIFT PRINT --}}
                <div style="background: {{ $isPrintLocked ? '#ecfeff' : '#f8fafc' }}; border: 1px solid {{ $isPrintLocked ? '#06b6d4' : '#e2e8f0' }}; border-radius: 6px; padding: 12px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="font-weight: 700; font-size: 13px; color: {{ $isPrintLocked ? '#155e75' : '#1e293b' }}; display: flex; align-items: center; gap: 6px;">
                            <span>{{ $isPrintLocked ? '🔒' : '🔓' }}</span> Status Kunci Data Print Kertas Shift
                        </div>
                        <span style="font-size: 10.5px; padding: 2px 8px; border-radius: 9999px; font-weight: 700; background: {{ $isPrintLocked ? '#dc2626' : '#16a34a' }}; color: #ffffff;">
                            {{ $isPrintLocked ? 'TERKUNCI' : 'BELUM DIKUNCI' }}
                        </span>
                    </div>

                    @if($isPrintLocked)
                        <div style="background: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #a5f3fc; font-size: 12px; color: #1e293b; margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <div>Total Lembar: <strong style="color: #0891b2; font-size: 13px;">{{ $totalPrintPages }} lembar</strong></div>
                                <div>Total Nominal: <strong style="color: #0891b2; font-size: 13px;">Rp {{ number_format($totalPrintIncome, 0, ',', '.') }}</strong></div>
                            </div>
                            <div style="font-size: 10.5px; color: #6b7280; margin-top: 3px;">Waktu Kunci: {{ $salesSummary?->locked_print_at ? \Carbon\Carbon::parse($salesSummary->locked_print_at)->format('H:i - d/m/Y') : '-' }}</div>
                        </div>
                        <button type="button" onclick="submitToggleLockPrint('unlock')" 
                                style="width: 100%; background: #ffffff; color: #dc2626; border: 1px solid #dc2626; padding: 7px 12px; font-size: 11.5px; font-weight: 700; border-radius: 4px; cursor: pointer;">
                            🔓 Buka Kunci Data Print (Izinkan Perubahan)
                        </button>
                    @else
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 2px;">Jumlah Lembar Print:</label>
                                <input type="number" id="input-lock-print-pages" value="{{ (int) $totalPrintPages }}" min="0" style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12.5px; font-weight: 700; color: #0f172a;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 2px;">Nominal Print (Rp):</label>
                                <input type="number" id="input-lock-print-amount" value="{{ (int) $totalPrintIncome }}" min="0" style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12.5px; font-weight: 700; color: #0f172a;">
                            </div>
                        </div>
                        <button type="button" onclick="submitToggleLockPrint('lock')" 
                                style="width: 100%; background: #00c0ef; color: #ffffff; border: none; padding: 8px 14px; font-size: 11.5px; font-weight: 700; border-radius: 4px; cursor: pointer;">
                            🔒 Kunci Data Print Shift Ini
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 5. MODAL DAFTAR PRODUK KATEGORI (OPEN) --}}
    <div id="modal-category-items" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #ffffff; border-radius: 8px; width: 100%; max-width: 580px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; display: flex; flex-direction: column; color: #1e293b;">
            <div style="background: #00a65a; color: #ffffff; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px;">
                    <span>📂</span> <span id="cat-modal-title">Daftar Produk Kategori</span>
                </div>
                <button type="button" onclick="closeModal('modal-category-items')" style="background: none; border: none; color: #ffffff; font-size: 18px; font-weight: bold; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <input type="text" id="cat-modal-search" oninput="filterCategoryModalItems()" placeholder="Cari dalam kategori ini..." 
                       style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
            </div>
            <div id="cat-modal-items-list" style="padding: 14px 18px; max-height: 60vh; overflow-y: auto; display: flex; flex-direction: column; gap: 6px;">
                {{-- Dynamic list populated via JS --}}
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL & PEMBATALAN PESANAN INVOICE --}}
    <div id="modal-invoice-detail" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 18px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
            
            {{-- HEADER MODAL --}}
            <div style="padding: 16px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: space-between; background: rgba(30, 41, 59, 0.5);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(59, 130, 246, 0.2); display: flex; align-items: center; justify-content: center; font-size: 18px; color: #60a5fa;">
                        🧾
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">Detail Transaksi Invoice</h3>
                        <div id="modal-inv-num" style="font-family: monospace; font-size: 12px; color: #93c5fd; font-weight: 600; margin-top: 2px;"></div>
                    </div>
                </div>
                <button type="button" onclick="closeInvoiceModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer; padding: 4px 8px; border-radius: 6px; line-height: 1;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">✕</button>
            </div>

            {{-- BODY MODAL --}}
            <div style="padding: 20px; overflow-y: auto; flex: 1;">
                
                {{-- STATUS BANNER --}}
                <div id="modal-status-banner" style="margin-bottom: 16px; padding: 10px 14px; border-radius: 10px; font-size: 12px; display: flex; align-items: center; gap: 8px;"></div>

                {{-- INFORMASI TRANSAKSI --}}
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px;">
                    <div>
                        <span style="color: var(--text-muted); display: block; font-size: 11px;">Waktu Transaksi</span>
                        <strong id="modal-inv-time" style="color: #e2e8f0;">-</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); display: block; font-size: 11px;">Operator / Kasir</span>
                        <strong id="modal-inv-cashier" style="color: #e2e8f0;">-</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); display: block; font-size: 11px;">Nama Pelanggan</span>
                        <strong id="modal-inv-customer" style="color: #e2e8f0;">-</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); display: block; font-size: 11px;">Metode Pembayaran</span>
                        <strong id="modal-inv-payment" style="color: #34d399; text-transform: uppercase;">-</strong>
                    </div>
                </div>

                {{-- TABEL ITEM PESANAN --}}
                <div style="margin-bottom: 16px;">
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Rincian Item Menu</span>
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; background: rgba(15, 23, 42, 0.5); border-radius: 10px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.06);">
                        <thead>
                            <tr style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); text-align: left;">
                                <th style="padding: 8px 10px;">Item</th>
                                <th style="padding: 8px 10px; text-align: center;">Qty</th>
                                <th style="padding: 8px 10px; text-align: right;">Harga</th>
                                <th style="padding: 8px 10px; text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modal-inv-items-body"></tbody>
                    </table>
                </div>

                {{-- TOTAL PEMBAYARAN --}}
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 10px; margin-bottom: 16px;">
                    <span style="font-weight: 600; color: #fde68a; font-size: 13px;">Total Nilai Transaksi</span>
                    <span id="modal-inv-total" style="font-size: 18px; font-weight: 800; color: #fbbf24; font-family: 'Outfit', monospace;">Rp 0</span>
                </div>

                {{-- CATATAN ATAU INFO PEMBATALAN JIKA SUDAH BATAL --}}
                <div id="modal-cancelled-info" style="display: none; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 10px; padding: 12px 14px; font-size: 12px; color: #fca5a5; margin-bottom: 12px;">
                    <strong>⚠️ Status Pembatalan:</strong>
                    <p id="modal-cancelled-notes" style="margin: 4px 0 0 0; font-size: 11px; color: #fca5a5;"></p>
                    <div style="margin-top: 6px; font-size: 11px; color: #94a3b8;">
                        ✓ Stok barang telah dikembalikan secara otomatis ke inventaris.
                    </div>
                    <div style="margin-top: 10px;">
                        <a id="modal-cancelled-admin-link" href="{{ route('admin.penjualan') }}" class="btn" style="display: inline-flex; width: auto; padding: 6px 14px; font-size: 11px; background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4); text-decoration: none; border-radius: 6px;">
                            <span>🏢</span> Buka di Admin ERP
                        </a>
                    </div>
                </div>

                {{-- FORM PEMBATALAN PESANAN (JIKA MASIH AKTIF) --}}
                <div id="modal-cancel-section" style="background: rgba(239, 68, 68, 0.06); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 12px; padding: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <span style="font-size: 16px;">⚠️</span>
                        <strong style="color: #f87171; font-size: 13px;">Menu Pembatalan Pesanan</strong>
                    </div>
                    <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.4;">
                        Jika pesanan ini dibatalkan, sistem akan <strong>mengembalikan stok barang</strong> ke inventaris dan memotong nominal dari total omzet shift.
                    </p>
                    <form id="form-cancel-order" method="POST" action="" onsubmit="return confirmCancelOrder()">
                        @csrf
                        <input type="hidden" name="redirect_to" id="cancel_redirect_to" value="admin">
                        <div style="margin-bottom: 12px;">
                            <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 4px;">Alasan Pembatalan (Audit ISO 27001):</label>
                            <input type="text" name="reason" id="cancel_reason" placeholder="Misal: Salah input menu, pesanan batal beli, dll" style="width: 100%; padding: 8px 10px; background: rgba(10, 14, 26, 0.8); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; color: #fff; font-size: 12px;" required>
                        </div>
                        <div style="margin-bottom: 14px; background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 10px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                <label style="font-size: 11px; font-weight: 700; color: #fca5a5; display: block;">🛡️ PIN Otorisasi Supervisor (Dual-Control):</label>
                                <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; font-size: 9.5px;">Wajib SoD</span>
                            </div>
                            <input type="password" name="supervisor_pin" id="cancel_supervisor_pin" maxlength="6" placeholder="Masukkan 6-digit PIN Supervisor..." style="width: 100%; padding: 8px 10px; background: rgba(10, 14, 26, 0.9); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 6px; color: #fff; font-size: 13px; font-weight: bold; letter-spacing: 0.1em;" required>
                            <span style="font-size: 10.5px; color: var(--text-muted); display: block; margin-top: 4px;">Sesuai kepatuhan Segregation of Duties (SoD), pembatalan nota wajib disetujui langsung oleh Supervisor / Otorisator jaga.</span>
                        </div>
                        <div style="display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap;">
                            <button type="button" onclick="closeInvoiceModal()" class="btn" style="padding: 8px 14px; font-size: 12px; background: rgba(255, 255, 255, 0.08); color: var(--text-muted);">
                                Tutup
                            </button>
                            <button type="submit" onclick="document.getElementById('cancel_redirect_to').value='presensi2'" class="btn" style="padding: 8px 14px; font-size: 12px; font-weight: 600; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                                ✖ Batalkan Saja
                            </button>
                           
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showStockTab(tabId, btn) {
        document.querySelectorAll('.stock-tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.btn-stock-tab').forEach(b => {
            b.style.background = 'rgba(15, 23, 42, 0.6)';
            b.style.borderColor = 'var(--border-glass)';
            b.style.color = 'var(--text-muted)';
        });

        const target = document.getElementById(tabId);
        if (target) {
            target.style.display = 'block';
        }
        btn.style.background = 'rgba(139, 92, 246, 0.25)';
        btn.style.borderColor = 'rgba(139, 92, 246, 0.4)';
        btn.style.color = '#fff';
    }

    function toggleForm(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }
    }

    function autoFillItem(select) {
        const val = select.value;
        const nameInput = document.getElementById('req_item_name');
        const unitInput = document.getElementById('req_unit');
        if (val === 'kertas') {
            nameInput.value = 'Kertas HVS A4 70gsm';
            unitInput.value = 'Rim';
        } else if (val === 'minuman') {
            nameInput.value = 'Le Minerale 600ml Dingin';
            unitInput.value = 'Dus';
        } else if (val === 'makanan') {
            nameInput.value = 'Indomie Goreng Spesial';
            unitInput.value = 'Dus';
        } else {
            nameInput.value = 'Kertas Thermal Struk Kasir';
            unitInput.value = 'Roll';
        }
    }

    // STATE POS KASIR SINKRON
    const fnbCatalog = [
        @foreach($fnbProducts as $fp)
        {
            id: {{ $fp->id }},
            name: @json($fp->name),
            price: {{ (float) $fp->base_price }},
            stock: {{ (int) ($fp->stock_qty ?? 20) }},
            cat: @json($fp->product?->category?->code ?? 'CAT-OTHER'),
            cat_name: @json($fp->product?->category?->name ?? 'Lainnya'),
        },
        @endforeach
    ];

    let posCart = {}; // { variantId: { id, name, price, qty, stock } }
    let currentPaymentMethod = 'cash';
    let currentCategoryModalList = [];
    let selectedBillingPc = 'PC-01';

    function toggleCartBody() {
        const body = document.getElementById('cart-body-content');
        if (body) {
            body.style.display = (body.style.display === 'none') ? 'flex' : 'none';
        }
    }

    function toggleHistoryAccordion() {
        const body = document.getElementById('history-pos-accordion-body');
        const icon = document.getElementById('history-toggle-icon');
        if (body) {
            if (body.style.display === 'none' || !body.style.display) {
                body.style.display = 'block';
                if (icon) icon.innerText = '−';
            } else {
                body.style.display = 'none';
                if (icon) icon.innerText = '+';
            }
        }
    }

    function filterPosMenu() {
        const q = (document.getElementById('pos-search').value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.pos-terlaris-item');
        let visibleCount = 0;
        items.forEach(el => {
            const name = (el.getAttribute('data-name') || '').toLowerCase();
            if (!q || name.includes(q)) {
                el.style.display = 'flex';
                visibleCount++;
            } else {
                el.style.display = 'none';
            }
        });
        const emptyNotice = document.getElementById('pos-search-empty-notice');
        if (emptyNotice) {
            emptyNotice.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }

    function selectPcTarget(val) {
        const input = document.getElementById('pos-customer-input');
        if (input) input.value = val;
        const quickSelect = document.getElementById('pos-pc-quick-select');
        if (quickSelect && quickSelect.value !== val) {
            quickSelect.value = val;
        }
    }

    function togglePosDiscountBox() {
        const box = document.getElementById('pos-discount-collapsible');
        const btn = document.getElementById('btn-toggle-discount');
        if (!box) return;
        const isHidden = box.style.display === 'none' || !box.style.display;
        box.style.display = isHidden ? 'block' : 'none';
        if (btn) {
            btn.innerText = isHidden ? '🏷️ Tutup Diskon' : '🏷️ + Diskon';
        }
        if (isHidden) {
            document.getElementById('pos-discount-percent')?.focus();
        }
    }

    function onPaymentMethodChanged(method) {
        currentPaymentMethod = method;
        const calcSection = document.getElementById('cash-calculator-section');
        if (calcSection) {
            calcSection.style.display = (method === 'cash') ? 'block' : 'none';
            if (method === 'cash') calculateChange();
        }
    }

    function addToCart(variantId) {
        const prod = fnbCatalog.find(p => p.id === variantId);
        if (!prod) return;

        if (!posCart[variantId]) {
            posCart[variantId] = {
                id: prod.id,
                name: prod.name,
                price: prod.price,
                qty: 1,
                stock: prod.stock
            };
        } else {
            posCart[variantId].qty += 1;
        }

        renderCart();
        showMiniPulse(variantId);
    }

    function updateCartQty(variantId, delta) {
        if (!posCart[variantId]) return;

        posCart[variantId].qty += delta;
        if (posCart[variantId].qty <= 0) {
            delete posCart[variantId];
        }

        renderCart();
    }

    function removeFromCart(variantId) {
        if (posCart[variantId]) {
            delete posCart[variantId];
            renderCart();
        }
    }

    function clearCart() {
        posCart = {};
        const discountInput = document.getElementById('pos-discount-percent');
        if (discountInput) discountInput.value = '0';
        const pinInput = document.getElementById('pos-supervisor-discount-pin');
        if (pinInput) pinInput.value = '';
        const discountBox = document.getElementById('pos-discount-collapsible');
        if (discountBox) discountBox.style.display = 'none';
        const btnDisc = document.getElementById('btn-toggle-discount');
        if (btnDisc) btnDisc.innerText = '🏷️ + Diskon';
        renderCart();
    }

    function showMiniPulse(variantId) {
        const row = document.getElementById('terlaris-item-' + variantId);
        if (row) {
            row.style.background = '#dcfce7';
            setTimeout(() => row.style.background = '#ffffff', 200);
        }
    }

    function renderCart() {
        const cartEmpty = document.getElementById('cart-empty-placeholder');
        const cartRows = document.getElementById('pos-cart-rows');
        const cartTbody = document.getElementById('cart-table-tbody');
        const badgeCount = document.getElementById('cart-item-count-badge');
        const totalDisplay = document.getElementById('pos-total-display');
        const btnSubmitText = document.getElementById('btn-submit-pos-text');

        const items = Object.values(posCart);
        let totalQty = 0;
        let grandTotal = 0;

        if (items.length === 0) {
            if (cartEmpty) cartEmpty.style.display = 'flex';
            if (cartRows) cartRows.style.display = 'none';
            if (cartTbody) cartTbody.innerHTML = '';
            if (badgeCount) badgeCount.innerText = '0 item';
            if (totalDisplay) totalDisplay.innerText = 'Rp 0';
            if (btnSubmitText) btnSubmitText.innerText = 'Simpan Order Penjualan';
            const cashInput = document.getElementById('pos-cash-amount');
            if (cashInput) cashInput.value = '';
            const changeText = document.getElementById('pos-change-text');
            if (changeText) changeText.innerText = 'Rp 0';
            return;
        }

        if (cartEmpty) cartEmpty.style.display = 'none';
        if (cartRows) cartRows.style.display = 'block';
        if (cartTbody) cartTbody.innerHTML = '';

        items.forEach(item => {
            const itemTotal = item.price * item.qty;
            totalQty += item.qty;
            grandTotal += itemTotal;

            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            tr.innerHTML = `
                <td style="padding: 8px 10px; font-weight: 600; color: #1e293b;">
                    <div>${item.name}</div>
                    <div style="font-size: 10.5px; color: #64748b;">Rp ${item.price.toLocaleString('id-ID')} / unit</div>
                </td>
                <td style="padding: 8px 6px; text-align: center;">
                    <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 4px; overflow: hidden; background: #fff;">
                        <button type="button" onclick="updateCartQty(${item.id}, -1)" style="border: none; background: #f1f5f9; width: 22px; height: 22px; cursor: pointer; font-weight: bold; color: #475569;">-</button>
                        <span style="min-width: 26px; text-align: center; font-size: 12px; font-weight: 700; color: #0f172a;">${item.qty}</span>
                        <button type="button" onclick="updateCartQty(${item.id}, 1)" style="border: none; background: #f1f5f9; width: 22px; height: 22px; cursor: pointer; font-weight: bold; color: #475569;">+</button>
                    </div>
                </td>
                <td style="padding: 8px 10px; text-align: right; color: #475569; font-family: monospace;">
                    Rp ${item.price.toLocaleString('id-ID')}
                </td>
                <td style="padding: 8px 10px; text-align: right; font-weight: 700; color: #059669; font-family: monospace;">
                    Rp ${itemTotal.toLocaleString('id-ID')}
                </td>
                <td style="padding: 8px 6px; text-align: center;">
                    <button type="button" onclick="removeFromCart(${item.id})" title="Hapus dari keranjang" style="border: none; background: none; color: #ef4444; font-size: 13px; cursor: pointer; font-weight: bold;">✕</button>
                </td>
            `;
            cartTbody.appendChild(tr);
        });

        if (badgeCount) badgeCount.innerText = totalQty + ' item';
        calculateDiscountAndChange();
    }

    const currentEmployeeMaxDiscount = {{ (float) ($currentEmployee->max_discount_approval_percent ?? 0) }};

    function calculateDiscountAndChange() {
        const grossTotal = getCartTotal();
        const discountInput = document.getElementById('pos-discount-percent');
        const discountPercent = Math.max(0, Math.min(100, parseFloat(discountInput ? discountInput.value : 0) || 0));
        const discountAmount = Math.round((discountPercent / 100) * grossTotal);
        const netTotal = Math.max(0, grossTotal - discountAmount);

        const grossDisplay = document.getElementById('pos-gross-display');
        const discountText = document.getElementById('pos-discount-amount-text');
        const netDisplay = document.getElementById('pos-total-display');
        const btnSubmitText = document.getElementById('pos-btn-submit-text');
        const supervisorBox = document.getElementById('pos-supervisor-discount-box');

        if (grossDisplay) {
            grossDisplay.innerText = 'Rp ' + grossTotal.toLocaleString('id-ID');
            grossDisplay.style.display = discountPercent > 0 ? 'inline' : 'none';
        }
        if (discountText) discountText.innerText = '-Rp ' + discountAmount.toLocaleString('id-ID');
        if (netDisplay) netDisplay.innerText = 'Rp ' + netTotal.toLocaleString('id-ID');
        if (btnSubmitText) btnSubmitText.innerText = 'Simpan Order Penjualan (Rp ' + netTotal.toLocaleString('id-ID') + ')';

        if (supervisorBox) {
            if (discountPercent > currentEmployeeMaxDiscount) {
                supervisorBox.style.display = 'block';
            } else {
                supervisorBox.style.display = 'none';
                const pinInput = document.getElementById('pos-supervisor-discount-pin');
                if (pinInput) pinInput.value = '';
            }
        }

        calculateChange();
    }

    function getNetTotal() {
        const grossTotal = getCartTotal();
        const discountInput = document.getElementById('pos-discount-percent');
        const discountPercent = Math.max(0, Math.min(100, parseFloat(discountInput ? discountInput.value : 0) || 0));
        const discountAmount = Math.round((discountPercent / 100) * grossTotal);
        return Math.max(0, grossTotal - discountAmount);
    }

    function setCashPreset(val) {
        const netTotal = getNetTotal();
        let amount = (val === 'exact') ? netTotal : Number(val);
        const cashInput = document.getElementById('pos-cash-amount');
        if (cashInput) {
            cashInput.value = amount;
            calculateChange();
        }
    }

    function getCartTotal() {
        return Object.values(posCart).reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    // ACTION MODALS FUNCTIONS
    function openModalBilling() {
        const modal = document.getElementById('modal-billing');
        if (modal) modal.style.display = 'flex';
    }

    function openModalCekBilling() {
        openModalBilling();
    }

    function openModalPrintKertas() {
        const modal = document.getElementById('modal-print-kertas');
        if (modal) modal.style.display = 'flex';
    }

    function openModalCekPrinting() {
        openModalPrintKertas();
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    }

    async function submitToggleLockBilling(action) {
        let amount = 0;
        if (action === 'lock') {
            const inputEl = document.getElementById('input-lock-billing-amount');
            amount = inputEl ? parseFloat(inputEl.value) || 0 : 0;
            if (!confirm(`Kunci data billing shift dengan nominal Rp ${amount.toLocaleString('id-ID')}? Data akan dibekukan untuk serah terima shift.`)) {
                return;
            }
        } else {
            if (!confirm('Buka kembali kunci data billing shift? Akumulasi billing akan kembali berjalan otomatis.')) {
                return;
            }
        }

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const token = csrfMeta ? csrfMeta.content : '{{ csrf_token() }}';
            const response = await fetch('{{ route("presensi2.penjualan.kunci-billing") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    locked_billing_amount: amount,
                    current_billing_amount: {{ (int)$totalInternetIncome }}
                })
            });

            const data = await response.json();
            if (data.success) {
                showPosToast(data.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(data.message || 'Gagal mengubah status kunci billing.');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        }
    }

    async function submitToggleLockPrint(action) {
        let amount = 0;
        let pages = 0;
        if (action === 'lock') {
            const pagesEl = document.getElementById('input-lock-print-pages');
            const amountEl = document.getElementById('input-lock-print-amount');
            pages = pagesEl ? parseInt(pagesEl.value) || 0 : 0;
            amount = amountEl ? parseFloat(amountEl.value) || 0 : 0;
            if (!confirm(`Kunci data print kertas shift (${pages} lembar, Rp ${amount.toLocaleString('id-ID')})? Data akan dibekukan untuk rekap shift.`)) {
                return;
            }
        } else {
            if (!confirm('Buka kembali kunci data print kertas shift?')) {
                return;
            }
        }

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const token = csrfMeta ? csrfMeta.content : '{{ csrf_token() }}';
            const response = await fetch('{{ route("presensi2.penjualan.kunci-print") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    locked_print_pages: pages,
                    locked_print_amount: amount
                })
            });

            const data = await response.json();
            if (data.success) {
                showPosToast(data.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(data.message || 'Gagal mengubah status kunci data print.');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        }
    }

    function selectBillingModalPc(code, btn) {
        selectedBillingPc = code;
        document.querySelectorAll('.btn-select-modal-pc').forEach(b => {
            b.style.borderColor = '#cbd5e1';
            b.style.background = '#f8fafc';
        });
        btn.style.borderColor = '#f39c12';
        btn.style.background = '#fef3c7';
    }

    function addBillingToCart(variantId, name, price) {
        selectPcTarget(selectedBillingPc);
        addToCart(variantId);
        closeModal('modal-billing');
        showPosToast(`Paket billing ${name} berhasil ditambahkan ke keranjang untuk ${selectedBillingPc}!`);
    }

    function addCustomPrintToCart(variantId) {
        const qtyInput = document.getElementById('print-qty-' + variantId);
        const qty = Math.max(1, parseInt(qtyInput ? qtyInput.value : 1) || 1);
        const prod = fnbCatalog.find(p => p.id === variantId);
        if (!prod) return;

        if (!posCart[variantId]) {
            posCart[variantId] = {
                id: prod.id,
                name: prod.name,
                price: prod.price,
                qty: qty,
                stock: prod.stock
            };
        } else {
            posCart[variantId].qty += qty;
        }

        renderCart();
        closeModal('modal-print-kertas');
        showPosToast(`${qty}x ${prod.name} berhasil ditambahkan ke keranjang!`);
    }

    function openCategoryModal(catKey, catTitle) {
        const modal = document.getElementById('modal-category-items');
        const titleEl = document.getElementById('cat-modal-title');
        const searchInput = document.getElementById('cat-modal-search');
        if (titleEl) titleEl.innerText = catTitle;
        if (searchInput) searchInput.value = '';

        // Filter products based on category key
        currentCategoryModalList = fnbCatalog.filter(p => {
            const name = (p.name || '').toLowerCase();
            const cat = (p.cat || '').toLowerCase();
            if (catKey === 'hardware') {
                return cat.includes('hardware') || name.includes('mouse') || name.includes('headset') || name.includes('kabel') || name.includes('flashdisk') || name.includes('keyboard');
            } else if (catKey === 'softdrink') {
                return cat.includes('drink') || name.includes('aqua') || name.includes('minerale') || name.includes('teh') || name.includes('kopi') || name.includes('es') || name.includes('susu') || name.includes('tekita') || name.includes('ocha');
            } else if (catKey === 'kertas') {
                return cat.includes('paper') || name.includes('kertas') || name.includes('print') || name.includes('hvs') || name.includes('folio') || name.includes('jilid');
            } else if (catKey === 'snack') {
                return cat.includes('snack') || cat.includes('food') || name.includes('snack') || name.includes('chitato') || name.includes('mie') || name.includes('indomie') || name.includes('kacang') || name.includes('roti') || name.includes('beng');
            } else if (catKey === 'internet') {
                return cat.includes('billing') || name.includes('billing') || name.includes('paket') || name.includes('internet') || name.includes('voucher');
            }
            return true;
        });

        renderCategoryModalItems(currentCategoryModalList);
        if (modal) modal.style.display = 'flex';
    }

    function filterCategoryModalItems() {
        const q = (document.getElementById('cat-modal-search').value || '').toLowerCase().trim();
        const filtered = currentCategoryModalList.filter(p => !q || p.name.toLowerCase().includes(q));
        renderCategoryModalItems(filtered);
    }

    function renderCategoryModalItems(items) {
        const container = document.getElementById('cat-modal-items-list');
        if (!container) return;
        container.innerHTML = '';

        if (items.length === 0) {
            container.innerHTML = '<div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 13px;">Tidak ada produk dalam kategori ini.</div>';
            return;
        }

        items.forEach(p => {
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.alignItems = 'center';
            row.style.justifyContent = 'space-between';
            row.style.padding = '10px 12px';
            row.style.border = '1px solid #e2e8f0';
            row.style.borderRadius = '6px';
            row.style.background = '#ffffff';
            row.innerHTML = `
                <div>
                    <strong style="font-size: 13px; color: #0f172a; display: block;">${p.name}</strong>
                    <div style="display: flex; gap: 6px; margin-top: 2px;">
                        <span class="badge-cyan-stock">Stok: ${p.stock}</span>
                        <span class="badge-red-price">Rp ${p.price.toLocaleString('id-ID')}</span>
                    </div>
                </div>
                <button type="button" onclick="addToCart(${p.id}); closeModal('modal-category-items'); showPosToast('${p.name} ditambahkan ke keranjang!');"
                        style="background: #00a65a; color: #ffffff; border: none; padding: 6px 14px; font-size: 11px; font-weight: 700; border-radius: 4px; cursor: pointer;">
                    + Tambah
                </button>
            `;
            container.appendChild(row);
        });
    }

    function calculateChange() {
        const netTotal = getNetTotal();
        const cashInput = document.getElementById('pos-cash-amount');
        const changeText = document.getElementById('pos-change-text');
        if (!cashInput || !changeText) return;

        if (currentPaymentMethod !== 'cash') {
            return;
        }

        const cashVal = parseFloat(cashInput.value) || 0;
        if (cashVal <= 0) {
            changeText.innerText = 'Rp 0';
            changeText.style.color = '#34d399';
            return;
        }

        const diff = cashVal - netTotal;
        if (diff >= 0) {
            changeText.innerText = 'Rp ' + diff.toLocaleString('id-ID');
            changeText.style.color = '#34d399';
        } else {
            changeText.innerText = 'Kurang Rp ' + Math.abs(diff).toLocaleString('id-ID');
            changeText.style.color = '#f87171';
        }
    }

    function toggleDenomCalculator() {
        const grid = document.getElementById('denom-calculator-grid');
        const label = document.getElementById('toggle-denom-label');
        if (!grid) return;
        if (grid.style.display === 'none' || grid.style.display === '') {
            grid.style.display = 'block';
            if (label) label.innerText = 'Tutup Kalkulator Pecahan Uang';
        } else {
            grid.style.display = 'none';
            if (label) label.innerText = 'Buka Kalkulator Rincian Pecahan Uang';
        }
    }

    function recalcBlindCash() {
        const map = {
            '100k': 100000,
            '50k': 50000,
            '20k': 20000,
            '10k': 10000,
            '5k': 5000,
            '2k': 2000,
            '1k': 1000,
            'coins': 1
        };
        let total = 0;
        const denoms = {};
        for (const [k, mult] of Object.entries(map)) {
            const input = document.getElementById('denom_' + k);
            const val = parseInt(input ? input.value : 0) || 0;
            denoms[k] = val;
            total += val * mult;
        }

        const blindInput = document.getElementById('blind_cash_count');
        const hiddenCash = document.getElementById('cash_in_drawer_hidden');
        const hiddenDenom = document.getElementById('cash_denominations');

        if (blindInput) blindInput.value = total;
        if (hiddenCash) hiddenCash.value = total;
        if (hiddenDenom) hiddenDenom.value = JSON.stringify(denoms);
    }

    function showPosToast(message, isSuccess = true) {
        const toast = document.getElementById('pos-toast');
        if (!toast) return;

        toast.style.display = 'block';
        toast.style.background = isSuccess ? 'rgba(16, 185, 129, 0.95)' : 'rgba(239, 68, 68, 0.95)';
        toast.style.color = '#fff';
        toast.style.border = isSuccess ? '1px solid #34d399' : '1px solid #f87171';
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600;">
                <span style="font-size: 18px;">${isSuccess ? '✅' : '❌'}</span>
                <div>${message}</div>
            </div>
        `;

        setTimeout(() => {
            toast.style.display = 'none';
        }, 4000);
    }

    async function submitPosOrder() {
        const cartItems = Object.values(posCart);
        if (cartItems.length === 0) {
            showPosToast('Keranjang masih kosong! Silakan pilih minimal 1 menu F&B terlebih dahulu.', false);
            return;
        }

        const grossTotal = getCartTotal();
        const netTotal = getNetTotal();
        const discountInput = document.getElementById('pos-discount-percent');
        const discountPercent = Math.max(0, Math.min(100, parseFloat(discountInput ? discountInput.value : 0) || 0));
        const discountAmount = Math.round((discountPercent / 100) * grossTotal);
        let supervisorPin = '';

        if (discountPercent > currentEmployeeMaxDiscount) {
            supervisorPin = (document.getElementById('pos-supervisor-discount-pin')?.value || '').trim();
            if (!supervisorPin) {
                showPosToast(`Diskon ${discountPercent}% melebihi batas wewenang kasir (${currentEmployeeMaxDiscount}%). Masukkan PIN Supervisor!`, false);
                document.getElementById('pos-supervisor-discount-pin')?.focus();
                return;
            }
        }

        const customerName = document.getElementById('pos-customer-input').value.trim() || 'Pelanggan Umum';
        let cashPaid = netTotal;

        if (currentPaymentMethod === 'cash') {
            const inputVal = parseFloat(document.getElementById('pos-cash-amount').value);
            if (isNaN(inputVal) || inputVal < netTotal) {
                showPosToast('Uang tunai yang diterima kurang dari total tagihan (Rp ' + netTotal.toLocaleString('id-ID') + ')', false);
                document.getElementById('pos-cash-amount').focus();
                return;
            }
            cashPaid = inputVal;
        }

        const btnSubmit = document.getElementById('btn-submit-pos');
        btnSubmit.disabled = true;
        btnSubmit.style.opacity = '0.6';
        btnSubmit.innerHTML = '<span>⏳</span> Menyimpan Transaksi...';

        try {
            const payload = {
                transaction_type: 'fnb',
                cart_items: cartItems.map(item => ({
                    variant_id: item.id,
                    qty: item.qty
                })),
                customer_name: customerName,
                payment_method: currentPaymentMethod,
                amount_paid: cashPaid,
                discount_percent: discountPercent,
                discount_amount: discountAmount,
                supervisor_pin: supervisorPin
            };

            const response = await fetch('{{ route("presensi2.penjualan.transaksi") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                showPosToast(`Transaksi ${data.invoice_number || ''} senilai Rp ${netTotal.toLocaleString('id-ID')} berhasil dicatat!`);

                // 1. Kurangi stok lokal pada visual card
                cartItems.forEach(ci => {
                    const prod = fnbCatalog.find(p => p.id === ci.id);
                    if (prod) {
                        prod.stock = Math.max(0, prod.stock - ci.qty);
                        const stockEl = document.getElementById('stock-text-' + ci.id);
                        if (stockEl) {
                            stockEl.innerText = prod.stock <= 5 ? `⚠️ Sisa ${prod.stock} pcs` : `✓ Stok ${prod.stock} pcs`;
                            stockEl.style.color = prod.stock <= 5 ? '#f87171' : '#34d399';
                        }
                    }
                });

                // 2. Update Header Metrik Sinkron
                const topTotal = document.getElementById('top-stat-total');
                const topFnb = document.getElementById('top-stat-fnb');
                if (topFnb) {
                    const curFnb = parseInt(topFnb.innerText.replace(/[^0-9]/g, '')) || 0;
                    const newFnb = curFnb + grandTotal;
                    topFnb.innerText = 'Rp ' + newFnb.toLocaleString('id-ID');
                }
                if (topTotal) {
                    const curTot = parseInt(topTotal.innerText.replace(/[^0-9]/g, '')) || 0;
                    const newTot = curTot + grandTotal;
                    topTotal.innerText = 'Rp ' + newTot.toLocaleString('id-ID');
                }

                // 3. Tambahkan ke Tabel Riwayat
                const tbody = document.getElementById('fnb-sales-tbody');
                const emptyRow = document.getElementById('fnb-empty-row');
                if (emptyRow) emptyRow.remove();

                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
                const saleObj = data.sale || {
                    id: Date.now(),
                    invoice_number: data.invoice_number || 'INV-POS-BARU',
                    customer_name: customerName,
                    payment_method: currentPaymentMethod,
                    payment_status: 'paid',
                    total_fnb: grandTotal,
                    items: cartItems.map(c => ({ item_name: c.name, qty: c.qty, unit_price: c.price, subtotal: c.price * c.qty })),
                    created_at: now.toISOString()
                };

                const tr = document.createElement('tr');
                tr.id = 'sale-row-' + (saleObj.id || Date.now());
                tr.style.borderBottom = '1px solid #f1f5f9';
                tr.style.background = '#f0fdf4';

                let itemsHtml = '';
                if (saleObj.items) {
                    saleObj.items.forEach(it => {
                        itemsHtml += `<div>${it.qty}x ${it.item_name}</div>`;
                    });
                }

                const saleJsonStr = JSON.stringify(saleObj).replace(/'/g, "&#39;");
                tr.innerHTML = `
                    <td style="padding: 9px 10px; color: #64748b; white-space: nowrap;">${timeStr}</td>
                    <td style="padding: 9px 10px;">
                        <button type="button" 
                            onclick='openInvoiceModal(${saleJsonStr})'
                            title="Klik untuk membuka rincian"
                            style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0284c7; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                            <span>📄</span>
                            <span>${saleObj.invoice_number}</span>
                        </button>
                    </td>
                    <td style="padding: 9px 10px; color: #1e293b;">${itemsHtml}</td>
                    <td style="padding: 9px 10px; color: #475569;">${saleObj.customer_name}</td>
                    <td style="padding: 9px 10px; text-align: right; font-weight: 700; color: #059669;">
                        Rp ${grandTotal.toLocaleString('id-ID')}
                    </td>
                    <td style="padding: 9px 10px; text-align: center;">
                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 10px; text-transform: uppercase; padding: 2px 7px; font-weight: 700;">
                            ${saleObj.payment_method}
                        </span>
                    </td>
                    <td style="padding: 9px 10px; text-align: center;">
                        <button type="button" onclick='openInvoiceModal(${saleJsonStr})' class="btn" style="padding: 3px 8px; font-size: 11px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 4px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            <span>✖</span> Batalkan
                        </button>
                    </td>
                `;
                if (tbody.firstChild) {
                    tbody.insertBefore(tr, tbody.firstChild);
                } else {
                    tbody.appendChild(tr);
                }

                // Update badge total count
                const countBadge = document.getElementById('badge-total-sales-count');
                if (countBadge) {
                    countBadge.innerText = parseInt(countBadge.innerText || 0) + 1;
                }

                // 4. Kosongkan keranjang
                clearCart();
            } else {
                showPosToast(data.message || 'Gagal memproses transaksi.', false);
            }
        } catch (err) {
            console.error(err);
            showPosToast('Terjadi kesalahan jaringan saat menyimpan transaksi.', false);
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.style.opacity = '1';
            btnSubmit.innerHTML = '<span>⚡</span> <span id="btn-submit-pos-text">Proses & Simpan Transaksi</span>';
            renderCart();
        }
    }

    function openInvoiceModal(sale) {
        if (!sale) return;

        document.getElementById('modal-inv-num').innerText = sale.invoice_number || '-';
        
        let createdDate = '-';
        if (sale.created_at) {
            const d = new Date(sale.created_at);
            createdDate = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' +
                          d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }
        document.getElementById('modal-inv-time').innerText = createdDate;
        document.getElementById('modal-inv-cashier').innerText = (sale.employee && sale.employee.name) ? sale.employee.name : 'Kasir Shift';
        document.getElementById('modal-inv-customer').innerText = sale.customer_name || 'Pelanggan Umum';
        document.getElementById('modal-inv-payment').innerText = (sale.payment_method || 'cash').toUpperCase();
        
        const tbody = document.getElementById('modal-inv-items-body');
        tbody.innerHTML = '';
        if (sale.items && sale.items.length > 0) {
            sale.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid rgba(255, 255, 255, 0.04)';
                tr.innerHTML = `
                    <td style="padding: 8px 10px; color: #f1f5f9;">${item.item_name}</td>
                    <td style="padding: 8px 10px; text-align: center; color: #94a3b8;">${item.qty}</td>
                    <td style="padding: 8px 10px; text-align: right; color: #94a3b8;">Rp ${Number(item.unit_price).toLocaleString('id-ID')}</td>
                    <td style="padding: 8px 10px; text-align: right; color: #fff; font-weight: 600;">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 12px; color: var(--text-muted);">Tidak ada rincian item</td></tr>';
        }

        const totalAmount = sale.total_fnb > 0 ? sale.total_fnb : (sale.total_billing || 0);
        document.getElementById('modal-inv-total').innerText = 'Rp ' + Number(totalAmount).toLocaleString('id-ID');

        const isCancelled = sale.payment_status === 'cancelled';
        const banner = document.getElementById('modal-status-banner');
        const cancelledInfo = document.getElementById('modal-cancelled-info');
        const cancelSection = document.getElementById('modal-cancel-section');
        const cancelForm = document.getElementById('form-cancel-order');

        if (isCancelled) {
            banner.style.background = 'rgba(239, 68, 68, 0.15)';
            banner.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            banner.style.color = '#f87171';
            banner.innerHTML = '<span>🔴</span> <strong>Status: PESANAN TELAH DIBATALKAN</strong>';
            
            cancelledInfo.style.display = 'block';
            document.getElementById('modal-cancelled-notes').innerText = sale.cashier_notes || 'Pesanan telah dibatalkan.';
            
            const adminLink = document.getElementById('modal-cancelled-admin-link');
            if (adminLink && sale.invoice_number) {
                adminLink.href = '{{ route("admin.penjualan") }}?q=' + encodeURIComponent(sale.invoice_number);
            }
            
            cancelSection.style.display = 'none';
        } else {
            banner.style.background = 'rgba(16, 185, 129, 0.15)';
            banner.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            banner.style.color = '#34d399';
            banner.innerHTML = '<span>🟢</span> <strong>Status: LUNAS & TERCATAT</strong>';
            
            cancelledInfo.style.display = 'none';
            cancelSection.style.display = 'block';

            // Route pembatalan presensi2 dinamis dengan prefix aplikasi
            cancelForm.action = '{{ route("presensi2.penjualan") }}/' + sale.id + '/batal';
            document.getElementById('cancel_reason').value = '';
        }

        const modal = document.getElementById('modal-invoice-detail');
        if (modal) modal.style.display = 'flex';
    }

    function closeInvoiceModal() {
        const modal = document.getElementById('modal-invoice-detail');
        if (modal) modal.style.display = 'none';
    }

    function confirmCancelOrder() {
        const reason = document.getElementById('cancel_reason').value.trim();
        if (!reason) {
            alert('Mohon isi alasan pembatalan pesanan terlebih dahulu (Audit ISO 27001).');
            document.getElementById('cancel_reason').focus();
            return false;
        }

        const pin = (document.getElementById('cancel_supervisor_pin')?.value || '').trim();
        if (!pin || pin.length < 4) {
            alert('Sesuai aturan Segregation of Duties (SoD), pembatalan nota wajib diotorisasi oleh Supervisor dengan memasukkan PIN Supervisor yang valid.');
            document.getElementById('cancel_supervisor_pin')?.focus();
            return false;
        }

        return confirm('Apakah Anda yakin ingin MEMBATALKAN pesanan ini dengan otorisasi Supervisor?\n\nStok barang yang ada di dalam pesanan ini akan otomatis dikembalikan ke inventaris.');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeInvoiceModal();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modal-invoice-detail');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeInvoiceModal();
            });
        }
    });

    async function submitAttendanceCheckOut() {
        if (!confirm('Apakah pergantian shift telah tiba dan Anda yakin ingin melakukan Check Out Presensi sekarang?')) {
            return;
        }

        const btn = document.getElementById('btn-clock-out-penjualan');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Memproses Check-Out...';
        }

        try {
            const response = await fetch('{{ route("presensi2.clock-out") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            });

            const data = await response.json();
            const resultBox = document.getElementById('checkout-result-box');

            if (data.success) {
                resultBox.style.display = 'block';
                resultBox.style.background = 'rgba(16, 185, 129, 0.15)';
                resultBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
                resultBox.style.color = '#34d399';
                resultBox.innerHTML = `✅ ${data.message} Terima kasih atas kerja keras Anda!`;
                setTimeout(() => location.reload(), 2000);
            } else {
                resultBox.style.display = 'block';
                resultBox.style.background = 'rgba(239, 68, 68, 0.15)';
                resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                resultBox.style.color = '#fca5a5';
                resultBox.innerHTML = `❌ ${data.message || 'Terjadi kesalahan saat check-out.'}`;
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<span>🏁</span> 5. Check Out Presensi';
                }
            }
        } catch (err) {
            alert('Gagal terhubung ke server. Periksa koneksi internet Anda.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span>🏁</span> 5. Check Out Presensi';
            }
        }
    }
</script>
@endpush
@endsection
