<?php

namespace App\Models;

use App\Traits\HasPersonName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;
    use HasPersonName;

    protected $fillable = [
        'phone',
        'address',
        'is_active',
        'credit_limit',
        'balance',
        'share_capital',
        'tin_number',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
        'share_capital' => 'decimal:2',
    ];

    public function ledgers(): HasMany
    {
        return $this->hasMany(MemberLedger::class)->orderBy('created_at', 'desc');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class)->orderBy('created_at', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('created_at', 'desc');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class)->orderBy('created_at', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
