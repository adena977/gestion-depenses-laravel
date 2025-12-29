<?php

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SavingsGoalPolicy
{
    use HandlesAuthorization;

    /**
     * Déterminer si l'utilisateur peut voir l'objectif d'épargne.
     */
    public function view(User $user, SavingsGoal $savingsGoal): Response
    {
        return $user->id === $savingsGoal->user_id
            ? Response::allow()
            : Response::deny('Vous n\'avez pas accès à cet objectif d\'épargne.');
    }

    /**
     * Déterminer si l'utilisateur peut créer un objectif d'épargne.
     */
    public function create(User $user): bool
    {
        return true; // Tout utilisateur authentifié peut créer
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour l'objectif d'épargne.
     */
    public function update(User $user, SavingsGoal $savingsGoal): Response
    {
        return $user->id === $savingsGoal->user_id
            ? Response::allow()
            : Response::deny('Vous n\'êtes pas autorisé à modifier cet objectif.');
    }

    /**
     * Déterminer si l'utilisateur peut supprimer l'objectif d'épargne.
     */
    public function delete(User $user, SavingsGoal $savingsGoal): Response
    {
        return $user->id === $savingsGoal->user_id
            ? Response::allow()
            : Response::deny('Vous n\'êtes pas autorisé à supprimer cet objectif.');
    }

    /**
     * Déterminer si l'utilisateur peut ajouter des fonds à l'objectif.
     */
    public function addFunds(User $user, SavingsGoal $savingsGoal): Response
    {
        return $user->id === $savingsGoal->user_id && !$savingsGoal->is_completed
            ? Response::allow()
            : Response::deny('Vous ne pouvez pas ajouter de fonds à cet objectif.');
    }

    /**
     * Déterminer si l'utilisateur peut retirer des fonds de l'objectif.
     */
    public function withdrawFunds(User $user, SavingsGoal $savingsGoal): Response
    {
        return $user->id === $savingsGoal->user_id && $savingsGoal->current_amount > 0
            ? Response::allow()
            : Response::deny('Vous ne pouvez pas retirer de fonds de cet objectif.');
    }

    /**
     * Déterminer si l'utilisateur peut marquer l'objectif comme terminé.
     */
    public function complete(User $user, SavingsGoal $savingsGoal): Response
    {
        return $user->id === $savingsGoal->user_id && !$savingsGoal->is_completed
            ? Response::allow()
            : Response::deny('Vous ne pouvez pas marquer cet objectif comme terminé.');
    }

    /**
     * Déterminer si l'utilisateur peut réactiver l'objectif.
     */
    public function reactivate(User $user, SavingsGoal $savingsGoal): Response
    {
        return $user->id === $savingsGoal->user_id && $savingsGoal->is_completed
            ? Response::allow()
            : Response::deny('Vous ne pouvez pas réactiver cet objectif.');
    }
}