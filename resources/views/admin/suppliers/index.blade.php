@extends('layouts.admin')

@section('title', 'Manajemen Supplier')

@section('content')
<div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; justify-content: space-between; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-truck-field" style="color: var(--bancha);"></i> Manajemen Supplier</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Kelola daftar pemasok barang dan distributor toko Anda.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openCreateModal()">
        <i class="fa-solid fa-plus"></i> Tambah Supplier
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:50px; text-align:center;">No</th>
                    <th>Nama Supplier</th>
                    <th>Perusahaan / Toko</th>
                    <th>No. Telepon / WA</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th style="width:120px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td style="text-align:center; font-weight:700; color:var(--text-muted); font-size:0.85rem;">
                            {{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $loop->iteration }}
                        </td>
                        <td style="font-weight:700; color:var(--olivenite);">{{ $supplier->name }}</td>
                        <td>{{ $supplier->company ?? '-' }}</td>
                        <td>
                            @if($supplier->phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supplier->phone) }}" target="_blank" style="color: var(--success); font-weight: 600; text-decoration: none;">
                                    <i class="fa-brands fa-whatsapp"></i> {{ $supplier->phone }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $supplier->email ?? '-' }}</td>
                        <td style="font-size:0.85rem; color:var(--text-muted);">{{ $supplier->address ?? '-' }}</td>
                        <td>
                            <div style="display:flex; justify-content:center; gap:0.35rem;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick='openEditModal(@json($supplier))' title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem 1rem;">
                            Belum ada data supplier / pemasok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
    <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border,#e5e7eb);">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>

<!-- Modal Form (Create & Edit) -->
<div id="supplierModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:500px; padding:1.5rem; background:white; border-radius:16px; margin:1rem;">
        <h3 id="modalTitle" style="margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem;">
            <i class="fa-solid fa-truck-field" style="color:var(--bancha);"></i> Tambah Supplier
        </h3>
        <form id="supplierForm" action="{{ route('admin.suppliers.store') }}" method="POST">
            @csrf
            <div id="methodField"></div>
            
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Nama Supplier / Kontak *</label>
                <input type="text" name="name" id="field_name" class="form-control" required placeholder="Contoh: Pak Budi (Distributor Sembako)">
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Nama Perusahaan / PT / Toko</label>
                <input type="text" name="company" id="field_company" class="form-control" placeholder="Contoh: PT Sumber Makmur">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">No. Telepon / WA</label>
                    <input type="text" name="phone" id="field_phone" class="form-control" placeholder="081234567890">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Email</label>
                    <input type="email" name="email" id="field_email" class="form-control" placeholder="supplier@email.com">
                </div>
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Alamat</label>
                <textarea name="address" id="field_address" class="form-control" rows="2" placeholder="Alamat gudang / toko supplier"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Supplier</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openCreateModal() {
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-truck-field" style="color:var(--bancha);"></i> Tambah Supplier';
        document.getElementById('supplierForm').action = "{{ route('admin.suppliers.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('field_name').value = '';
        document.getElementById('field_company').value = '';
        document.getElementById('field_phone').value = '';
        document.getElementById('field_email').value = '';
        document.getElementById('field_address').value = '';
        document.getElementById('supplierModal').style.display = 'flex';
    }

    function openEditModal(supplier) {
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color:var(--bancha);"></i> Edit Supplier';
        document.getElementById('supplierForm').action = "/admin/suppliers/update/" + supplier.id;
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('field_name').value = supplier.name || '';
        document.getElementById('field_company').value = supplier.company || '';
        document.getElementById('field_phone').value = supplier.phone || '';
        document.getElementById('field_email').value = supplier.email || '';
        document.getElementById('field_address').value = supplier.address || '';
        document.getElementById('supplierModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('supplierModal').style.display = 'none';
    }
</script>
@endsection
