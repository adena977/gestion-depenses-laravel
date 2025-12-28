<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
        use AuthorizesRequests;
   public function index(Request $request)
{
    $user = auth()->user();
    
    // Récupérer les paramètres de filtre
    $search = $request->get('search');
    $type = $request->get('type');
    $category_id = $request->get('category_id');
    $start_date = $request->get('start_date');
    $end_date = $request->get('end_date');
    
    // Construire la requête
    $query = Transaction::with('category')
        ->where('user_id', $user->id)
        ->latestFirst();
    
    // Appliquer les filtres
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }
    
    if ($type && in_array($type, ['expense', 'income'])) {
        $query->where('type', $type);
    }
    
    if ($category_id) {
        $query->where('category_id', $category_id);
    }
    
    if ($start_date) {
        $query->whereDate('date', '>=', $start_date);
    }
    
    if ($end_date) {
        $query->whereDate('date', '<=', $end_date);
    }
    
    // Paginer les résultats
    $transactions = $query->paginate(20);
    
    // Calculer les totaux
    $totalExpenses = Transaction::where('user_id', $user->id)
        ->where('type', 'expense')
        ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
        ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
        ->sum('amount');
    
    $totalIncome = Transaction::where('user_id', $user->id)
        ->where('type', 'income')
        ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
        ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
        ->sum('amount');
    
    $balance = $totalIncome - $totalExpenses;
    
    // Récupérer les catégories pour le filtre
    $categories = Category::where('user_id', $user->id)
        ->orWhere('is_default', true)
        ->get();
    
    return view('transactions.index', compact(
        'transactions',
        'totalExpenses',
        'totalIncome',
        'balance',
        'categories',
        'search',
        'type',
        'category_id',
        'start_date',
        'end_date'
    ));
}
    
 public function create()
{
    $user = auth()->user();
    
    // Récupérer les catégories de l'utilisateur
    $categories = Category::where('user_id', $user->id)
        ->orWhere('is_default', true)
        ->get();
    
    // Pré-sélectionner le type si passé en paramètre
    $type = request('type', 'expense');
    
    return view('transactions.create', compact('categories', 'type'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'amount' => 'required|numeric|min:0.01|max:9999999.99',
        'type' => 'required|in:expense,income',
        'description' => 'nullable|string|max:255',
        'date' => 'required|date',
        'payment_method' => 'nullable|in:cash,card,transfer,mobile_money',
        'location' => 'nullable|string|max:255',
        'is_recurring' => 'nullable|boolean',
        'recurring_frequency' => 'nullable|in:daily,weekly,monthly,yearly',
    ]);
    
    // Vérifier que la catégorie appartient bien à l'utilisateur
    $category = Category::findOrFail($validated['category_id']);
    if ($category->user_id !== auth()->id() && !$category->is_default) {
        abort(403, 'Cette catégorie ne vous appartient pas');
    }
    
    // Vérifier que le type correspond à la catégorie
    if ($category->type !== $validated['type']) {
        return back()->withErrors([
            'category_id' => 'La catégorie sélectionnée ne correspond pas au type de transaction'
        ])->withInput();
    }
    
    $validated['user_id'] = auth()->id();
    
    // Gérer les transactions récurrentes
    if ($request->has('is_recurring') && $request->is_recurring) {
        $validated['is_recurring'] = true;
        $validated['recurring_frequency'] = $request->recurring_frequency;
    } else {
        $validated['is_recurring'] = false;
        $validated['recurring_frequency'] = null;
    }
    
    Transaction::create($validated);
    
    return redirect()->route('transactions.index')
        ->with('success', 'Transaction créée avec succès !');
}
    
 public function edit(Transaction $transaction)
{
    // Vérifier que l'utilisateur peut modifier cette transaction
    $this->authorize('update', $transaction);
    
    // Récupérer les catégories du même type que la transaction
    $categories = Category::where('user_id', auth()->id())
        ->where('type', $transaction->type)
        ->orWhere(function($query) use ($transaction) {
            $query->where('is_default', true)
                  ->where('type', $transaction->type);
        })
        ->get();
    
    return view('transactions.edit', compact('transaction', 'categories'));
}

public function update(Request $request, Transaction $transaction)
{
    // Vérifier les autorisations
    $this->authorize('update', $transaction);
    
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'amount' => 'required|numeric|min:0.01|max:9999999.99',
        'type' => 'required|in:expense,income',
        'description' => 'nullable|string|max:255',
        'date' => 'required|date',
        'payment_method' => 'nullable|in:cash,card,transfer,mobile_money',
        'location' => 'nullable|string|max:255',
        'is_recurring' => 'nullable|boolean',
        'recurring_frequency' => 'nullable|in:daily,weekly,monthly,yearly',
    ]);
    
    // Vérifier que la catégorie appartient bien à l'utilisateur
    $category = Category::findOrFail($validated['category_id']);
    if ($category->user_id !== auth()->id() && !$category->is_default) {
        abort(403, 'Cette catégorie ne vous appartient pas');
    }
    
    // Vérifier que le type correspond à la catégorie
    if ($category->type !== $validated['type']) {
        return back()->withErrors([
            'category_id' => 'La catégorie sélectionnée ne correspond pas au type de transaction'
        ])->withInput();
    }
    
    // Gérer les transactions récurrentes
    if ($request->has('is_recurring') && $request->is_recurring) {
        $validated['is_recurring'] = true;
        $validated['recurring_frequency'] = $request->recurring_frequency;
    } else {
        $validated['is_recurring'] = false;
        $validated['recurring_frequency'] = null;
    }
    
    $transaction->update($validated);
    
    return redirect()->route('transactions.index')
        ->with('success', 'Transaction mise à jour avec succès !');
}

public function destroy(Transaction $transaction)
{
    $this->authorize('delete', $transaction);
    
    $transaction->delete();
    
    return redirect()->route('transactions.index')
        ->with('success', 'Transaction supprimée avec succès !');
}}