<?php

namespace App\Models;

use App\Traits\HasPersonName;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Member extends Model
{
    use HasFactory, HasPersonName;

    protected $appends = ['full_name', 'formal_name'];

    protected $fillable = [
        'phone',
        'address',
        'is_active',
        'balance',
        'share_capital',
        'tin_number',
    ];

    protected static function booted(): void
    {
        static::creating(function (Member $member): void {
            $member->created_by = Auth::id();
        });

        static::updating(function (Member $member): void {
            $member->updated_by = Auth::id();
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'balance' => 'decimal:2',
            'share_capital' => 'decimal:2',
        ];
    }

    protected function deletable(): Attribute
    {
        return Attribute::get(fn () => ! $this->sales()->exists() && ! $this->ledgers()->exists() && ! $this->payments()->exists() && ! $this->returns()->exists());
    }

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
