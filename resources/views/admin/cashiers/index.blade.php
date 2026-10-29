@extends('layouts.admin')

@section('title', 'Kelola Kasir')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-users-gear" style="color: var(--bancha);"></i> Kelola Akun Kasir</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Buat, ubah, dan pantau status aktif kasir secara real-time.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createCashierModal').showModal()">
        <i class="fa-solid fa-user-plus"></i> Tambah Akun Kasir
    </button>
</div>

<!-- Cashiers Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Status Aktivitas</th>
                    <th>Terakhir Aktif</th>
                    <th style="width: 200px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashiers as $index => $cashier)
                    @php
                        $isOnline = $cashier->isOnline();
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="font-weight: 600; color: var(--olivenite);">{{ $cashier->name }}</td>
                        <td style="font-family: monospace; font-size: 0.9rem;">{{ $cashier->username }}</td>
                        <td>
                            @if($isOnline)
                                <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <i class="fa-solid fa-circle" style="font-size: 0.5rem; animation: pulse 1.5s infinite;"></i> Online
                                </span>
                            @else
                                <span class="badge badge-danger">Offline</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.9rem;">
                            @if($cashier->last_seen_at)
                                {{ $cashier->last_seen_at->translatedFormat('d M Y, H:i') }}
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Belum pernah login</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button class="btn btn-secondary btn-sm" onclick="document.getElementById('editCashierModal-{{ $cashier->id }}').showModal()">
                                    <i class="fa-solid fa-user-pen"></i> Edit
                                </button>
                                
                                <form action="{{ route('admin.cashiers.destroy', $cashier->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun kasir ini?')" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Cashier Modal Dialog -->
                    <dialog id="editCashierModal-{{ $cashier->id }}" closedby="any">
                        <div class="modal-header">
                            <h3>Edit Akun Kasir</h3>
                            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('editCashierModal-{{ $cashier->id }}').close()" style="border-radius: var(--radius-full); width: 32px; height: 32px; padding: 0;">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <form action="{{ route('admin.cashiers.update', $cashier->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="edit_name-{{ $cashier->id }}" class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" id="edit_name-{{ $cashier->id }}" class="form-control" value="{{ $cashier->name }}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_username-{{ $cashier->id }}" class="form-label">Username</label>
                                <input type="text" name="username" id="edit_username-{{ $cashier->id }}" class="form-control" value="{{ $cashier->username }}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_password-{{ $cashier->id }}" class="form-label">Password Baru (Opsional)</label>
                                <input type="password" name="password" id="edit_password-{{ $cashier->id }}" class="form-control" placeholder="Kosongkan jika tidak diganti">
                                <small style="color: var(--text-muted); font-size: 0.75rem;">Minimal 6 karakter.</small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editCashierModal-{{ $cashier->id }}').close()">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </dialog>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                            Belum ada akun kasir. Silakan tambahkan kasir baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Cashier Modal Dialog -->
<dialog id="createCashierModal" closedby="any">
    <div class="modal-header">
        <h3>Tambah Akun Kasir Baru</h3>
        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('createCashierModal').close()" style="border-radius: var(--radius-full); width: 32px; height: 32px; padding: 0;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <form action="{{ route('admin.cashiers.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="create_name" class="form-label">Nama Lengkap</label>
            <input type="text" name="name" id="create_name" class="form-control" placeholder="Contoh: Budi Santoso" required>
        </div>
        <div class="form-group">
            <label for="create_username" class="form-label">Username</label>
            <input type="text" name="username" id="create_username" class="form-control" placeholder="Contoh: budis" required>
        </div>
        <div class="form-group">
            <label for="create_password" class="form-label">Password</label>
            <input type="password" name="password" id="create_password" class="form-control" placeholder="Masukkan password kasir" required>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Minimal 6 karakter.</small>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('createCashierModal').close()">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</dialog>

<style>
    @keyframes pulse {
        0% { opacity: 0.4; }
        50% { opacity: 1; }
        100% { opacity: 0.4; }
    }
</style>
@endsection

@section('scripts')
<script>
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
