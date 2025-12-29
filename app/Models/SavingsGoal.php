<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SavingsGoal extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'deadline',
        'color',
        'is_completed',
        'completed_at',
        'description',
    ];

    /**
     * Les attributs à caster.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'date',
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Les accesseurs à ajouter au tableau du modèle.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'progress_percentage',
        'remaining_amount',
        'remaining_days',
        'is_overdue',
        'status_color',
        'formatted_deadline',
        'days_left_text',
        'daily_amount_needed',
        'weekly_amount_needed',
        'monthly_amount_needed',
        'is_active',
    ];

    // RELATIONS

    /**
     * Obtenir l'utilisateur propriétaire de l'objectif d'épargne.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ACCESSORS

    /**
     * Obtenir le pourcentage de progression.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        $percentage = ($this->current_amount / $this->target_amount) * 100;
        return min(100, round($percentage, 2));
    }

    /**
     * Obtenir le montant restant.
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Obtenir le nombre de jours restants.
     * Valeur positive = jours restants, Valeur négative = jours de retard
     */
    public function getRemainingDaysAttribute()
    {
        if (!$this->deadline) {
            return null;
        }

        $now = Carbon::now();
        $deadline = Carbon::parse($this->deadline);
        
        // diffInDays avec false pour obtenir une différence signée
        return $deadline->diffInDays($now, false);
    }

    /**
     * Vérifier si l'objectif est en retard.
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->deadline || $this->is_completed) {
            return false;
        }

        return $this->deadline < now();
    }

    /**
     * Vérifier si l'objectif est actif.
     */
    public function getIsActiveAttribute()
    {
        return !$this->is_completed && !$this->is_overdue;
    }

    /**
     * Obtenir la couleur du statut basée sur la progression.
     */
    public function getStatusColorAttribute()
    {
        if ($this->is_completed) {
            return 'success';
        } elseif ($this->is_overdue) {
            return 'error';
        } elseif ($this->remaining_days !== null && $this->remaining_days < 7) {
            return 'warning';
        } else {
            return 'primary';
        }
    }

    /**
     * Obtenir la date limite formatée.
     */
    public function getFormattedDeadlineAttribute()
    {
        if (!$this->deadline) {
            return 'Aucune date limite';
        }

        return $this->deadline->format('d/m/Y');
    }

    /**
     * Obtenir le texte des jours restants pour l'affichage.
     */
    public function getDaysLeftTextAttribute()
    {
        if (!$this->deadline) {
            return 'Aucune date limite';
        }

        $days = $this->remaining_days;
        
        if ($days > 0) {
            return round($days, 0) . ' jour' . ($days > 1 ? 's' : '') . ' restant' . ($days > 1 ? 's' : '');
        } elseif ($days === 0) {
            return "Aujourd'hui!";
        } else {
            $daysLate = abs($days);
            return round($daysLate, 0) . ' jour' . ($daysLate > 1 ? 's' : '') . ' de retard';
        }
    }

    /**
     * Obtenir le montant quotidien nécessaire pour atteindre l'objectif.
     */
    public function getDailyAmountNeededAttribute()
    {
        if (!$this->deadline || $this->is_completed) {
            return 0;
        }

        $days = $this->remaining_days;
        
        if ($days <= 0) {
            return 0;
        }

        return round($this->remaining_amount / $days, 2);
    }

    /**
     * Obtenir le montant hebdomadaire nécessaire pour atteindre l'objectif.
     */
    public function getWeeklyAmountNeededAttribute()
    {
        if (!$this->deadline || $this->is_completed) {
            return 0;
        }

        $days = $this->remaining_days;
        
        if ($days <= 0) {
            return 0;
        }

        $remainingWeeks = ceil($days / 7);
        return round($this->remaining_amount / max(1, $remainingWeeks), 2);
    }

    /**
     * Obtenir le montant mensuel nécessaire pour atteindre l'objectif.
     */
    public function getMonthlyAmountNeededAttribute()
    {
        if (!$this->deadline || $this->is_completed) {
            return 0;
        }

        $days = $this->remaining_days;
        
        if ($days <= 0) {
            return 0;
        }

        $remainingMonths = ceil($days / 30);
        return round($this->remaining_amount / max(1, $remainingMonths), 2);
    }

    // MUTATORS

    /**
     * Définir l'attribut color avec une valeur par défaut.
     */
    public function setColorAttribute($value)
    {
        $this->attributes['color'] = $value ?: '#10B981';
    }

    /**
     * Définir l'attribut current_amount pour s'assurer qu'il n'est pas supérieur à target_amount.
     */
    public function setCurrentAmountAttribute($value)
    {
        $this->attributes['current_amount'] = min($value, $this->target_amount);
        
        // Marquer comme terminé si le montant actuel atteint ou dépasse la cible
        if ($value >= $this->target_amount) {
            $this->attributes['is_completed'] = true;
            if (!isset($this->attributes['completed_at'])) {
                $this->attributes['completed_at'] = now();
            }
        }
    }

    // HELPERS

    /**
     * Ajouter un montant à l'épargne actuelle.
     */
    public function addAmount(float $amount, string $description = null): bool
    {
        $this->current_amount += $amount;
        
        if ($this->current_amount >= $this->target_amount) {
            $this->markAsCompleted();
        } else {
            $this->save();
        }
        
        return true;
    }

    /**
     * Retirer un montant de l'épargne.
     */
    public function withdrawAmount(float $amount): bool
    {
        if ($amount > $this->current_amount) {
            return false;
        }

        $this->current_amount -= $amount;
        
        if ($this->is_completed && $this->current_amount < $this->target_amount) {
            $this->is_completed = false;
            $this->completed_at = null;
        }
        
        return $this->save();
    }

    /**
     * Marquer l'objectif comme terminé.
     */
    public function markAsCompleted(): bool
    {
        $this->is_completed = true;
        $this->completed_at = now();
        return $this->save();
    }

    /**
     * Réactiver un objectif terminé.
     */
    public function reactivate(): bool
    {
        $this->is_completed = false;
        $this->completed_at = null;
        return $this->save();
    }

    /**
     * Vérifier si l'objectif est complété.
     */
    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    /**
     * Obtenir le montant recommandé à épargner par mois.
     */
    public function getRecommendedMonthlySaving(): float
    {
        if (!$this->deadline || $this->is_completed) {
            return 0;
        }

        $now = now();
        $deadline = Carbon::parse($this->deadline);
        
        if ($deadline <= $now) {
            return $this->remaining_amount;
        }

        $monthsRemaining = $now->diffInMonths($deadline, false);
        if ($monthsRemaining <= 0) {
            $monthsRemaining = 1;
        }

        return round($this->remaining_amount / $monthsRemaining, 2);
    }

    // SCOPES

    /**
     * Scope pour les objectifs terminés.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope pour les objectifs actifs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope pour les objectifs en retard.
     */
    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                    ->where('is_completed', false);
    }

    /**
     * Scope pour les objectifs avec date limite.
     */
    public function scopeWithDeadline($query)
    {
        return $query->whereNotNull('deadline');
    }

    /**
     * Scope pour les objectifs sans date limite.
     */
    public function scopeWithoutDeadline($query)
    {
        return $query->whereNull('deadline');
    }

    /**
     * Scope pour les objectifs approchant de la date limite (moins de 7 jours).
     */
    public function scopeNearingDeadline($query)
    {
        return $query->whereNotNull('deadline')
                    ->where('deadline', '<=', now()->addDays(7))
                    ->where('is_completed', false);
    }

    /**
     * Scope pour les objectifs triés par priorité (en retard d'abord, puis approchant la date limite, puis autres).
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw('
            CASE 
                WHEN deadline < NOW() AND is_completed = 0 THEN 1
                WHEN deadline <= DATE_ADD(NOW(), INTERVAL 7 DAY) AND is_completed = 0 THEN 2
                ELSE 3
            END
        ')->orderBy('deadline', 'asc');
    }

    /**
     * Scope pour les objectifs récents (créés dans les 30 derniers jours).
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Scope pour les objectifs d'un utilisateur spécifique.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}