<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SavingsGoalController extends Controller
{
    use AuthorizesRequests;

    /**
     * Afficher la liste des objectifs d'épargne.
     */
  // Dans SavingsGoalController.php, modifiez la méthode index() :
public function index(Request $request)
{
    $user = Auth::user();
    
    // Récupérer le filtre d'onglet
    $activeTab = $request->get('tab', 'all');
    
    // Base query
    $query = SavingsGoal::forUser($user->id);
    
    // Appliquer le filtre selon l'onglet
    switch ($activeTab) {
        case 'active':
            $query->active();
            break;
        case 'completed':
            $query->completed();
            break;
        case 'overdue':
            $query->overdue();
            break;
        case 'all':
        default:
            // Pas de filtre
            break;
    }
    
    // Récupérer les objectifs avec tri par priorité
    $savingsGoals = $query->orderByPriority()->get();
    
    // Récupérer TOUS les objectifs pour les statistiques
    $allGoals = SavingsGoal::forUser($user->id)->get();
    
    // Calcul des statistiques
    $totalSaved = $allGoals->sum('current_amount');
    $totalTarget = $allGoals->where('is_completed', false)->sum('target_amount');
    $totalProgress = $totalTarget > 0 ? ($totalSaved / $totalTarget) * 100 : 0;
    
    // Collections pour les statistiques
    $activeGoals = $allGoals->where('is_completed', false);
    $completedGoals = $allGoals->where('is_completed', true);
    $overdueGoals = $allGoals->where('is_overdue', true);
    
    return view('savings.index', compact(
        'savingsGoals',
        'activeTab',
        'totalSaved',
        'totalTarget',
        'totalProgress',
        'activeGoals',
        'completedGoals',
        'overdueGoals'
    ));
}

    /**
     * Afficher le formulaire de création d'un objectif d'épargne.
     */
    public function create()
    {
        return view('savings.create');
    }

    /**
     * Stocker un nouvel objectif d'épargne.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'current_amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|size:7',
        ]);

        try {
            DB::beginTransaction();
            
            $savingsGoal = new SavingsGoal();
            $savingsGoal->user_id = Auth::id();
            $savingsGoal->name = $validated['name'];
            $savingsGoal->target_amount = $validated['target_amount'];
            $savingsGoal->current_amount = $validated['current_amount'] ?? 0;
            $savingsGoal->deadline = $validated['deadline'] ?? null;
            $savingsGoal->description = $validated['description'] ?? null;
            $savingsGoal->color = $validated['color'] ?? '#10B981';
            
            // Vérifier si déjà complété
            if ($savingsGoal->current_amount >= $savingsGoal->target_amount) {
                $savingsGoal->is_completed = true;
                $savingsGoal->completed_at = now();
            }
            
            $savingsGoal->save();
            
            DB::commit();
            
            return redirect()->route('savings.index')
                ->with('success', 'Objectif d\'épargne créé avec succès!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création objectif épargne: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'objectif: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher un objectif d'épargne spécifique.
     */
    public function show(SavingsGoal $savingsGoal)
    {
        $this->authorize('view', $savingsGoal);
        
        // Récupérer les transactions liées (si vous avez un système de transactions d'épargne)
        $transactions = $this->getSavingsTransactions($savingsGoal);
        
        // Calculer les statistiques détaillées
        $stats = [
            'daily_needed' => $savingsGoal->daily_amount_needed,
            'weekly_needed' => $savingsGoal->weekly_amount_needed,
            'monthly_needed' => $savingsGoal->monthly_amount_needed,
            'recommended_monthly' => $savingsGoal->getRecommendedMonthlySaving(),
        ];
        
        return view('savings.show', compact('savingsGoal', 'transactions', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition d'un objectif d'épargne.
     */
    public function edit(SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);
        
        return view('savings.edit', compact('savingsGoal'));
    }

    /**
     * Mettre à jour un objectif d'épargne.
     */
    public function update(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'current_amount' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|size:7',
        ]);

        try {
            DB::beginTransaction();
            
            $savingsGoal->name = $validated['name'];
            $savingsGoal->target_amount = $validated['target_amount'];
            $savingsGoal->current_amount = $validated['current_amount'];
            $savingsGoal->deadline = $validated['deadline'] ?? null;
            $savingsGoal->description = $validated['description'] ?? null;
            $savingsGoal->color = $validated['color'] ?? $savingsGoal->color;
            
            // Mettre à jour le statut de complétion
            if ($savingsGoal->current_amount >= $savingsGoal->target_amount && !$savingsGoal->is_completed) {
                $savingsGoal->is_completed = true;
                $savingsGoal->completed_at = now();
            } elseif ($savingsGoal->current_amount < $savingsGoal->target_amount && $savingsGoal->is_completed) {
                $savingsGoal->is_completed = false;
                $savingsGoal->completed_at = null;
            }
            
            $savingsGoal->save();
            
            DB::commit();
            
            return redirect()->route('savings.show', $savingsGoal)
                ->with('success', 'Objectif d\'épargne mis à jour avec succès!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour objectif épargne: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un objectif d'épargne.
     */
    public function destroy(SavingsGoal $savingsGoal)
    {
        $this->authorize('delete', $savingsGoal);
        
        try {
            $savingsGoal->delete();
            
            return redirect()->route('savings.index')
                ->with('success', 'Objectif d\'épargne supprimé avec succès!');
                
        } catch (\Exception $e) {
            Log::error('Erreur suppression objectif épargne: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Ajouter des fonds à un objectif d'épargne.
     */
    public function addFunds(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();
            
            $amount = $validated['amount'];
            $description = $validated['description'] ?? 'Dépôt manuel';
            
            $success = $savingsGoal->addAmount($amount, $description);
            
            if (!$success) {
                throw new \Exception('Impossible d\'ajouter les fonds');
            }
            
            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fonds ajoutés avec succès!',
                    'goal' => $savingsGoal->fresh(),
                    'new_amount' => $savingsGoal->current_amount,
                ]);
            }
            
            return redirect()->route('savings.show', $savingsGoal)
                ->with('success', sprintf('%s FDJ ajoutés à votre objectif!', number_format($amount, 2)));
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout fonds objectif: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'ajout des fonds: ' . $e->getMessage());
        }
    }

    /**
     * Retirer des fonds d'un objectif d'épargne.
     */
    public function withdrawFunds(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $savingsGoal->current_amount,
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();
            
            $amount = $validated['amount'];
            $description = $validated['description'] ?? 'Retrait manuel';
            
            $success = $savingsGoal->withdrawAmount($amount);
            
            if (!$success) {
                throw new \Exception('Impossible de retirer les fonds');
            }
            
            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fonds retirés avec succès!',
                    'goal' => $savingsGoal->fresh(),
                    'new_amount' => $savingsGoal->current_amount,
                ]);
            }
            
            return redirect()->route('savings.show', $savingsGoal)
                ->with('success', sprintf('%s FDJ retirés de votre objectif!', number_format($amount, 2)));
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur retrait fonds objectif: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erreur lors du retrait des fonds: ' . $e->getMessage());
        }
    }

    /**
     * Marquer un objectif comme terminé.
     */
    public function complete(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);
        
        try {
            $savingsGoal->markAsCompleted();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Objectif marqué comme terminé!',
                    'goal' => $savingsGoal->fresh(),
                ]);
            }
            
            return redirect()->route('savings.show', $savingsGoal)
                ->with('success', 'Objectif marqué comme terminé!');
                
        } catch (\Exception $e) {
            Log::error('Erreur complétion objectif: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Réactiver un objectif terminé.
     */
    public function reactivate(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);
        
        try {
            $savingsGoal->reactivate();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Objectif réactivé!',
                    'goal' => $savingsGoal->fresh(),
                ]);
            }
            
            return redirect()->route('savings.show', $savingsGoal)
                ->with('success', 'Objectif réactivé!');
                
        } catch (\Exception $e) {
            Log::error('Erreur réactivation objectif: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir le résumé des objectifs d'épargne (pour dashboard).
     */
    public function summary(Request $request)
    {
        $user = Auth::user();
        
        $summary = [
            'total_goals' => SavingsGoal::forUser($user->id)->count(),
            'active_goals' => SavingsGoal::forUser($user->id)->active()->count(),
            'completed_goals' => SavingsGoal::forUser($user->id)->completed()->count(),
            'overdue_goals' => SavingsGoal::forUser($user->id)->overdue()->count(),
            'total_saved' => SavingsGoal::forUser($user->id)->sum('current_amount'),
            'total_target' => SavingsGoal::forUser($user->id)->sum('target_amount'),
        ];
        
        $summary['overall_progress'] = $summary['total_target'] > 0 
            ? round(($summary['total_saved'] / $summary['total_target']) * 100, 2)
            : 0;
        
        $recentGoals = SavingsGoal::forUser($user->id)
            ->recent()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $nearingDeadline = SavingsGoal::forUser($user->id)
            ->nearingDeadline()
            ->get();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'summary' => $summary,
                'recent_goals' => $recentGoals,
                'nearing_deadline' => $nearingDeadline,
            ]);
        }
        
        return compact('summary', 'recentGoals', 'nearingDeadline');
    }

    /**
     * Obtenir les transactions d'épargne liées à un objectif.
     */
    private function getSavingsTransactions(SavingsGoal $savingsGoal)
    {
        // Cette méthode devrait retourner les transactions spécifiques à l'épargne
        // Pour l'instant, retourner une collection vide
        return collect();
        
        // Exemple d'implémentation si vous avez une table savings_transactions:
        // return $savingsGoal->transactions()->orderBy('created_at', 'desc')->get();
    }
}