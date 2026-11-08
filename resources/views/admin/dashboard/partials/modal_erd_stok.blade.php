<!-- MODAL ERD MANAJEMEN STOK & PERGUDANGAN -->
<div id="erdModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(3, 7, 18, 0.85); backdrop-filter: blur(8px); padding: 20px; overflow-y: auto; justify-content: center; align-items: flex-start;">
    <div style="background: #0f172a; border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 16px; width: 100%; max-width: 1200px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); margin: 30px auto; overflow: hidden; display: flex; flex-direction: column;">
        
        <!-- MODAL HEADER -->
        <div style="padding: 20px 24px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.98)); border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #38bdf8, #6366f1); display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; box-shadow: 0 0 20px rgba(56, 189, 248, 0.35);">
                    📊
                </div>
                <div>
                    <h2 style="font-size: 18px; font-weight: 800; color: #fff; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 8px;">
                        Entity Relationship Diagram (ERD) Manajemen Stok & Pergudangan
                        <span style="font-size: 11px; padding: 3px 8px; border-radius: 20px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; font-weight: 700;">PostgreSQL Schema</span>
                    </h2>
                    <p style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                        Struktur database terpadu: Relasi Cabang, Gudang, Master Varian SKU, Mutasi Fisik, Distribusi & Jurnal Akuntansi SAK
                    </p>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" onclick="closeErdModal()" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #e2e8f0; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.2)'; this.style.borderColor='rgba(239, 68, 68, 0.4)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.borderColor='rgba(255, 255, 255, 0.15)'">
                    ✕ Tutup Diagram
                </button>
            </div>
        </div>

        <!-- NAVIGATION TABS -->
        <div style="padding: 12px 24px; background: rgba(15, 23, 42, 0.7); border-bottom: 1px solid rgba(255, 255, 255, 0.06); display: flex; gap: 8px; flex-wrap: wrap;">
            <button type="button" onclick="switchErdTab('visual')" id="btnErdTabVisual" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; padding: 7px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer;">
                🗺️ Arsitektur Visual & Alur Stok
            </button>
            <button type="button" onclick="switchErdTab('tables')" id="btnErdTabTables" style="background: transparent; border: 1px solid transparent; color: #94a3b8; padding: 7px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer;">
                📑 Kamus Entitas & Kolom Database
            </button>
            <button type="button" onclick="switchErdTab('accounting')" id="btnErdTabAccounting" style="background: transparent; border: 1px solid transparent; color: #94a3b8; padding: 7px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer;">
                ⚖️ Jembatan Akuntansi SAK (Persediaan & HPP)
            </button>
        </div>

        <!-- MODAL BODY -->
        <div style="padding: 24px; max-height: calc(85vh - 160px); overflow-y: auto;">
            
            <!-- TAB 1: ARSITEKTUR VISUAL -->
            <div id="erdTabVisual">
                <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h3 style="font-size: 14px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                        <span>🔄</span> Diagram Alur Sirkulasi Stok Multi-Gudang
                    </h3>
                    
                    <!-- VISUAL GRID -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                        
                        <!-- CARD 1: CABANG & GUDANG -->
                        <div style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 12px; padding: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <span style="font-size: 12px; font-weight: 800; color: #818cf8; text-transform: uppercase;">1. Wilayah & Fasilitas</span>
                                <span style="font-size: 10px; background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 2px 6px; border-radius: 4px;">1 : N Relasi</span>
                            </div>
                            <div style="border-left: 3px solid #6366f1; padding-left: 10px; margin-bottom: 8px;">
                                <div style="font-weight: 700; color: #fff; font-size: 13px;">branches (Cabang)</div>
                                <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">PK: id | code, name, is_hq</div>
                            </div>
                            <div style="text-align: center; color: #6366f1; font-size: 12px; margin: 4px 0;">↓ memiliki</div>
                            <div style="border-left: 3px solid #818cf8; padding-left: 10px;">
                                <div style="font-weight: 700; color: #fff; font-size: 13px;">warehouses (Gudang)</div>
                                <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">PK: id | FK: branch_id | type: storage, in_transit</div>
                            </div>
                        </div>

                        <!-- CARD 2: MASTER KATALOG & SKU -->
                        <div style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <span style="font-size: 12px; font-weight: 800; color: #34d399; text-transform: uppercase;">2. Master SKU & Varian</span>
                                <span style="font-size: 10px; background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 2px 6px; border-radius: 4px;">1 : N Relasi</span>
                            </div>
                            <div style="border-left: 3px solid #10b981; padding-left: 10px; margin-bottom: 8px;">
                                <div style="font-weight: 700; color: #fff; font-size: 13px;">products (Katalog Induk)</div>
                                <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">FK: category_id, brand_id, uom_id</div>
                            </div>
                            <div style="text-align: center; color: #10b981; font-size: 12px; margin: 4px 0;">↓ memiliki varian</div>
                            <div style="border-left: 3px solid #34d399; padding-left: 10px;">
                                <div style="font-weight: 700; color: #fff; font-size: 13px;">product_variants (Varian SKU)</div>
                                <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">sku, barcode, base_price, cost_price (HPP)</div>
                            </div>
                        </div>

                        <!-- CARD 3: INVENTARIS FISIK -->
                        <div style="background: rgba(15, 23, 42, 0.85); border: 2px solid rgba(56, 189, 248, 0.5); border-radius: 12px; padding: 16px; box-shadow: 0 0 15px rgba(56, 189, 248, 0.15);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <span style="font-size: 12px; font-weight: 800; color: #38bdf8; text-transform: uppercase;">3. Inti Saldo Fisik Stok</span>
                                <span style="font-size: 10px; background: rgba(56, 189, 248, 0.25); color: #7dd3fc; padding: 2px 6px; border-radius: 4px; font-weight: 700;">UNIQUE KEY</span>
                            </div>
                            <div style="border-left: 3px solid #38bdf8; padding-left: 10px;">
                                <div style="font-weight: 800; color: #fff; font-size: 14px;">stock_inventories</div>
                                <div style="font-size: 11px; color: #38bdf8; font-family: monospace; margin-top: 4px;">
                                    UK: [warehouse_id, product_variant_id]
                                </div>
                                <div style="font-size: 11.5px; color: #e2e8f0; margin-top: 6px; line-height: 1.4;">
                                    • <strong style="color: #34d399;">qty_on_hand:</strong> Saldo fisik nyata<br>
                                    • <strong style="color: #fbbf24;">safety_stock:</strong> Batas aman peringatan
                                </div>
                            </div>
                        </div>

                        <!-- CARD 4: TRANSAKSI INFLOW / OUTFLOW -->
                        <div style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <span style="font-size: 12px; font-weight: 800; color: #fbbf24; text-transform: uppercase;">4. Alur Mutasi & Penjualan</span>
                                <span style="font-size: 10px; background: rgba(245, 158, 11, 0.2); color: #fde68a; padding: 2px 6px; border-radius: 4px;">Mutasi Dinamis</span>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 6px; padding: 6px 10px; font-size: 11px;">
                                    <strong style="color: #34d399;">[+] Goods Receipts:</strong> Masuk dari Supplier PO
                                </div>
                                <div style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 6px; padding: 6px 10px; font-size: 11px;">
                                    <strong style="color: #a5b4fc;">[⇄] Stock Transfers:</strong> Distribusi antar-gudang
                                </div>
                                <div style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.2); border-radius: 6px; padding: 6px 10px; font-size: 11px;">
                                    <strong style="color: #fb7185;">[-] Cafe Sales / POS:</strong> Terjual ke konsumen kasir
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ALUR MUTASI LOGISTIK CARD -->
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 18px;">
                    <h4 style="font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <span>📋</span> Tiga Siklus Utama Pergerakan Barang
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 14px;">
                        <div style="padding: 12px; background: rgba(30, 41, 59, 0.6); border-radius: 8px; border-left: 3px solid #10b981;">
                            <div style="font-weight: 700; font-size: 12.5px; color: #34d399;">A. Siklus Pengadaan (Inflow)</div>
                            <div style="font-size: 11.5px; color: #cbd5e1; margin-top: 4px; line-height: 1.4;">
                                PO Supplier → Penerimaan Fisik (<code>goods_receipts</code>) → Saldo <code>qty_on_hand</code> bertambah di Gudang Utama → Terbentuk Jurnal Pembelian.
                            </div>
                        </div>
                        <div style="padding: 12px; background: rgba(30, 41, 59, 0.6); border-radius: 8px; border-left: 3px solid #6366f1;">
                            <div style="font-weight: 700; font-size: 12.5px; color: #818cf8;">B. Siklus Distribusi (Transfer)</div>
                            <div style="font-size: 11.5px; color: #cbd5e1; margin-top: 4px; line-height: 1.4;">
                                Gudang Utama (Storage) → Dispatch ke In-Transit → Penerimaan di Pos Kasir Cabang (Storage) → Mengurangi gudang asal & menambah pos kasir.
                            </div>
                        </div>
                        <div style="padding: 12px; background: rgba(30, 41, 59, 0.6); border-radius: 8px; border-left: 3px solid #f43f5e;">
                            <div style="font-weight: 700; font-size: 12.5px; color: #fb7185;">C. Siklus Penjualan Kasir (Outflow)</div>
                            <div style="font-size: 11.5px; color: #cbd5e1; margin-top: 4px; line-height: 1.4;">
                                Order POS Desktop diselesaikan → Validasi stok etalase → Saldo <code>qty_on_hand</code> berkurang → Auto-jurnal mendebit HPP & mengkredit Persediaan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: KAMUS ENTITAS & KOLOM DATABASE -->
            <div id="erdTabTables" style="display: none;">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    
                    <!-- TABEL: stock_inventories -->
                    <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 10px; overflow: hidden;">
                        <div style="background: rgba(56, 189, 248, 0.1); padding: 10px 16px; border-bottom: 1px solid rgba(56, 189, 248, 0.2); display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-weight: 700; color: #38bdf8; font-family: monospace; font-size: 13px;">
                                📦 stock_inventories (Ledger Saldo Stok Fisik)
                            </div>
                            <span style="font-size: 11px; background: rgba(56, 189, 248, 0.2); color: #7dd3fc; padding: 2px 8px; border-radius: 4px;">Tabel Inti Ketersediaan</span>
                        </div>
                        <div style="padding: 12px 16px; overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #e2e8f0;">
                                <thead>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left; color: #94a3b8;">
                                        <th style="padding: 6px;">Nama Kolom</th>
                                        <th style="padding: 6px;">Tipe Data</th>
                                        <th style="padding: 6px;">Constraint</th>
                                        <th style="padding: 6px;">Deskripsi Bisnis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td style="padding: 6px; font-family: monospace; color: #fbbf24;">id</td>
                                        <td style="padding: 6px; color: #94a3b8;">bigint</td>
                                        <td style="padding: 6px;"><span style="color: #fb7185; font-weight: 700;">PRIMARY KEY</span></td>
                                        <td style="padding: 6px;">ID unik pencatatan inventaris</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td style="padding: 6px; font-family: monospace; color: #38bdf8;">warehouse_id</td>
                                        <td style="padding: 6px; color: #94a3b8;">bigint</td>
                                        <td style="padding: 6px;"><span style="color: #38bdf8;">FK -> warehouses.id</span> (CASCADE)</td>
                                        <td style="padding: 6px;">Gudang fisik / etalase pos kasir lokasi barang</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td style="padding: 6px; font-family: monospace; color: #38bdf8;">product_variant_id</td>
                                        <td style="padding: 6px; color: #94a3b8;">bigint</td>
                                        <td style="padding: 6px;"><span style="color: #38bdf8;">FK -> product_variants.id</span> (CASCADE)</td>
                                        <td style="padding: 6px;">Spesifikasi varian SKU produk</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td style="padding: 6px; font-family: monospace; color: #34d399;">qty_on_hand</td>
                                        <td style="padding: 6px; color: #94a3b8;">integer (default: 0)</td>
                                        <td style="padding: 6px;">-</td>
                                        <td style="padding: 6px;">Jumlah fisik barang saat ini (ready to sell)</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px; font-family: monospace; color: #fbbf24;">safety_stock</td>
                                        <td style="padding: 6px; color: #94a3b8;">integer (default: 5)</td>
                                        <td style="padding: 6px;">-</td>
                                        <td style="padding: 6px;">Ambang batas minimum sebelum warning restock</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 8px; font-style: italic;">
                                * Unique Constraint: <code>UNIQUE(warehouse_id, product_variant_id)</code> mencegah duplikasi pencatatan varian di gudang yang sama.
                            </div>
                        </div>
                    </div>

                    <!-- TABEL: stock_transfers & dispatches -->
                    <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 10px; overflow: hidden;">
                        <div style="background: rgba(99, 102, 241, 0.1); padding: 10px 16px; border-bottom: 1px solid rgba(99, 102, 241, 0.2); display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-weight: 700; color: #818cf8; font-family: monospace; font-size: 13px;">
                                🚚 stock_transfer_dispatches / stock_transfers (Mutasi Antar-Gudang)
                            </div>
                            <span style="font-size: 11px; background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 2px 8px; border-radius: 4px;">Logistik Distribusi</span>
                        </div>
                        <div style="padding: 12px 16px; overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #e2e8f0;">
                                <thead>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left; color: #94a3b8;">
                                        <th style="padding: 6px;">Nama Kolom</th>
                                        <th style="padding: 6px;">Tipe Data</th>
                                        <th style="padding: 6px;">Constraint</th>
                                        <th style="padding: 6px;">Deskripsi Bisnis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td style="padding: 6px; font-family: monospace; color: #fbbf24;">transfer_code / number</td>
                                        <td style="padding: 6px; color: #94a3b8;">varchar(50)</td>
                                        <td style="padding: 6px;"><span style="color: #fb7185; font-weight: 700;">UNIQUE</span></td>
                                        <td style="padding: 6px;">Nomor surat jalan resmi transfer distribusi</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td style="padding: 6px; font-family: monospace; color: #38bdf8;">from_warehouse_id</td>
                                        <td style="padding: 6px; color: #94a3b8;">bigint</td>
                                        <td style="padding: 6px;">FK -> warehouses.id</td>
                                        <td style="padding: 6px;">Gudang asal pengirim barang (stok berkurang)</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td style="padding: 6px; font-family: monospace; color: #38bdf8;">to_warehouse_id</td>
                                        <td style="padding: 6px; color: #94a3b8;">bigint</td>
                                        <td style="padding: 6px;">FK -> warehouses.id</td>
                                        <td style="padding: 6px;">Gudang destinasi penerima (stok bertambah)</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px; font-family: monospace; color: #34d399;">status</td>
                                        <td style="padding: 6px; color: #94a3b8;">varchar(30)</td>
                                        <td style="padding: 6px;">in_transit / received / cancelled</td>
                                        <td style="padding: 6px;">Status lifecycle perpindahan barang</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 3: JEMBATAN AKUNTANSI SAK -->
            <div id="erdTabAccounting" style="display: none;">
                <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
                    <h3 style="font-size: 14px; font-weight: 700; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                        <span>⚖️</span> Sinkronisasi Mutasi Fisik ke Buku Besar SAK (Double-Entry)
                    </h3>
                    <p style="font-size: 12.5px; color: #94a3b8; line-height: 1.5; margin-bottom: 16px;">
                        Setiap perubahan nilai fisik pada tabel <code>stock_inventories</code> dihubungkan secara otomatis ke Bagan Akun Standar Akuntansi Keuangan (COA SAK) menggunakan metode Perpetual Inventory:
                    </p>

                    <!-- PERPETUAL TABLE RULES -->
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        
                        <!-- RULE 1: PENJUALAN KASIR POS -->
                        <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong style="color: #38bdf8; font-size: 13px;">1. Saat Penjualan Barang di Kasir POS (Perpetual COGS Entry)</strong>
                                <span style="font-size: 10.5px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 2px 8px; border-radius: 4px;">Auto-Journal POS</span>
                            </div>
                            <div style="font-family: monospace; font-size: 12px; background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 6px; line-height: 1.6;">
                                <span style="color: #34d399;">(DEBIT)  5-10001 - Beban Pokok Penjualan (HPP) F&B</span> : Rp [Qty × Cost Price]<br>
                                <span style="color: #fb7185;">(KREDIT) 1-13001 - Persediaan Barang Dagang & Bahan Baku</span> : Rp [Qty × Cost Price]
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 6px;">
                                * Otomatis mengurangi nominal aset persediaan dan membukukan beban pokok secara *real-time* per transaksi.
                            </div>
                        </div>

                        <!-- RULE 2: VOID / BATAL NOTA -->
                        <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong style="color: #f43f5e; font-size: 13px;">2. Saat Pembatalan Nota (VOID Nota Kasir)</strong>
                                <span style="font-size: 10.5px; background: rgba(244, 63, 94, 0.15); color: #fb7185; padding: 2px 8px; border-radius: 4px;">Reversal Entry</span>
                            </div>
                            <div style="font-family: monospace; font-size: 12px; background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 6px; line-height: 1.6;">
                                <span style="color: #34d399;">(DEBIT)  1-13001 - Persediaan Barang Dagang & Bahan Baku</span> : Rp [Qty × Cost Price]<br>
                                <span style="color: #fb7185;">(KREDIT) 5-10001 - Beban Pokok Penjualan (HPP) F&B</span> : Rp [Qty × Cost Price]
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 6px;">
                                * Memulihkan stok fisik kembali ke etalase kasir serta mengembalikan nilai persediaan di Buku Besar.
                            </div>
                        </div>

                        <!-- RULE 3: FORMULA VALUASI -->
                        <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 10px; padding: 14px;">
                            <div style="font-weight: 700; color: #34d399; font-size: 12.5px; margin-bottom: 6px;">
                                💡 Rumus Valuasi Saldo Akhir Stok Neraca
                            </div>
                            <div style="font-size: 12px; color: #e2e8f0; line-height: 1.5;">
                                Nilai Aset Lancar Persediaan pada Neraca Keuangan (Akun <code>1-13001</code>) selalu merepresentasikan:
                                <div style="font-family: monospace; background: rgba(0,0,0,0.4); padding: 8px 12px; border-radius: 6px; margin-top: 6px; color: #38bdf8;">
                                    Total Saldo Akun 1-13001 = Σ (stock_inventories.qty_on_hand × product_variants.cost_price)
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- MODAL FOOTER -->
        <div style="padding: 14px 24px; background: rgba(15, 23, 42, 0.95); border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 11.5px; color: #94a3b8;">
                📄 Dokumen Lengkap ERD: <a href="file:///var/www/laravel_apps/bagus/docs/2026-11-08/erd_manajemen_stok_pergudangan.md" target="_blank" style="color: #38bdf8; text-decoration: none; font-weight: 600;">docs/2026-11-08/erd_manajemen_stok_pergudangan.md</a>
            </div>
            <button type="button" onclick="closeErdModal()" style="background: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; padding: 6px 16px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 700;">
                Tutup
            </button>
        </div>

    </div>
</div>

<script>
function openErdModal() {
    const modal = document.getElementById('erdModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeErdModal() {
    const modal = document.getElementById('erdModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function switchErdTab(tab) {
    const visual = document.getElementById('erdTabVisual');
    const tables = document.getElementById('erdTabTables');
    const accounting = document.getElementById('erdTabAccounting');

    const btnVisual = document.getElementById('btnErdTabVisual');
    const btnTables = document.getElementById('btnErdTabTables');
    const btnAccounting = document.getElementById('btnErdTabAccounting');

    // Hide all
    if (visual) visual.style.display = 'none';
    if (tables) tables.style.display = 'none';
    if (accounting) accounting.style.display = 'none';

    // Reset buttons
    [btnVisual, btnTables, btnAccounting].forEach(btn => {
        if (btn) {
            btn.style.background = 'transparent';
            btn.style.borderColor = 'transparent';
            btn.style.color = '#94a3b8';
            btn.style.fontWeight = '600';
        }
    });

    if (tab === 'visual' && visual && btnVisual) {
        visual.style.display = 'block';
        btnVisual.style.background = 'rgba(56, 189, 248, 0.15)';
        btnVisual.style.borderColor = 'rgba(56, 189, 248, 0.4)';
        btnVisual.style.color = '#38bdf8';
        btnVisual.style.fontWeight = '700';
    } else if (tab === 'tables' && tables && btnTables) {
        tables.style.display = 'block';
        btnTables.style.background = 'rgba(56, 189, 248, 0.15)';
        btnTables.style.borderColor = 'rgba(56, 189, 248, 0.4)';
        btnTables.style.color = '#38bdf8';
        btnTables.style.fontWeight = '700';
    } else if (tab === 'accounting' && accounting && btnAccounting) {
        accounting.style.display = 'block';
        btnAccounting.style.background = 'rgba(56, 189, 248, 0.15)';
        btnAccounting.style.borderColor = 'rgba(56, 189, 248, 0.4)';
        btnAccounting.style.color = '#38bdf8';
        btnAccounting.style.fontWeight = '700';
    }
}

// Close on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeErdModal();
    }
});
</script>

