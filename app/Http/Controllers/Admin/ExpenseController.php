<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('user')->orderBy('expense_date', 'desc')->paginate(10);
        $totalMonthlyExpense = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
        $totalOverallExpense = Expense::sum('amount');

        return view('admin.expenses.index', compact('expenses', 'totalMonthlyExpense', 'totalOverallExpense'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:150',
            'category'     => 'required|string|max:100',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        Expense::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        return redirect()->back()->with('success', 'Pengeluaran toko berhasil dicatat!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus!');
    }
}
