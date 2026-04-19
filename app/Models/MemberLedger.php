<?php

namespace App\Models;

use App\Enums\MemberLedgerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class MemberLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'reference_type' => MemberLedgerType::class,
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (MemberLedger $ledger): void {
            $ledger->created_by = Auth::id();
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
