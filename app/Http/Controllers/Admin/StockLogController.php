<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use App\Models\Product;
use Illuminate\Http\Request;

class StockLogController extends Controller
{
    public function index(Request $request)
    {
        $query = StockLog::with(['product.category', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $logs = $query->paginate(15);
        $products = Product::orderBy('name', 'asc')->get();

        return view('admin.stock_logs.index', compact('logs', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:in,out,adjust',
            'qty'        => 'required|integer|min:1',
            'notes'      => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);
        $stockBefore = $product->stock;
        $qty = (int) $request->qty;

        if ($request->type === 'in') {
            $stockAfter = $stockBefore + $qty;
        } elseif ($request->type === 'out') {
            $stockAfter = max(0, $stockBefore - $qty);
        } else { // adjust
            $stockAfter = $qty;
            $qty = abs($stockAfter - $stockBefore);
        }

        $product->update(['stock' => $stockAfter]);

        StockLog::create([
            'product_id'   => $product->id,
            'user_id'      => auth()->id(),
            'type'         => $request->type,
            'qty'          => $qty,
            'stock_before' => $stockBefore,
            'stock_after'  => $stockAfter,
            'notes'        => $request->notes ?? 'Penyesuaian stok manual oleh Admin',
        ]);

        return redirect()->back()->with('success', "Stok {$product->name} berhasil diperbarui!");
    }
}
