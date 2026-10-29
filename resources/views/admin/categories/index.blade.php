@extends('layouts.admin')

@section('title', 'Kategori Barang')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-tags" style="color: var(--bancha);"></i> Kelola Kategori</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Tambahkan atau ubah kategori untuk menyusun struktur barang dagangan.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createCategoryModal').showModal()">
        <i class="fa-solid fa-plus"></i> Tambah Kategori
    </button>
</div>

<!-- Category Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">No</th>
                    <th>Nama Kategori</th>
                    <th>Slug</th>
                    <th>Jumlah Produk</th>
                    <th style="width: 200px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="font-weight: 600; color: var(--olivenite);">{{ $category->name }}</td>
                        <td style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted);">{{ $category->slug }}</td>
                        <td>
                            <span class="badge badge-info">{{ $category->products_count }} produk</span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button class="btn btn-secondary btn-sm"
                                    onclick="document.getElementById('editCategoryModal-{{ $category->id }}').showModal()">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="confirmDeleteCategory({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->products_count }})">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Category Modal Dialog -->
                    <dialog id="editCategoryModal-{{ $category->id }}" closedby="any">
                        <div class="modal-header">
                            <h3>Edit Kategori</h3>
                            <button class="btn btn-secondary btn-sm"
                                onclick="document.getElementById('editCategoryModal-{{ $category->id }}').close()"
                                style="border-radius: var(--radius-full); width: 32px; height: 32px; padding: 0;">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="edit_name-{{ $category->id }}" class="form-label">Nama Kategori</label>
                                <input type="text" name="name" id="edit_name-{{ $category->id }}"
                                    class="form-control" value="{{ $category->name }}" required
                                    oninput="checkCategoryEditDuplicate(this, {{ $category->id }})">
                                <div id="edit_name_error-{{ $category->id }}"
                                    style="color: var(--terracotta); font-size: 0.8rem; margin-top: 0.25rem; display: none;">
                                    <i class="fa-solid fa-circle-exclamation"></i> Nama kategori sudah ada!
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    onclick="document.getElementById('editCategoryModal-{{ $category->id }}').close()">Batal</button>
                                <button type="submit" class="btn btn-primary"
                                    id="edit_category_submit-{{ $category->id }}">Simpan Perubahan</button>
                            </div>
                        </form>
                    </dialog>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                            Belum ada kategori. Silakan tambahkan kategori baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Category Modal Dialog -->
<dialog id="createCategoryModal" closedby="any">
    <div class="modal-header">
        <h3>Tambah Kategori Baru</h3>
        <button class="btn btn-secondary btn-sm"
            onclick="document.getElementById('createCategoryModal').close()"
            style="border-radius: var(--radius-full); width: 32px; height: 32px; padding: 0;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="create_name" class="form-label">Nama Kategori</label>
            <input type="text" name="name" id="create_name" class="form-control"
                placeholder="Contoh: Makanan Instan" required oninput="checkCategoryCreateDuplicate(this)">
            <div id="create_name_error"
                style="color: var(--terracotta); font-size: 0.8rem; margin-top: 0.25rem; display: none;">
                <i class="fa-solid fa-circle-exclamation"></i> Nama kategori sudah ada!
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                onclick="document.getElementById('createCategoryModal').close()">Batal</button>
            <button type="submit" class="btn btn-primary" id="create_category_submit">Tambah</button>
        </div>
    </form>
</dialog>

<!-- Custom Delete Confirm Modal -->
<dialog id="deleteCategoryConfirmModal"
    style="border-radius: 20px; border: none; padding: 0; max-width: 420px; width: 94%; box-shadow: 0 20px 60px rgba(0,0,0,0.25);">
    <div style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.25rem;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.75rem; color: var(--terracotta);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--olivenite); margin-bottom: 0.5rem;">Hapus Kategori?</h3>
            <p id="delete_cat_desc" style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;"></p>
        </div>
        <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px; padding: 0.875rem; margin-bottom: 1.25rem; font-size: 0.82rem; color: #9a3412;">
            <i class="fa-solid fa-circle-info"></i>
            <strong>Peringatan:</strong> Tindakan ini <strong>tidak dapat dibatalkan</strong>. Semua produk dalam kategori ini akan ikut terhapus. Data transaksi lama tetap tersimpan.
        </div>
        <form id="deleteCategoryForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" style="flex: 1;"
                    onclick="document.getElementById('deleteCategoryConfirmModal').close()">
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
    const existingCategories = @json($categories->map(function($c) {
        return ['id' => $c->id, 'name' => strtolower(trim($c->name))];
    }));

    function checkCategoryCreateDuplicate(input) {
        const value = input.value.toLowerCase().trim();
        const isDuplicate = existingCategories.some(cat => cat.name === value);
        const errorEl = document.getElementById('create_name_error');
        const submitBtn = document.getElementById('create_category_submit');
        errorEl.style.display = isDuplicate ? 'block' : 'none';
        submitBtn.disabled = isDuplicate;
    }

    function checkCategoryEditDuplicate(input, currentId) {
        const value = input.value.toLowerCase().trim();
        const isDuplicate = existingCategories.some(cat => cat.name === value && cat.id !== currentId);
        const errorEl = document.getElementById('edit_name_error-' + currentId);
        const submitBtn = document.getElementById('edit_category_submit-' + currentId);
        errorEl.style.display = isDuplicate ? 'block' : 'none';
        submitBtn.disabled = isDuplicate;
    }

    function confirmDeleteCategory(id, name, productCount) {
        const modal = document.getElementById('deleteCategoryConfirmModal');
        const form = document.getElementById('deleteCategoryForm');
        const desc = document.getElementById('delete_cat_desc');
        form.action = `/admin/categories/delete/${id}`;
        desc.innerHTML = `Anda akan menghapus kategori <strong>"${name}"</strong>${productCount > 0 ? ` beserta <strong>${productCount} produk</strong> di dalamnya` : ''}.`;
        modal.showModal();
    }

    // Light-dismiss fallback for older browsers
    if (!('closedBy' in HTMLDialogElement.prototype)) {
        document.querySelectorAll('dialog').forEach(dialog => {
            dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) return;
                const rect = dialog.getBoundingClientRect();
                const inside = rect.top <= event.clientY && event.clientY <= rect.top + rect.height &&
                               rect.left <= event.clientX && event.clientX <= rect.left + rect.width;
                if (!inside) dialog.close();
            });
        });
    }
</script>
@endsection
