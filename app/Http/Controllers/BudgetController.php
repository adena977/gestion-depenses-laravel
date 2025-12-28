<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class BudgetController extends Controller
{   use AuthorizesRequests;
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeTab = $request->get('tab', 'active');
        $today = now()->format('Y-m-d');
        
        // Récupérer tous les budgets
        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->orderBy('start_date', 'desc')
            ->get();
        
        // Filtrer les budgets actifs (date courante entre start_date et end_date)
        $activeBudgets = $budgets->filter(function($budget) use ($today) {
            return $budget->start_date <= $today && $budget->end_date >= $today;
        });
        
        // Filtrer les budgets expirés
        $expiredBudgets = $budgets->filter(function($budget) use ($today) {
            return $budget->end_date < $today;
        });
        
        // Calculer les totaux
        $totalBudgets = $budgets->count();
        $totalBudgetAmount = $activeBudgets->sum('amount');
        $totalSpent = 0;
        
        // Calculer le total dépensé pour les budgets actifs
        foreach ($activeBudgets as $budget) {
            $totalSpent += $budget->spent_amount;
        }
        
        $totalSpentPercentage = $totalBudgetAmount > 0 ? ($totalSpent / $totalBudgetAmount) * 100 : 0;
        
        // Alertes de budget
        $budgetAlerts = $activeBudgets->filter(function($budget) {
            return $budget->progress_percentage >= $budget->threshold_percentage;
        });
        
        // Budgets par catégorie
        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->orWhere(function($query) use ($user) {
                $query->where('is_default', true)
                      ->where('type', 'expense');
            })
            ->get();
        
        $budgetsByCategory = $categories->map(function($category) use ($user, $today) {
            // Trouver le budget actif pour cette catégorie
            $budget = Budget::where('user_id', $user->id)
                ->where('category_id', $category->id)
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->first();
            
            // Calculer les dépenses
            $spent = $category->transactions()
                ->where('user_id', $user->id)
                ->where('type', 'expense')
                ->when($budget, function($query) use ($budget) {
                    return $query->whereBetween('date', [$budget->start_date, $budget->end_date]);
                }, function($query) {
                    // Si pas de budget, prendre le mois courant
                    return $query->whereMonth('date', now()->month)
                                ->whereYear('date', now()->year);
                })
                ->sum('amount');
            
            $percentage = 0;
            if ($budget && $budget->amount > 0) {
                $percentage = ($spent / $budget->amount) * 100;
            }
            
            return [
                'category' => $category,
                'budget' => $budget,
                'spent' => $spent,
                'percentage' => $percentage,
            ];
        })->sortByDesc('percentage');
        
        // Filtrer selon l'onglet actif
        if ($activeTab === 'active') {
            $budgets = $activeBudgets;
        } elseif ($activeTab === 'expired') {
            $budgets = $expiredBudgets;
        }
        
        return view('budgets.index', compact(
            'budgets',
            'activeBudgets',
            'expiredBudgets',
            'totalBudgets',
            'totalBudgetAmount',
            'totalSpent',
            'totalSpentPercentage',
            'budgetAlerts',
            'budgetsByCategory',
            'activeTab'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Récupérer les catégories de dépenses
        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->orWhere(function($query) use ($user) {
                $query->where('is_default', true)
                      ->where('type', 'expense');
            })
            ->get();
        
        // Pré-sélectionner une catégorie si passée en paramètre
        $category_id = request('category_id');
        
        return view('budgets.create', compact('categories', 'category_id'));
     }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'period' => 'required|in:monthly,weekly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notifications_enabled' => 'nullable|boolean',
            'threshold_percentage' => 'nullable|integer|min:1|max:100',
        ]);
        
        // Vérifier que la catégorie appartient bien à l'utilisateur
        $category = Category::findOrFail($validated['category_id']);
        if ($category->user_id !== auth()->id() && !$category->is_default) {
            abort(403, 'Cette catégorie ne vous appartient pas');
        }
        
        // Vérifier que la catégorie est une catégorie de dépense
        if ($category->type !== 'expense') {
            return back()->withErrors([
                'category_id' => 'Seules les catégories de dépenses peuvent avoir un budget'
            ])->withInput();
        }
        
        $validated['user_id'] = auth()->id();
        
        // Définir les valeurs par défaut
        $validated['notifications_enabled'] = $request->has('notifications_enabled');
        $validated['threshold_percentage'] = $validated['threshold_percentage'] ?? 80;
        
        // Si pas de date de fin, elle sera calculée automatiquement par le mutator
        if (empty($validated['end_date'])) {
            unset($validated['end_date']);
        }
        
        Budget::create($validated);
        
        return redirect()->route('budgets.index')
            ->with('success', 'Budget créé avec succès !');
    }

   public function show(Budget $budget)
{
    // Vérifier les autorisations
    $this->authorize('view', $budget);
    
    // Récupérer les transactions associées à ce budget
    $transactions = $budget->category->transactions()
        ->where('user_id', auth()->id())
        ->where('type', 'expense')
        ->whereBetween('date', [$budget->start_date, $budget->end_date])
        ->orderBy('date', 'desc')
        ->paginate(15);
    
    // Calculer les statistiques supplémentaires
    $today = now();
    $daysTotal = $budget->start_date->diffInDays($budget->end_date) + 1;
    $daysPassed = min($daysTotal, max(0, $today->diffInDays($budget->start_date) + 1));
    $daysLeft = max(0, $budget->end_date->diffInDays($today) + 1);
    
    $averagePerDay = $budget->amount / $daysTotal;
    $actualAveragePerDay = $daysPassed > 0 ? $budget->spent_amount / $daysPassed : 0;
    $dailyBudget = $daysLeft > 0 ? $budget->remaining_amount / $daysLeft : 0;
    
    return view('budgets.show', compact(
        'budget',
        'transactions',
        'daysTotal',
        'daysPassed',
        'daysLeft',
        'averagePerDay',
        'actualAveragePerDay',
        'dailyBudget'
    ));
}

    public function edit(Budget $budget)
    {
        $this->authorize('update', $budget);
        
        $user = auth()->user();
        
        // Récupérer les catégories de dépenses
        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->orWhere(function($query) use ($user) {
                $query->where('is_default', true)
                      ->where('type', 'expense');
            })
            ->get();
        
         return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);
        
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'period' => 'required|in:monthly,weekly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notifications_enabled' => 'nullable|boolean',
            'threshold_percentage' => 'nullable|integer|min:1|max:100',
        ]);
        
        // Vérifier que la catégorie appartient bien à l'utilisateur
        $category = Category::findOrFail($validated['category_id']);
        if ($category->user_id !== auth()->id() && !$category->is_default) {
            abort(403, 'Cette catégorie ne vous appartient pas');
        }
        
        // Vérifier que la catégorie est une catégorie de dépense
        if ($category->type !== 'expense') {
            return back()->withErrors([
                'category_id' => 'Seules les catégories de dépenses peuvent avoir un budget'
            ])->withInput();
        }
        
        // Définir les valeurs par défaut
        $validated['notifications_enabled'] = $request->has('notifications_enabled');
        $validated['threshold_percentage'] = $validated['threshold_percentage'] ?? 80;
        
        // Si pas de date de fin, elle sera calculée automatiquement par le mutator
        if (empty($validated['end_date'])) {
            unset($validated['end_date']);
        }
        
        $budget->update($validated);
        
        return redirect()->route('budgets.index')
            ->with('success', 'Budget mis à jour avec succès !');
    }

    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);
        
        $budget->delete();
        
        return redirect()->route('budgets.index')
            ->with('success', 'Budget supprimé avec succès !');
    }
}