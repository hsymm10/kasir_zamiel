<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function currentShift()
    {
        $shift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$shift) {
            return response()->json(['active' => false]);
        }

        $cashSales = Transaction::where('user_id', auth()->id())
            ->where('payment_method', 'Tunai')
            ->where('created_at', '>=', $shift->start_time)
            ->sum('total_price');

        $expectedEnding = $shift->starting_cash + $cashSales;

        return response()->json([
            'active'               => true,
            'shift'                => $shift,
            'cash_sales'           => $cashSales,
            'expected_ending_cash' => $expectedEnding,
        ]);
    }

    public function openShift(Request $request)
    {
        $request->validate([
            'starting_cash' => 'required|numeric|min:0',
        ]);

        $activeShift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if ($activeShift) {
            return response()->json(['error' => 'Anda sudah memiliki shift yang aktif.'], 422);
        }

        $shift = CashierShift::create([
            'user_id'       => auth()->id(),
            'start_time'    => Carbon::now(),
            'starting_cash' => $request->starting_cash,
            'status'        => 'open',
        ]);

        return response()->json(['success' => true, 'shift' => $shift]);
    }

    public function closeShift(Request $request)
    {
        $request->validate([
            'actual_ending_cash' => 'required|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $shift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (!$shift) {
            return response()->json(['error' => 'Tidak ada shift aktif yang ditemukan.'], 404);
        }

        $cashSales = Transaction::where('user_id', auth()->id())
            ->where('payment_method', 'Tunai')
            ->where('created_at', '>=', $shift->start_time)
            ->sum('total_price');

        $expectedEnding = $shift->starting_cash + $cashSales;
        $actualEnding = (float) $request->actual_ending_cash;
        $difference = $actualEnding - $expectedEnding;

        $shift->update([
            'end_time'             => Carbon::now(),
            'expected_ending_cash' => $expectedEnding,
            'actual_ending_cash'   => $actualEnding,
            'difference'           => $difference,
            'status'               => 'closed',
            'notes'                => $request->notes,
        ]);

        return response()->json([
            'success'        => true,
            'expected'       => $expectedEnding,
            'actual'         => $actualEnding,
            'difference'     => $difference,
        ]);
    }
}
