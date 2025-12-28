<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budget;
use App\Models\SavingsGoal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentMonth = now()->format('Y-m');
        
        // Récupérer les données pour le dashboard
        $monthlyExpense = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        
        $monthlyIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        
        $totalBudget = Budget::where('user_id', $user->id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->sum('amount');
        
        $remainingBudget = $totalBudget - $monthlyExpense;
        
        $totalSavings = SavingsGoal::where('user_id', $user->id)->sum('current_amount');
        
        $recentTransactions = Transaction::with('category')
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();
        
        $categories = Category::where('user_id', $user->id)
            ->orWhere('is_default', true)
            ->get();
        
        $budgetAlerts = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('notifications_enabled', true)
            ->get()
            ->map(function ($budget) use ($user) {
                $spent = Transaction::where('user_id', $user->id)
                    ->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$budget->start_date, $budget->end_date])
                    ->sum('amount');
                
                $percentage = ($budget->amount > 0) ? ($spent / $budget->amount) * 100 : 0;
                
                if ($percentage >= $budget->threshold_percentage) {
                    $budget->percentage = $percentage;
                    $budget->spent = $spent;
                    return $budget;
                }
                return null;
            })
            ->filter();
        
        return view('dashboard', compact(
            'monthlyExpense',
            'monthlyIncome',
            'remainingBudget',
            'totalSavings',
            'recentTransactions',
            'categories',
            'budgetAlerts'
        ));
    }
    
    public function stats(Request $request)
    {
        $user = auth()->user();
        
        $data = [
            'monthly_expense' => Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereMonth('date', now()->month)
                ->sum('amount'),
            'monthly_income' => Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereMonth('date', now()->month)
                ->sum('amount'),
            'transactions_count' => Transaction::where('user_id', $user->id)
                ->whereMonth('date', now()->month)
                ->count(),
            'budget_usage' => 65, // À calculer
        ];
        
        return response()->json($data);
    }
}