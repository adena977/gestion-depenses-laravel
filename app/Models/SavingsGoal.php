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
     * The attributes that are mass assignable.
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline' => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'progress_percentage',
        'remaining_amount',
        'remaining_days',
        'is_completed',
        'is_overdue',
    ];

    // RELATIONS

    /**
     * Get the user that owns the savings goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ACCESSORS

    /**
     * Get the progress percentage.
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
     * Get the remaining amount.
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Get remaining days until deadline.
     */
    public function getRemainingDaysAttribute()
    {
        if (!$this->deadline) {
            return null;
        }

        return max(0, Carbon::parse($this->deadline)->diffInDays(now(), false) * -1);
    }

    /**
     * Check if goal is completed.
     */
    public function getIsCompletedAttribute()
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Check if goal is overdue.
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->deadline) {
            return false;
        }

        return !$this->is_completed && $this->remaining_days < 0;
    }

    /**
     * Get status color based on progress.
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
     * Get formatted deadline.
     */
    public function getFormattedDeadlineAttribute()
    {
        if (!$this->deadline) {
            return 'Aucune date limite';
        }

        return $this->deadline->format('d/m/Y');
    }

    // MUTATORS

    /**
     * Set the color attribute with default.
     */
    public function setColorAttribute($value)
    {
        $this->attributes['color'] = $value ?: '#10B981';
    }

    // HELPERS

    /**
     * Add amount to current savings.
     */
    public function addAmount(float $amount): bool
    {
        $this->current_amount += $amount;
        return $this->save();
    }

    /**
     * Withdraw amount from savings.
     */
    public function withdrawAmount(float $amount): bool
    {
        if ($amount > $this->current_amount) {
            return false;
        }

        $this->current_amount -= $amount;
        return $this->save();
    }

    /**
     * Get daily amount needed to reach goal.
     */
    public function getDailyAmountNeeded(): float
    {
        if (!$this->deadline || $this->is_completed || $this->is_overdue) {
            return 0;
        }

        $remainingDays = $this->remaining_days;
        if ($remainingDays <= 0) {
            return $this->remaining_amount;
        }

        return $this->remaining_amount / $remainingDays;
    }

    /**
     * Get monthly amount needed to reach goal.
     */
    public function getMonthlyAmountNeeded(): float
    {
        if (!$this->deadline || $this->is_completed || $this->is_overdue) {
            return 0;
        }

        $remainingDays = $this->remaining_days;
        if ($remainingDays <= 0) {
            return $this->remaining_amount;
        }

        $remainingMonths = ceil($remainingDays / 30);
        return $this->remaining_amount / max(1, $remainingMonths);
    }
}