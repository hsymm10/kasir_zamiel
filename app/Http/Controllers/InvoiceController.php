<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;

class InvoiceController extends Controller
{
    public function show($invoice_number)
    {
        $transaction = Transaction::with(['user', 'items.product'])
            ->where('invoice_number', $invoice_number)
            ->firstOrFail();

        return view('invoice.show', compact('transaction'));
    }
}
