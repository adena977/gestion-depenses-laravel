<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'period',
        'start_date',
        'end_date',
        'notifications_enabled',
        'threshold_percentage',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'notifications_enabled' => 'boolean',
        'threshold_percentage' => 'integer',
    ];

    protected $appends = [
        'spent_amount', 
        'remaining_amount', 
        'progress_percentage', 
        'is_active',
        'formatted_period',
        'status_color'
    ];

    // -------------------
    // RELATIONS
    // -------------------
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // -------------------
    // SCOPES
    // -------------------
    public function scopeActive($query)
    {
        $today = now()->format('Y-m-d');
        return $query->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }

    public function scopeForPeriod($query, $period = 'monthly')
    {
        return $query->where('period', $period);
    }

    public function scopeWithNotifications($query)
    {
        return $query->where('notifications_enabled', true);
    }

    // -------------------
    // ACCESSORS
    // -------------------
    public function getSpentAmountAttribute(): float
    {
        if (!$this->category) {
            return 0;
        }

        return (float) $this->category->transactions()
            ->where('user_id', $this->user_id)
            ->where('type', 'expense')
            ->whereBetween('date', [$this->start_date, $this->end_date])
            ->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->amount - $this->spent_amount);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->amount <= 0) return 0;

        $percentage = ($this->spent_amount / $this->amount) * 100;
        return round($percentage, 2); // On peut dépasser 100 si dépenses > montant
    }

    public function getIsActiveAttribute(): bool
    {
        $today = now()->format('Y-m-d');
        return $this->start_date <= $today && $this->end_date >= $today;
    }

    public function getFormattedPeriodAttribute(): string
    {
        return match($this->period) {
            'monthly' => 'Mensuel',
            'weekly' => 'Hebdomadaire',
            'yearly' => 'Annuel',
            default => $this->period,
        };
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->progress_percentage > 100) return 'error';
        if ($this->progress_percentage >= $this->threshold_percentage) return 'warning';
        return 'success';
    }

    // -------------------
    // MUTATORS
    // -------------------
    public function setEndDateAttribute($value)
    {
        if (!$value && $this->period && $this->start_date) {
            $startDate = Carbon::parse($this->start_date);

            $value = match($this->period) {
                'weekly' => $startDate->copy()->addWeek()->subDay(),
                'monthly' => $startDate->copy()->addMonth()->subDay(),
                'yearly' => $startDate->copy()->addYear()->subDay(),
                default => $startDate->copy()->addMonth()->subDay(),
            };
        }

        $this->attributes['end_date'] = $value;
    }

    // -------------------
    // HELPERS
    // -------------------
    public function isExceeded(): bool
    {
        return $this->spent_amount >= $this->amount;
    }

    public function isNearThreshold(): bool
    {
        return $this->progress_percentage >= $this->threshold_percentage && !$this->isExceeded();
    }

    public function createNextPeriod(): ?self
    {
        if (!$this->is_active) return null;

        $nextBudget = $this->replicate();
        $nextBudget->start_date = Carbon::parse($this->end_date)->addDay();
        $nextBudget->end_date = null; // recalculé automatiquement
        $nextBudget->save();

        return $nextBudget;
    }
}
