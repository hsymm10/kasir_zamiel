@extends('layouts.admin')

@section('title', 'Produk Barang')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-box-open" style="color: var(--bancha);"></i> Kelola Produk</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Manajemen stok, barcode, foto produk, harga beli/jual, dan tanggal kadaluarsa.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createProductModal').showModal()">
        <i class="fa-solid fa-plus"></i> Tambah Produk
    </button>
</div>

<!-- Products Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:48px; text-align:center;">No</th>
                    <th style="width: 60px;">Foto</th>
                    <th>Nama Produk</th>
                    @if(($settings['enable_barcode'] ?? '1') == '1')
                    <th>Barcode</th>
                    @endif
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Diskon (%)</th>
                    <th>Stok</th>
                    @if(($settings['enable_expiration'] ?? '1') == '1')
                    <th>Kadaluarsa</th>
                    @endif
                    <th style="width: 180px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $rowStyle = '';
                        $expiryDateHtml = '<span style="color: var(--text-muted); font-size: 0.85rem;">-</span>';
                        if ($product->expiration_date && ($settings['enable_expiration'] ?? '1') == '1') {
                            $daysLeft = (int) \Carbon\Carbon::now()->startOfDay()->diffInDays($product->expiration_date->startOfDay(), false);
                            if ($daysLeft <= 0) {
                                // Kadaluarsa (hari ini atau sudah lewat)
                                $rowStyle = 'style="background-color: #fef2f2;"';
                                $expiryDateHtml = '<span style="font-family: monospace; font-size: 0.85rem; color: #dc2626; font-weight: 800;" title="Kadaluarsa">' . $product->expiration_date->format('d M Y') . ' <i class="fa-solid fa-triangle-exclamation"></i></span>';
                            } elseif ($daysLeft <= 7) {
                                // Hampir kadaluarsa (1 - 7 hari lagi)
                                $rowStyle = 'style="background-color: #fffbeb;"';
                                $expiryDateHtml = '<span style="font-family: monospace; font-size: 0.85rem; color: #d97706; font-weight: 700;" title="Hampir Kadaluarsa (' . $daysLeft . ' hari lagi)">' . $product->expiration_date->format('d M Y') . '</span>';
                            } else {
                                // Masih lama / Aman (lebih dari 7 hari)
                                $expiryDateHtml = '<span style="font-family: monospace; font-size: 0.85rem; color: var(--olivenite); font-weight: 600;">' . $product->expiration_date->format('d M Y') . '</span>';
                            }
                        }
                    @endphp
                    <tr {!! $rowStyle !!}>
                        <td style="text-align:center; font-weight:700; color:var(--text-muted); font-size:0.85rem;">
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            <div class="product-img-wrapper" style="width: 45px; height: 45px; margin: 0; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                                @if($product->image)
                                    <img src="{{ asset($product->image) }}" class="product-img" alt="{{ $product->name }}">
                                @else
                                    <i class="fa-solid fa-image product-no-img" style="font-size: 1.2rem;"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--olivenite);">{{ $product->name }}</div>
                        </td>
                        @if(($settings['enable_barcode'] ?? '1') == '1')
                        <td style="font-family: monospace; font-size: 0.85rem;">
                            {{ $product->barcode ?: '-' }}
                        </td>
                        @endif
                        <td>
                            <span class="badge badge-info" style="font-size: 0.7rem;">{{ $product->category->name }}</span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">Rp {{ number_format($product->price_buy, 0, ',', '.') }}</td>
                        <td style="font-weight: 700; color: var(--terracotta);">Rp {{ number_format($product->price_sell, 0, ',', '.') }}</td>
                        <td>
                            @if(($product->discount_persen ?? 0) > 0)
                                <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.75rem;">
                                    <i class="fa-solid fa-tag"></i> {{ number_format($product->discount_persen, 0) }}%
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($product->stock <= 5)
                                <span style="color: var(--danger); font-weight: 700;">{{ $product->stock }} (Kritis)</span>
                            @else
                                <strong>{{ $product->stock }}</strong> pcs
                            @endif
                        </td>
                        <td>
                            {!! $expiryDateHtml !!}
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button class="btn btn-secondary btn-sm" onclick="document.getElementById('editProductModal-{{ $product->id }}').showModal()">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="confirmDeleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Product Modal Dialog -->
                    <dialog id="editProductModal-{{ $product->id }}" closedby="any" style="width: 500px;">
                        <div class="modal-header">
                            <h3>Edit Produk</h3>
                            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('editProductModal-{{ $product->id }}').close()" style="border-radius: var(--radius-full); width: 32px; height: 32px; padding: 0;">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="edit_category_id-{{ $product->id }}" class="form-label">Kategori</label>
                                <select name="category_id" id="edit_category_id-{{ $product->id }}" class="form-control" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_name-{{ $product->id }}" class="form-label">Nama Produk</label>
                                <input type="text" name="name" id="edit_name-{{ $product->id }}" class="form-control" value="{{ $product->name }}" required oninput="checkProductEditDuplicate({{ $product->id }})">
                                <div id="edit_name_error-{{ $product->id }}" style="color: var(--terracotta); font-size: 0.8rem; margin-top: 0.25rem; display: none;">
                                    <i class="fa-solid fa-circle-exclamation"></i> Nama produk sudah ada!
                                </div>
                            </div>
                            @if(($settings['enable_barcode'] ?? '1') == '1')
                            <div class="form-group">
                                <label for="edit_barcode-{{ $product->id }}" class="form-label">Barcode (Opsional)</label>
                                <input type="text" name="barcode" id="edit_barcode-{{ $product->id }}" class="form-control" value="{{ $product->barcode }}" oninput="checkProductEditDuplicate({{ $product->id }})">
                                <div id="edit_barcode_error-{{ $product->id }}" style="color: var(--terracotta); font-size: 0.8rem; margin-top: 0.25rem; display: none;">
                                    <i class="fa-solid fa-circle-exclamation"></i> Barcode sudah digunakan oleh produk lain!
                                </div>
                            </div>
                            @endif
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label for="edit_price_buy-{{ $product->id }}" class="form-label">Harga Beli</label>
                                    <input type="text" name="price_buy" id="edit_price_buy-{{ $product->id }}" class="form-control" value="{{ number_format($product->price_buy, 0, ',', '.') }}" required oninput="formatNumberInput(this); calculateEditProfit({{ $product->id }});">
                                </div>
                                <div class="form-group">
                                    <label for="edit_price_sell-{{ $product->id }}" class="form-label">Harga Jual</label>
                                    <input type="text" name="price_sell" id="edit_price_sell-{{ $product->id }}" class="form-control" value="{{ number_format($product->price_sell, 0, ',', '.') }}" required oninput="formatNumberInput(this); calculateEditProfit({{ $product->id }});">
                                </div>
                                <div class="form-group">
                                    <label for="edit_discount_persen-{{ $product->id }}" class="form-label">Diskon (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="discount_persen" id="edit_discount_persen-{{ $product->id }}" class="form-control" value="{{ $product->discount_persen ?? 0 }}" placeholder="0" oninput="calculateEditProfit({{ $product->id }});">
                                </div>
                                <div class="form-group">
                                    <label for="edit_profit-{{ $product->id }}" class="form-label">Keuntungan</label>
                                    @php
                                        $disc = (float)($product->discount_persen ?? 0);
                                        $sell = (float)$product->price_sell;
                                        $buy = (float)$product->price_buy;
                                        $effectiveSell = $sell - ($sell * ($disc / 100));
                                        $initialProfit = $effectiveSell - $buy;
                                    @endphp
                                    <input type="text" id="edit_profit-{{ $product->id }}" class="form-control" style="background-color: var(--bg-cream); font-weight: bold; color: {{ $initialProfit >= 0 ? 'var(--success)' : 'var(--terracotta)' }};" readonly value="{{ $initialProfit >= 0 ? number_format($initialProfit, 0, ',', '.') : '-' . number_format(abs($initialProfit), 0, ',', '.') }}">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="edit_stock-{{ $product->id }}" class="form-label">Stok</label>
                                    <input type="number" name="stock" id="edit_stock-{{ $product->id }}" class="form-control" value="{{ $product->stock }}" required>
                                </div>
                                @if(($settings['enable_expiration'] ?? '1') == '1')
                                <div class="form-group">
                                    <label for="edit_expiration_date-{{ $product->id }}" class="form-label">Tgl Kadaluarsa (Opsional)</label>
                                    <input type="date" name="expiration_date" id="edit_expiration_date-{{ $product->id }}" class="form-control" value="{{ $product->expiration_date ? $product->expiration_date->format('Y-m-d') : '' }}">
                                </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="edit_image-{{ $product->id }}" class="form-label">Foto Produk (Opsional)</label>
                                @if($product->image)
                                <div style="margin-bottom: 0.5rem; display:flex; align-items:center; gap: 0.75rem;">
                                    <img src="{{ asset($product->image) }}" alt="Foto saat ini" style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid var(--border-color);">
                                    <span style="font-size:0.8rem;color:var(--text-muted);">Foto saat ini</span>
                                </div>
                                @endif
                                <input type="file" name="image" id="edit_image-{{ $product->id }}" class="form-control"
                                    accept="image/jpeg,image/jpg,image/png,image/webp,image/gif,image/bmp"
                                    onchange="previewEditImage(this, {{ $product->id }})">
                                <div id="edit_img_preview-{{ $product->id }}" style="margin-top:0.5rem; display:none;">
                                    <img id="edit_img_preview_img-{{ $product->id }}" src="" alt="Preview" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:2px solid var(--bancha);">
                                    <span style="font-size:0.75rem;color:var(--success);margin-left:0.5rem;"><i class="fa-solid fa-check-circle"></i> Foto baru siap diupload</span>
                                </div>
                                <small style="color: var(--text-muted); font-size: 0.75rem;">Format: JPG, PNG, WEBP, GIF. Maks 5 MB. Biarkan kosong jika tidak ingin mengubah foto.</small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editProductModal-{{ $product->id }}').close()">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </dialog>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                            Belum ada produk. Silakan tambahkan produk baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Product Modal Dialog -->
<dialog id="createProductModal" closedby="any" style="width: 500px;">
    <div class="modal-header">
        <h3>Tambah Produk Baru</h3>
        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('createProductModal').close()" style="border-radius: var(--radius-full); width: 32px; height: 32px; padding: 0;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="create_category_id" class="form-label">Kategori</label>
            <select name="category_id" id="create_category_id" class="form-control" required>
                <option value="" disabled selected>Pilih Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="create_name" class="form-label">Nama Produk</label>
            <input type="text" name="name" id="create_name" class="form-control" placeholder="Contoh: Indomie Goreng Spesial" required oninput="checkProductCreateDuplicate()">
            <div id="create_name_error" style="color: var(--terracotta); font-size: 0.8rem; margin-top: 0.25rem; display: none;">
                <i class="fa-solid fa-circle-exclamation"></i> Nama produk sudah ada!
            </div>
        </div>
        @if(($settings['enable_barcode'] ?? '1') == '1')
        <div class="form-group">
            <label for="create_barcode" class="form-label">Barcode (Opsional)</label>
            <input type="text" name="barcode" id="create_barcode" class="form-control" placeholder="Masukkan nomor barcode barang" oninput="checkProductCreateDuplicate()">
            <div id="create_barcode_error" style="color: var(--terracotta); font-size: 0.8rem; margin-top: 0.25rem; display: none;">
                <i class="fa-solid fa-circle-exclamation"></i> Barcode sudah digunakan oleh produk lain!
            </div>
        </div>
        @endif
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem;">
            <div class="form-group">
                <label for="create_price_buy" class="form-label">Harga Beli</label>
                <input type="text" name="price_buy" id="create_price_buy" class="form-control" placeholder="2.500" required oninput="formatNumberInput(this); calculateCreateProfit();">
            </div>
            <div class="form-group">
                <label for="create_price_sell" class="form-label">Harga Jual</label>
                <input type="text" name="price_sell" id="create_price_sell" class="form-control" placeholder="3.000" required oninput="formatNumberInput(this); calculateCreateProfit();">
            </div>
            <div class="form-group">
                <label for="create_discount_persen" class="form-label">Diskon (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="discount_persen" id="create_discount_persen" class="form-control" placeholder="0" value="0" oninput="calculateCreateProfit();">
            </div>
            <div class="form-group">
                <label for="create_profit" class="form-label">Keuntungan</label>
                <input type="text" id="create_profit" class="form-control" style="background-color: var(--bg-cream); font-weight: bold; color: var(--success);" readonly value="0">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="create_stock" class="form-label">Stok Awal</label>
                <input type="number" name="stock" id="create_stock" class="form-control" placeholder="Contoh: 100" required>
            </div>
            @if(($settings['enable_expiration'] ?? '1') == '1')
            <div class="form-group">
                <label for="create_expiration_date" class="form-label">Tgl Kadaluarsa (Opsional)</label>
                <input type="date" name="expiration_date" id="create_expiration_date" class="form-control">
            </div>
            @endif
        </div>
        <div class="form-group">
            <label for="create_image" class="form-label">Foto Produk (Opsional)</label>
            <input type="file" name="image" id="create_image" class="form-control"
                accept="image/jpeg,image/jpg,image/png,image/webp,image/gif,image/bmp"
                onchange="previewCreateImage(this)">
            <div id="create_img_preview" style="margin-top: 0.5rem; display: none;">
                <img id="create_img_preview_img" src="" alt="Preview" style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 2px solid var(--bancha);">
                <span style="font-size: 0.75rem; color: var(--success); margin-left: 0.5rem;"><i class="fa-solid fa-check-circle"></i> Foto siap diupload</span>
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Format: JPG, PNG, WEBP, GIF. Maks 5 MB.</small>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('createProductModal').close()">Batal</button>
            <button type="submit" class="btn btn-primary">Tambah Produk</button>
        </div>
    </form>
</dialog>

<!-- Custom Delete Product Confirm Modal -->
<dialog id="deleteProductConfirmModal"
    style="border-radius: 20px; border: none; padding: 0; max-width: 420px; width: 94%; box-shadow: 0 20px 60px rgba(0,0,0,0.25);">
    <div style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.25rem;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.75rem; color: var(--terracotta);">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--olivenite); margin-bottom: 0.5rem;">Hapus Produk?</h3>
            <p id="delete_product_desc" style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;"></p>
        </div>
        <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 10px; padding: 0.875rem; margin-bottom: 1.25rem; font-size: 0.82rem; color: #166534;">
            <i class="fa-solid fa-circle-check"></i>
            <strong>Info:</strong> Data transaksi lama yang menggunakan produk ini <strong>tetap tersimpan</strong> dan tidak akan hilang.
        </div>
        <form id="deleteProductForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" style="flex: 1;"
                    onclick="document.getElementById('deleteProductConfirmModal').close()">
                    <i class="fa-solid fa-xmark"></i> Batal
                </button>
                <button type="submit" class="btn btn-danger" style="flex: 1;">
                    <i class="fa-solid fa-trash"></i> Ya, Hapus!
                </button>
            </div>
        </form>
    </div>
</dialog>
@endsection

@section('scripts')
<script>
    // ── Preview foto edit produk ──
    function previewEditImage(input, productId) {
        const preview    = document.getElementById('edit_img_preview-' + productId);
        const previewImg = document.getElementById('edit_img_preview_img-' + productId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'flex';
                preview.style.alignItems = 'center';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }

    // ── Preview foto tambah produk ──
    function previewCreateImage(input) {
        const preview    = document.getElementById('create_img_preview');
        const previewImg = document.getElementById('create_img_preview_img');
        if (!preview || !previewImg) return;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'flex';
                preview.style.alignItems = 'center';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }

    // Existing products data for validation (normalized in controller to avoid Blade ParseError)
    const existingProducts = @json($productsForValidation);

    function checkProductCreateDuplicate() {
        const nameInput = document.getElementById('create_name');
        const barcodeInput = document.getElementById('create_barcode');
        
        const nameVal = nameInput ? nameInput.value.replace(/\s+/g, ' ').toLowerCase().trim() : '';
        const barcodeVal = barcodeInput ? barcodeInput.value.toLowerCase().trim() : '';
        
        let isNameDuplicate = false;
        let isBarcodeDuplicate = false;
        
        if (nameVal) {
            isNameDuplicate = existingProducts.some(p => p.name === nameVal);
        }
        if (barcodeVal) {
            isBarcodeDuplicate = existingProducts.some(p => p.barcode === barcodeVal);
        }
        
        const nameError = document.getElementById('create_name_error');
        if (nameError) nameError.style.display = isNameDuplicate ? 'block' : 'none';
        
        const barcodeError = document.getElementById('create_barcode_error');
        if (barcodeError) barcodeError.style.display = isBarcodeDuplicate ? 'block' : 'none';
        
        const submitBtn = document.querySelector('#createProductModal button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = (isNameDuplicate || isBarcodeDuplicate);
        }
    }

    function checkProductEditDuplicate(id) {
        const nameInput = document.getElementById('edit_name-' + id);
        const barcodeInput = document.getElementById('edit_barcode-' + id);
        
        const nameVal = nameInput ? nameInput.value.replace(/\s+/g, ' ').toLowerCase().trim() : '';
        const barcodeVal = barcodeInput ? barcodeInput.value.toLowerCase().trim() : '';
        
        let isNameDuplicate = false;
        let isBarcodeDuplicate = false;
        
        if (nameVal) {
            isNameDuplicate = existingProducts.some(p => p.name === nameVal && p.id !== id);
        }
        if (barcodeVal) {
            isBarcodeDuplicate = existingProducts.some(p => p.barcode === barcodeVal && p.id !== id);
        }
        
        const nameError = document.getElementById('edit_name_error-' + id);
        if (nameError) nameError.style.display = isNameDuplicate ? 'block' : 'none';
        
        const barcodeError = document.getElementById('edit_barcode_error-' + id);
        if (barcodeError) barcodeError.style.display = isBarcodeDuplicate ? 'block' : 'none';
        
        const submitBtn = document.querySelector('#editProductModal-' + id + ' button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = (isNameDuplicate || isBarcodeDuplicate);
        }
    }

    function calculateCreateProfit() {
        const buyVal = parseInt(document.getElementById('create_price_buy').value.replace(/\D/g, '')) || 0;
        const sellVal = parseInt(document.getElementById('create_price_sell').value.replace(/\D/g, '')) || 0;
        const discVal = parseFloat(document.getElementById('create_discount_persen').value) || 0;
        
        const effectiveSell = sellVal - (sellVal * (discVal / 100));
        const profit = Math.round(effectiveSell - buyVal);
        
        const el = document.getElementById('create_profit');
        el.value = profit >= 0 ? profit.toLocaleString('id-ID') : '-' + Math.abs(profit).toLocaleString('id-ID');
        el.style.color = profit >= 0 ? 'var(--success)' : 'var(--terracotta)';
    }

    function calculateEditProfit(id) {
        const buyVal = parseInt(document.getElementById('edit_price_buy-' + id).value.replace(/\D/g, '')) || 0;
        const sellVal = parseInt(document.getElementById('edit_price_sell-' + id).value.replace(/\D/g, '')) || 0;
        const discInput = document.getElementById('edit_discount_persen-' + id);
        const discVal = discInput ? (parseFloat(discInput.value) || 0) : 0;
        
        const effectiveSell = sellVal - (sellVal * (discVal / 100));
        const profit = Math.round(effectiveSell - buyVal);
        
        const el = document.getElementById('edit_profit-' + id);
        if (el) {
            el.value = profit >= 0 ? profit.toLocaleString('id-ID') : '-' + Math.abs(profit).toLocaleString('id-ID');
            el.style.color = profit >= 0 ? 'var(--success)' : 'var(--terracotta)';
        }
    }

    // Format input as thousands with dot separator
    function formatNumberInput(input) {
        let cursorPosition = input.selectionStart;
        let originalLength = input.value.length;

        let value = input.value.replace(/\D/g, '');
        if (value === '') {
            input.value = '';
            return;
        }

        let formatted = parseInt(value, 10).toLocaleString('id-ID');
        input.value = formatted;

        let newLength = formatted.length;
        cursorPosition = cursorPosition + (newLength - originalLength);
        input.setSelectionRange(cursorPosition, cursorPosition);
    }

    // Also update edit_discount_persen change triggers profit recalculation
    document.querySelectorAll('[id^="edit_discount_persen-"]').forEach(el => {
        el.addEventListener('input', function() {
            const id = this.id.replace('edit_discount_persen-', '');
            calculateEditProfit(parseInt(id));
        });
    });

    function confirmDeleteProduct(id, name) {
        const modal = document.getElementById('deleteProductConfirmModal');
        const form = document.getElementById('deleteProductForm');
        const desc = document.getElementById('delete_product_desc');
        form.action = `/admin/products/delete/${id}`;
        desc.innerHTML = `Anda akan menghapus produk <strong>"${name}"</strong> secara permanen.`;
        modal.showModal();
    }

    // Fallback light-dismiss for browsers that don't support closedby attribute on <dialog>
    if (!('closedBy' in HTMLDialogElement.prototype)) {
        document.querySelectorAll('dialog').forEach(dialog => {
            dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) return;
                const rect = dialog.getBoundingClientRect();
                const isDialogContent = (
                    rect.top <= event.clientY &&
                    event.clientY <= rect.top + rect.height &&
                    rect.left <= event.clientX &&
                    event.clientX <= rect.left + rect.width
                );
                if (!isDialogContent) {
                    dialog.close();
                }
            });
        });
    }
</script>
@endsection
