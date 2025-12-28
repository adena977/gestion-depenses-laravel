<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class CategoryController extends Controller
{
      use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    $user = auth()->user();
    $activeTab = $request->get('tab', 'expense');
    
    // Récupérer toutes les catégories de l'utilisateur
    $categories = Category::where('user_id', $user->id)
        ->orderBy('is_default', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
    
    // Filtrer par type
    $expenseCategories = $categories->where('type', 'expense')->where('is_default', false);
    $incomeCategories = $categories->where('type', 'income')->where('is_default', false);
    $defaultCategories = $categories->where('is_default', true);
    
    // Si un type spécifique est demandé
    if ($activeTab === 'expense') {
        $categories = $categories->where('type', 'expense');
    } elseif ($activeTab === 'income') {
        $categories = $categories->where('type', 'income');
    }
    
    return view('categories.index', compact(
        'categories',
        'expenseCategories',
        'incomeCategories',
        'defaultCategories',
        'activeTab'
    ));
}

    /**
     * Show the form for creating a new resource.
     */
  public function create()
{
    $type = request('type', 'expense');
    return view('categories.create', compact('type'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:100|unique:categories,name,NULL,id,user_id,' . auth()->id(),
        'type' => 'required|in:expense,income',
        'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        'icon' => 'required|string|max:50',
        'description' => 'nullable|string',
    ]);
    
    // Vérifier que l'icône existe (validation basique)
    $validated['icon'] = str_replace('fa-', '', $validated['icon']);
    
    $validated['user_id'] = auth()->id();
    $validated['is_default'] = false;
    
    Category::create($validated);
    
    return redirect()->route('categories.index')
        ->with('success', 'Catégorie créée avec succès !');
}
    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit(Category $category)
{
    // Vérifier que l'utilisateur peut modifier cette catégorie
    $this->authorize('update', $category);
    
    return view('categories.edit', compact('category'));
}

public function update(Request $request, Category $category)
{
    // Vérifier les autorisations
    $this->authorize('update', $category);
    
    $validated = $request->validate([
        'name' => 'required|string|max:100|unique:categories,name,' . $category->id . ',id,user_id,' . auth()->id(),
        'type' => 'required|in:expense,income',
        'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        'icon' => 'required|string|max:50',
        'description' => 'nullable|string',
    ]);
    
    // Vérifier que le type n'a pas été modifié
    if ($validated['type'] !== $category->type) {
        return back()->withErrors([
            'type' => 'Le type de catégorie ne peut pas être modifié'
        ])->withInput();
    }
    
    // Vérifier que l'icône existe (validation basique)
    $validated['icon'] = str_replace('fa-', '', $validated['icon']);
    
    $category->update($validated);
    
    return redirect()->route('categories.index')
        ->with('success', 'Catégorie mise à jour avec succès !');
}

public function destroy(Request $request, Category $category)
{
    // Vérifier les autorisations
    $this->authorize('delete', $category);
    
    $action = $request->input('delete_action', 'reassign');
    $transactionCount = $category->transactions()->count();
    $budgetCount = $category->budgets()->count();
    
    // Vérifier si c'est une catégorie par défaut
    if ($category->is_default) {
        return redirect()->route('categories.index')
            ->with('error', 'Les catégories par défaut ne peuvent pas être supprimées.');
    }
    
    if ($action === 'reassign') {
        // Trouver ou créer la catégorie "Autres"
        $otherCategory = Category::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'name' => 'Autres',
                'type' => $category->type,
            ],
            [
                'color' => '#6B7280',
                'icon' => 'ellipsis-h',
                'is_default' => false,
            ]
        );
        
        // Réaffecter les transactions
        if ($transactionCount > 0) {
            $category->transactions()->update(['category_id' => $otherCategory->id]);
        }
        
        // Supprimer les budgets associés
        if ($budgetCount > 0) {
            $category->budgets()->delete();
        }
        
        $category->delete();
        
        return redirect()->route('categories.index')
            ->with('success', "Catégorie supprimée. {$transactionCount} transactions réaffectées à 'Autres'.");
            
    } elseif ($action === 'delete_all') {
        // Supprimer toutes les transactions et budgets
        $category->transactions()->delete();
        $category->budgets()->delete();
        $category->delete();
        
        return redirect()->route('categories.index')
            ->with('success', "Catégorie supprimée avec {$transactionCount} transactions et {$budgetCount} budgets.");
    }
    
    return redirect()->route('categories.index')
        ->with('error', 'Action de suppression non reconnue.');
}
}
