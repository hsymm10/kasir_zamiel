<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::with(['products' => function ($q) {
            $q->where('stock', '>', 0)->orderBy('name');
        }])->get();

        return view('cashier.pos', compact('categories'));
    }

    public function getProducts()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
            'payment_method' => 'required|in:Tunai,QRIS,Transfer',
            'pay_amount'     => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalPrice  = 0;
            $totalProfit = 0;
            $totalDiscount = 0;
            $itemsData   = [];

            $enableStock = \App\Models\Setting::getByKey('enable_stock', '1') == '1';

            foreach ($request->items as $cartItem) {
                $product = Product::lockForUpdate()->findOrFail($cartItem['id']);

                if ($enableStock && $product->stock < $cartItem['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Stok {$product->name} tidak cukup. Sisa stok: {$product->stock}"
                    ], 422);
                }

                $priceSell = (float) $product->price_sell;
                $priceBuy  = (float) $product->price_buy;
                $qty       = $cartItem['qty'];

                // Auto-apply discount dari produk (set oleh admin)
                $discountPersen = (float) ($product->discount_persen ?? 0);
                $discountPerItem = $priceSell * ($discountPersen / 100);
                $effectivePrice  = $priceSell - $discountPerItem;

                $subtotal        = $effectivePrice * $qty;
                $discountAmount  = $discountPerItem * $qty;
                $profitPerItem   = ($effectivePrice - $priceBuy) * $qty;

                $totalPrice    += $subtotal;
                $totalProfit   += $profitPerItem;
                $totalDiscount += $discountAmount;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'price_buy'  => $priceBuy,
                    'price_sell' => $priceSell,
                    'qty'        => $qty,
                    'subtotal'   => $subtotal,
                ];

                if ($enableStock) {
                    $product->decrement('stock', $qty);
                }
            }

            if ($request->pay_amount < $totalPrice) {
                DB::rollBack();
                return response()->json(['error' => 'Uang bayar kurang dari total harga.'], 422);
            }

            // Generate invoice number: KZR-YYYYMMDD-XXXX
            $today         = Carbon::now()->format('Ymd');
            $countToday    = Transaction::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = 'KZR-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $transaction = Transaction::create([
                'user_id'        => Auth::id(),
                'invoice_number' => $invoiceNumber,
                'total_price'    => $totalPrice,
                'discount'       => $totalDiscount,
                'profit'         => max(0, $totalProfit),
                'payment_method' => $request->payment_method,
                'pay_amount'     => $request->pay_amount,
                'change_amount'  => $request->pay_amount - $totalPrice,
            ]);

            foreach ($itemsData as $item) {
                TransactionItem::create(array_merge($item, ['transaction_id' => $transaction->id]));
            }

            DB::commit();

            return response()->json([
                'success'        => true,
                'invoice_number' => $invoiceNumber,
                'invoice_url'    => route('invoice.show', $invoiceNumber),
                'change_amount'  => $transaction->change_amount,
                'total_price'    => $totalPrice,
                'payment_method' => $transaction->payment_method,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Transaksi gagal: ' . $e->getMessage()], 500);
        }
    }
}

