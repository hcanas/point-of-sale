<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'member_id',
        'member_name',
        'member_short_name',
        'total_amount',
        'amount_paid',
        'amount_tendered',
        'change_given',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_tendered' => 'decimal:2',
        'change_given' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->amount_paid <= 0) {
                    return 'unpaid';
                }
                if ($this->amount_paid < $this->total_amount) {
                    return 'partially_paid';
                }

                return 'completed';
            }
        );
    }
}
