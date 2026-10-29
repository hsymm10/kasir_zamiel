<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Refund;
use App\Models\StockLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $invoice = $request->query('invoice');
        $transaction = null;

        if ($invoice) {
            $transaction = Transaction::with(['user', 'items.product'])
                ->where('invoice_number', trim($invoice))
                ->first();
        }

        $myRefunds = Refund::with(['transaction', 'product'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('cashier.refunds.index', compact('transaction', 'myRefunds', 'invoice'));
    }

    public function processRefund(Request $request)
    {
        $request->validate([
            'transaction_id'      => 'required|exists:transactions,id',
            'transaction_item_id' => 'required|exists:transaction_items,id',
            'qty'                 => 'required|integer|min:1',
            'reason'              => 'required|string',
            'is_restocked'        => 'required|boolean',
            'notes'               => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $txItem = TransactionItem::with('transaction')->findOrFail($request->transaction_item_id);

            if ($request->qty > $txItem->qty) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Jumlah retur melebihi jumlah yang dibeli dalam transaksi!');
            }

            $refundPerUnit = $txItem->subtotal / $txItem->qty;
            $refundAmount = $refundPerUnit * $request->qty;
            $productName = $txItem->product ? $txItem->product->name : 'Produk';

            $refund = Refund::create([
                'transaction_id'      => $txItem->transaction_id,
                'transaction_item_id' => $txItem->id,
                'user_id'             => auth()->id(),
                'product_id'          => $txItem->product_id,
                'product_name'        => $productName,
                'qty'                 => $request->qty,
                'refund_amount'       => $refundAmount,
                'reason'              => $request->reason,
                'is_restocked'        => $request->is_restocked,
                'notes'               => $request->notes,
            ]);

            // Restock item if requested
            if ($request->is_restocked && $txItem->product_id) {
                $product = Product::find($txItem->product_id);
                if ($product) {
                    $stockBefore = $product->stock;
                    $stockAfter = $stockBefore + $request->qty;
                    $product->update(['stock' => $stockAfter]);

                    StockLog::create([
                        'product_id'   => $product->id,
                        'user_id'      => auth()->id(),
                        'type'         => 'refund',
                        'qty'          => $request->qty,
                        'stock_before' => $stockBefore,
                        'stock_after'  => $stockAfter,
                        'notes'        => 'Retur Barang (Restok): ' . $request->reason,
                    ]);
                }
            } elseif (!$request->is_restocked && $txItem->product_id) {
                // Damaged log
                $product = Product::find($txItem->product_id);
                if ($product) {
                    StockLog::create([
                        'product_id'   => $product->id,
                        'user_id'      => auth()->id(),
                        'type'         => 'out',
                        'qty'          => $request->qty,
                        'stock_before' => $product->stock,
                        'stock_after'  => $product->stock,
                        'notes'        => 'Retur Barang Rusak (Tidak Restok): ' . $request->reason,
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Retur barang berhasil diproses dan tersimpan di sistem!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses retur: ' . $e->getMessage());
        }
    }
}
