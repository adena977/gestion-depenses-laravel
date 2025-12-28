<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BudgetPolicy
{
    use HandlesAuthorization;

    /**
     * Vérifie si l'utilisateur peut voir le budget
     */
    public function view(User $user, Budget $budget)
    {
        return $budget->user_id === $user->id;
    }

    /**
     * Vérifie si l'utilisateur peut mettre à jour le budget
     */
    public function update(User $user, Budget $budget)
    {
        return $budget->user_id === $user->id;
    }

    /**
     * Vérifie si l'utilisateur peut supprimer le budget
     */
    public function delete(User $user, Budget $budget)
    {
        return $budget->user_id === $user->id;
    }

    /**
     * Vérifie si l'utilisateur peut créer un budget
     */
    public function create(User $user)
    {
        return true; // Tous les utilisateurs authentifiés peuvent créer un budget
    }
}
