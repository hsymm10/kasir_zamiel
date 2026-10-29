<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // Daily Metrics
        $dailyIncome = Transaction::whereDate('created_at', $today)->sum('total_price');
        $dailyProfit = Transaction::whereDate('created_at', $today)->sum('profit');

        // Monthly Metrics
        $monthlyIncome = Transaction::where('created_at', '>=', $thisMonth)->sum('total_price');
        $monthlyProfit = Transaction::where('created_at', '>=', $thisMonth)->sum('profit');
        $monthlyExpenses = Expense::where('expense_date', '>=', $thisMonth)->sum('amount');
        $monthlyNetProfit = $monthlyProfit - $monthlyExpenses;

        // Yearly Metrics
        $yearlyIncome = Transaction::where('created_at', '>=', $thisYear)->sum('total_price');
        $yearlyProfit = Transaction::where('created_at', '>=', $thisYear)->sum('profit');

        // Total Expenses Overall
        $totalExpenses = Expense::sum('amount');

        // Top 5 Best-Selling Products
        $topSellingProducts = TransactionItem::select('product_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product.category')
            ->limit(5)
            ->get();

        // Product Expiration Warning (within 7 days or already expired)
        $expirationAlerts = Product::with('category')
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<=', now()->addDays(7))
            ->orderBy('expiration_date', 'asc')
            ->get();

        // Recent Transactions
        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data (Last 7 Days)
        $chartData = [
            'labels' => [],
            'income' => [],
            'profit' => [],
            'expenses' => [],
        ];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $income = Transaction::whereDate('created_at', $date)->sum('total_price');
            $profit = Transaction::whereDate('created_at', $date)->sum('profit');
            $expense = Expense::whereDate('expense_date', $date)->sum('amount');
            
            $chartData['labels'][] = $date->translatedFormat('d M');
            $chartData['income'][] = (float) $income;
            $chartData['profit'][] = (float) $profit;
            $chartData['expenses'][] = (float) $expense;
        }

        if (request()->ajax()) {
            return response()->json([
                'dailyIncome' => (float) $dailyIncome,
                'dailyProfit' => (float) $dailyProfit,
                'monthlyIncome' => (float) $monthlyIncome,
                'monthlyProfit' => (float) $monthlyProfit,
                'monthlyExpenses' => (float) $monthlyExpenses,
                'monthlyNetProfit' => (float) $monthlyNetProfit,
                'chartData' => $chartData,
                'topSellingProducts' => $topSellingProducts->map(function($item) {
                    return [
                        'name' => $item->product ? $item->product->name : 'Produk Dihapus',
                        'category' => $item->product && $item->product->category ? $item->product->category->name : '-',
                        'qty' => $item->total_qty,
                        'revenue' => $item->total_revenue
                    ];
                }),
                'recentTransactions' => $recentTransactions->map(function($tx) {
                    return [
                        'id' => $tx->id,
                        'invoice' => $tx->invoice_number,
                        'show_url' => route('admin.transactions.show', $tx->id),
                        'cashier' => $tx->user->name,
                        'method' => $tx->payment_method,
                        'total' => $tx->total_price,
                        'profit' => $tx->profit,
                        'time' => $tx->created_at->format('d M, H:i') . ' WIB'
                    ];
                })
            ]);
        }

        return view('admin.dashboard', compact(
            'dailyIncome', 'dailyProfit',
            'monthlyIncome', 'monthlyProfit', 'monthlyExpenses', 'monthlyNetProfit',
            'yearlyIncome', 'yearlyProfit', 'totalExpenses',
            'topSellingProducts', 'expirationAlerts', 'recentTransactions', 'chartData'
        ));
    }
}
