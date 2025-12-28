<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'type',
        'description',
        'date',
        'receipt_path',
        'location',
        'payment_method',
        'is_recurring',
        'recurring_frequency',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'is_recurring' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['formatted_amount', 'formatted_date'];

    // RELATIONS

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // SCOPES

    /**
     * Scope a query to only include expenses.
     */
    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope a query to only include income.
     */
    public function scopeIncomes($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope a query to only include transactions for a specific period.
     */
    public function scopeForPeriod($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include transactions for current month.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
    }

    /**
     * Scope a query to order by date descending.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
    }

    // ACCESSORS

    /**
     * Get formatted amount with sign.
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === 'expense' ? '-' : '+';
        return $sign . number_format($this->amount, 2, ',', ' ') . ' €';
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d/m/Y');
    }

    /**
     * Get color based on transaction type.
     */
    public function getAmountColorAttribute(): string
    {
        return $this->type === 'expense' ? 'text-error' : 'text-success';
    }

    /**
     * Get icon based on category or type.
     */
    public function getDisplayIconAttribute(): string
    {
        return $this->category->icon ?? ($this->type === 'expense' ? 'fa-arrow-down' : 'fa-arrow-up');
    }

    // MUTATORS

    /**
     * Set the date attribute.
     */
    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }

    // HELPERS

    /**
     * Check if transaction is recurring.
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring && $this->recurring_frequency;
    }

    /**
     * Get next occurrence date for recurring transaction.
     */
    public function getNextOccurrenceDate(): ?Carbon
    {
        if (!$this->isRecurring()) {
            return null;
        }

        $date = $this->date;
        $now = now();

        while ($date <= $now) {
            switch ($this->recurring_frequency) {
                case 'daily':
                    $date = $date->addDay();
                    break;
                case 'weekly':
                    $date = $date->addWeek();
                    break;
                case 'monthly':
                    $date = $date->addMonth();
                    break;
                case 'yearly':
                    $date = $date->addYear();
                    break;
            }
        }

        return $date;
    }
}