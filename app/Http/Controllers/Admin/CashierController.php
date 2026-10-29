<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CashierController extends Controller
{
    public function index()
    {
        $cashiers = User::where('role', 'cashier')->orderBy('name', 'asc')->get();
        return view('admin.cashiers.index', compact('cashiers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'cashier',
        ]);

        return redirect()->route('admin.cashiers')->with('success', 'Akun kasir berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.cashiers')->with('success', 'Akun kasir berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'cashier') {
            return redirect()->route('admin.cashiers')->with('error', 'Hanya akun kasir yang dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.cashiers')->with('success', 'Akun kasir berhasil dihapus.');
    }
}
