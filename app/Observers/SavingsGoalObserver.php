<?php

namespace App\Observers;

use App\Models\SavingsGoal;
use Illuminate\Support\Facades\Log;

class SavingsGoalObserver
{
    /**
     * Gérer l'événement "created".
     */
    public function created(SavingsGoal $savingsGoal): void
    {
        Log::info('Objectif d\'épargne créé', [
            'id' => $savingsGoal->id,
            'user_id' => $savingsGoal->user_id,
            'name' => $savingsGoal->name,
            'target_amount' => $savingsGoal->target_amount,
        ]);
    }

    /**
     * Gérer l'événement "updated".
     */
    public function updated(SavingsGoal $savingsGoal): void
    {
        Log::info('Objectif d\'épargne mis à jour', [
            'id' => $savingsGoal->id,
            'user_id' => $savingsGoal->user_id,
            'changes' => $savingsGoal->getChanges(),
        ]);
        
        // Envoyer une notification si l'objectif est complété
        if ($savingsGoal->wasChanged('is_completed') && $savingsGoal->is_completed) {
            $this->sendCompletionNotification($savingsGoal);
        }
    }

    /**
     * Gérer l'événement "deleted".
     */
    public function deleted(SavingsGoal $savingsGoal): void
    {
        Log::info('Objectif d\'épargne supprimé', [
            'id' => $savingsGoal->id,
            'user_id' => $savingsGoal->user_id,
            'name' => $savingsGoal->name,
        ]);
    }

    /**
     * Envoyer une notification de complétion.
     */
    private function sendCompletionNotification(SavingsGoal $savingsGoal): void
    {
        // Ici, vous pourriez envoyer une notification par email ou notification push
        // Exemple: Notification::send($savingsGoal->user, new SavingsGoalCompleted($savingsGoal));
        
        Log::info('Objectif d\'épargne complété - Notification à envoyer', [
            'goal_id' => $savingsGoal->id,
            'user_id' => $savingsGoal->user_id,
            'goal_name' => $savingsGoal->name,
        ]);
    }
}