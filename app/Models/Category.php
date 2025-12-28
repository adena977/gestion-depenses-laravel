<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
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
        'type',
        'color',
        'icon',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    // RELATIONS

    /**
     * Get the user that owns the category.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for the category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all budgets for the category.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    // SCOPES

    /**
     * Scope a query to only include expense categories.
     */
    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope a query to only include income categories.
     */
    public function scopeIncomes($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope a query to only include default categories.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include user categories (excluding defaults).
     */
    public function scopeUserCategories($query)
    {
        return $query->where('is_default', false);
    }

    // HELPERS

    /**
     * Get total amount spent in this category for current month.
     */
    public function getCurrentMonthTotal()
    {
        return $this->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    }

    /**
     * Get the icon class for display.
     */
    public function getIconClass()
    {
        return str_starts_with($this->icon, 'fa-') ? $this->icon : 'fa-' . $this->icon;
    }

    /**
     * Check if category can be deleted.
     */
    public function canBeDeleted()
    {
        return $this->transactions()->count() === 0 && $this->budgets()->count() === 0;
    }
    /**
 * Get monthly statistics for the category.
 */
public function getMonthlyStats($year = null, $month = null)
{
    $year = $year ?? now()->year;
    $month = $month ?? now()->month;
    
    return [
        'count' => $this->transactions()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->count(),
        'total' => $this->transactions()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount'),
        'average' => $this->transactions()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->avg('amount'),
    ];
}

/**
 * Get yearly statistics for the category.
 */
public function getYearlyStats($year = null)
{
    $year = $year ?? now()->year;
    
    $transactions = $this->transactions()
        ->whereYear('date', $year)
        ->get();
    
    return [
        'count' => $transactions->count(),
        'total' => $transactions->sum('amount'),
        'monthly_average' => $transactions->count() > 0 ? $transactions->sum('amount') / 12 : 0,
        'by_month' => $transactions->groupBy(function ($transaction) {
            return $transaction->date->format('m');
        })->map(function ($monthTransactions) {
            return $monthTransactions->sum('amount');
        }),
    ];
}
}