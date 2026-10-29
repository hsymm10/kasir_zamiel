<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $query = Refund::with(['transaction', 'user', 'product'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('reason')) {
            if ($request->reason === 'Lainnya') {
                $query->whereNotIn('reason', ['Barang Rusak / Cacat', 'Kadaluarsa', 'Salah Beli (Pelanggan)']);
            } else {
                $query->where('reason', $request->reason);
            }
        }

        $refunds = $query->paginate(10);
        $totalRefundAmount = Refund::sum('refund_amount');
        $totalRefundQty = Refund::sum('qty');

        return view('admin.refunds.index', compact('refunds', 'totalRefundAmount', 'totalRefundQty'));
    }
}
