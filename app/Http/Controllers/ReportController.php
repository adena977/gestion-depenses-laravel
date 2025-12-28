<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budget;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf; // Correction de l'import PDF

class ReportController extends Controller
{
    /**
     * Afficher la page principale des rapports
     */
    public function index()
    {
        $user = auth()->user();
        $currentYear = now()->year;
        
        // Statistiques rapides pour l'année en cours
        $yearlyStats = [
            'total_expenses' => Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereYear('date', $currentYear)
                ->sum('amount'),
            'total_incomes' => Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereYear('date', $currentYear)
                ->sum('amount'),
            'transaction_count' => Transaction::where('user_id', $user->id)
                ->whereYear('date', $currentYear)
                ->count(),
            'top_categories' => Category::where('user_id', $user->id)
                ->where('type', 'expense')
                ->withSum(['transactions as total_amount' => function($query) use ($user, $currentYear) {
                    $query->where('user_id', $user->id)
                          ->where('type', 'expense')
                          ->whereYear('date', $currentYear);
                }], 'amount')
                ->orderBy('total_amount', 'desc')
                ->take(5)
                ->get()
        ];
        
        return view('reports.index', compact('yearlyStats', 'currentYear'));
    }

    /**
     * Afficher le rapport mensuel
     */
    public function monthly(Request $request)
    {
        $user = auth()->user();
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        
        // Validation des paramètres
        if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
            $year = date('Y');
            $month = date('m');
        }
        
        // Récupérer les transactions du mois
        $transactions = Transaction::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('category')
            ->orderBy('date', 'desc')
            ->paginate(20);
        
        // Calculer les statistiques du mois
        $stats = $this->calculateMonthlyStats($user->id, $year, $month);
        
        return view('reports.monthly', compact('transactions', 'stats', 'year', 'month'));
    }

    /**
     * Afficher le rapport annuel
     */
    public function yearly(Request $request)
    {
        $user = auth()->user();
        $year = $request->input('year', date('Y'));
        
        // Validation de l'année
        if (!is_numeric($year) || $year < 2000 || $year > 2100) {
            $year = date('Y');
        }
        
        // Récupérer les données mensuelles pour l'année
        $monthlyData = $this->getYearlyData($user->id, $year);
        
        // Statistiques annuelles
        $yearlyStats = [
            'total_expenses' => Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereYear('date', $year)
                ->sum('amount'),
            'total_incomes' => Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereYear('date', $year)
                ->sum('amount'),
            'balance' => Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereYear('date', $year)
                ->sum('amount') - 
                Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereYear('date', $year)
                ->sum('amount'),
            'transaction_count' => Transaction::where('user_id', $user->id)
                ->whereYear('date', $year)
                ->count()
        ];
        
        return view('reports.yearly', compact('monthlyData', 'yearlyStats', 'year'));
    }

    /**
     * Afficher l'analyse par catégorie
     */
    public function byCategory(Request $request)
    {
        $user = auth()->user();
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Définir les dates par défaut selon la période
        if ($period === 'month') {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        } elseif ($period === 'year') {
            $startDate = now()->startOfYear()->format('Y-m-d');
            $endDate = now()->endOfYear()->format('Y-m-d');
        } elseif (!$startDate || !$endDate) {
            // Par défaut : mois en cours
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
            $period = 'month';
        }
        
        // Récupérer les statistiques par catégorie
        $categoryStats = $this->getCategoryStats($user->id, $startDate, $endDate);
        
        return view('reports.category', compact('categoryStats', 'period', 'startDate', 'endDate'));
    }

    /**
     * Afficher les tendances
     */
    public function trends()
    {
        $user = auth()->user();
        
        // Tendances des 12 derniers mois
        $expenseTrends = $this->getExpenseTrends($user->id);
        $incomeTrends = $this->getIncomeTrends($user->id);
        
        // Moyennes mensuelles
        $monthlyAverages = [
            'expenses' => $this->calculateMonthlyAverage($user->id, 'expense'),
            'incomes' => $this->calculateMonthlyAverage($user->id, 'income')
        ];
        
        return view('reports.trends', compact('expenseTrends', 'incomeTrends', 'monthlyAverages'));
    }

    /**
     * Exporter les données
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $format = $request->input('format', 'html');
        $period = $request->input('period', 'month');
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        
        // Récupérer les données selon la période
        $data = $this->getExportData($user->id, $period, $year, $month);
        
        // Si pas de données
        if ($data->isEmpty()) {
            return back()->with('warning', 'Aucune donnée à exporter pour cette période.');
        }
        
        switch ($format) {
            case 'html':
                return $this->exportAsView($data, $period, $year, $month);
                
            case 'csv':
                return $this->exportAsCsv($data, $period, $year, $month);
                
            case 'pdf':
                // Vérifier si DomPDF est installé
                if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                    return $this->generatePdf($data, $period, $year, $month);
                } else {
                    // Rediriger vers HTML si PDF non disponible
                    return redirect()->route('reports.export', [
                        'format' => 'html',
                        'period' => $period,
                        'year' => $year,
                        'month' => $month
                    ])->with('warning', 'PDF non disponible. Installation requise: composer require barryvdh/laravel-dompdf');
                }
                
            default:
                return back()->with('error', 'Format non supporté. Utilisez HTML, CSV ou PDF.');
        }
    }

    /**
     * ============================================
     * MÉTHODES PRIVÉES POUR LES CALCULS
     * ============================================
     */

    private function calculateMonthlyStats($userId, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Dépenses du mois
        $expenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // Revenus du mois
        $incomes = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // Calcul des statistiques
        $totalExpenses = $expenses->sum('amount') ?? 0;
        $totalIncomes = $incomes->sum('amount') ?? 0;
        $balance = $totalIncomes - $totalExpenses;
        
        // Dépenses par catégorie
        $categoryExpenses = Category::where('user_id', $userId)
            ->where('type', 'expense')
            ->with(['transactions' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function($category) {
                return [
                    'name' => $category->name,
                    'amount' => $category->transactions->sum('amount') ?? 0,
                    'color' => $category->color
                ];
            })
            ->filter(fn($cat) => $cat['amount'] > 0)
            ->sortByDesc('amount')
            ->values();
        
        // Données quotidiennes pour le graphique
        $dailyData = [];
        $currentDate = $startDate->copy();
        $daysCount = 0;
        
        while ($currentDate <= $endDate) {
            $dayExpenses = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereDate('date', $currentDate)
                ->sum('amount') ?? 0;
                
            $dayIncomes = Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereDate('date', $currentDate)
                ->sum('amount') ?? 0;
                
            $dailyData[] = [
                'date' => $currentDate->format('d/m'),
                'expenses' => $dayExpenses,
                'incomes' => $dayIncomes
            ];
            
            $currentDate->addDay();
            $daysCount++;
        }
        
        // Éviter la division par zéro
        $daysCount = max(1, $daysCount);
        $incomeCount = max(1, $incomes->count());
        
        return [
            'total_expenses' => $totalExpenses,
            'total_incomes' => $totalIncomes,
            'balance' => $balance,
            'expense_transactions' => $expenses->count(),
            'income_transactions' => $incomes->count(),
            'transaction_count' => $expenses->count() + $incomes->count(),
            'total_days' => $daysCount,
            'avg_daily_expense' => $totalExpenses / $daysCount,
            'avg_income' => $totalIncomes / $incomeCount,
            'savings_rate' => $totalIncomes > 0 ? ($balance / $totalIncomes) * 100 : 0,
            
            // Données pour les graphiques
            'category_labels' => $categoryExpenses->pluck('name')->toArray(),
            'category_values' => $categoryExpenses->pluck('amount')->toArray(),
            'category_colors' => $categoryExpenses->pluck('color')->toArray(),
            
            'daily_labels' => collect($dailyData)->pluck('date')->toArray(),
            'daily_expenses' => collect($dailyData)->pluck('expenses')->toArray(),
            'daily_incomes' => collect($dailyData)->pluck('incomes')->toArray(),
        ];
    }

    private function getYearlyData($userId, $year)
    {
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            
            $expenses = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount') ?? 0;
                
            $incomes = Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount') ?? 0;
                
            $monthlyData[] = [
                'month' => $startDate->translatedFormat('F'),
                'month_number' => $month,
                'expenses' => $expenses,
                'incomes' => $incomes,
                'balance' => $incomes - $expenses,
                'transaction_count' => Transaction::where('user_id', $userId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->count()
            ];
        }
        
        return $monthlyData;
    }

    private function getCategoryStats($userId, $startDate, $endDate)
    {
        // Récupérer toutes les catégories de l'utilisateur
        $categories = Category::where('user_id', $userId)
            ->with(['transactions' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get();
        
        // Calculer les statistiques par catégorie
        $stats = $categories->map(function($category) {
            $expenses = $category->transactions
                ->where('type', 'expense')
                ->sum('amount') ?? 0;
                
            $incomes = $category->transactions
                ->where('type', 'income')
                ->sum('amount') ?? 0;
                
            $transactionCount = $category->transactions->count();
            
            return [
                'category' => $category,
                'expenses' => $expenses,
                'incomes' => $incomes,
                'total' => $expenses + $incomes,
                'transaction_count' => $transactionCount,
                'percentage' => 0 // Calculé après
            ];
        })
        ->filter(fn($stat) => $stat['total'] > 0 || $stat['transaction_count'] > 0)
        ->sortByDesc('total')
        ->values();
        
        // Calculer les pourcentages
        $totalAmount = $stats->sum('total');
        if ($totalAmount > 0) {
            $stats = $stats->map(function($stat) use ($totalAmount) {
                $stat['percentage'] = ($stat['total'] / $totalAmount) * 100;
                return $stat;
            });
        }
        
        return $stats;
    }

    private function getExpenseTrends($userId)
    {
        $trends = [];
        $now = now();
        
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
            
            $expenses = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount') ?? 0;
                
            $trends[] = [
                'month' => $date->translatedFormat('M Y'),
                'date' => $date->format('Y-m'),
                'amount' => $expenses
            ];
        }
        
        return $trends;
    }

    private function getIncomeTrends($userId)
    {
        $trends = [];
        $now = now();
        
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
            
            $incomes = Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount') ?? 0;
                
            $trends[] = [
                'month' => $date->translatedFormat('M Y'),
                'date' => $date->format('Y-m'),
                'amount' => $incomes
            ];
        }
        
        return $trends;
    }

    private function calculateMonthlyAverage($userId, $type)
    {
        // Calculer la moyenne des 6 derniers mois
        $total = 0;
        $monthCount = 0;
        $now = now();
        
        for ($i = 0; $i < 6; $i++) {
            $date = $now->copy()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
            
            $amount = Transaction::where('user_id', $userId)
                ->where('type', $type)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount') ?? 0;
                
            if ($amount > 0) {
                $total += $amount;
                $monthCount++;
            }
        }
        
        return $monthCount > 0 ? $total / $monthCount : 0;
    }

    private function getExportData($userId, $period, $year = null, $month = null)
    {
        $query = Transaction::where('user_id', $userId)
            ->with('category');
        
        switch ($period) {
            case 'month':
                if ($year && $month) {
                    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                    $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
                break;
                
            case 'year':
                if ($year) {
                    $startDate = Carbon::create($year, 1, 1)->startOfYear();
                    $endDate = Carbon::create($year, 12, 31)->endOfYear();
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
                break;
                
            case 'all':
                // Toutes les données
                break;
        }
        
        return $query->orderBy('date', 'desc')->get();
    }

    private function generatePdf($data, $period, $year = null, $month = null)
    {
        $title = "Rapport financier - ";
        
        switch ($period) {
            case 'month':
                $title .= Carbon::create($year, $month, 1)->translatedFormat('F Y');
                break;
            case 'year':
                $title .= $year;
                break;
            default:
                $title .= "Toutes périodes";
        }
        
        $pdf = Pdf::loadView('reports.pdf.export', [
            'title' => $title,
            'transactions' => $data,
            'period' => $period,
            'generated_at' => now(),
            'totals' => [
                'expenses' => $data->where('type', 'expense')->sum('amount'),
                'incomes' => $data->where('type', 'income')->sum('amount'),
                'balance' => $data->where('type', 'income')->sum('amount') - 
                             $data->where('type', 'expense')->sum('amount')
            ]
        ]);
        
        return $pdf->download('rapport-financier.pdf');
    }
    
    /**
     * Export temporaire en vue HTML
     */
    private function exportAsView($data, $period, $year = null, $month = null)
    {
        $title = "Rapport financier - ";
        
        switch ($period) {
            case 'month':
                $title .= Carbon::create($year, $month, 1)->translatedFormat('F Y');
                break;
            case 'year':
                $title .= $year;
                break;
            default:
                $title .= "Toutes périodes";
        }
        
        // Calculer les totaux
        $totals = [
            'expenses' => $data->where('type', 'expense')->sum('amount'),
            'incomes' => $data->where('type', 'income')->sum('amount'),
            'balance' => $data->where('type', 'income')->sum('amount') - 
                         $data->where('type', 'expense')->sum('amount')
        ];
        
        return view('reports.pdf.export', [
            'title' => $title,
            'transactions' => $data,
            'period' => $period,
            'generated_at' => now(),
            'totals' => $totals
        ]);
    }
    
    /**
     * Export en CSV
     */
    private function exportAsCsv($data, $period, $year = null, $month = null)
    {
        $filename = 'rapport-financier-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // En-tête CSV
            fputcsv($file, [
                'Date', 'Type', 'Catégorie', 'Description', 
                'Montant (FDJ)', 'Méthode de paiement'
            ]);
            
            // Données
            foreach ($data as $transaction) {
                fputcsv($file, [
                    $transaction->date->format('d/m/Y'),
                    $transaction->type === 'expense' ? 'Dépense' : 'Revenu',
                    $transaction->category->name,
                    $transaction->description ?? '',
                    number_format($transaction->amount, 2, ',', ' '),
                    $this->getPaymentMethodLabel($transaction->payment_method)
                ]);
            }
            
            // Totaux
            fputcsv($file, ['', '', '', '', '', '']);
            fputcsv($file, [
                'Total Dépenses:', 
                '', '', '', 
                number_format($data->where('type', 'expense')->sum('amount'), 2, ',', ' '),
                ''
            ]);
            fputcsv($file, [
                'Total Revenus:', 
                '', '', '', 
                number_format($data->where('type', 'income')->sum('amount'), 2, ',', ' '),
                ''
            ]);
            fputcsv($file, [
                'Balance:', 
                '', '', '', 
                number_format(
                    $data->where('type', 'income')->sum('amount') - 
                    $data->where('type', 'expense')->sum('amount'), 
                    2, ',', ' '
                ),
                ''
            ]);
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    /**
     * Helper pour les méthodes de paiement
     */
    private function getPaymentMethodLabel($method)
    {
        return match($method) {
            'cash' => 'Espèces',
            'card' => 'Carte',
            'transfer' => 'Virement',
            'mobile_money' => 'Mobile Money',
            default => $method
        };
    }
}