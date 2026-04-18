<?php

namespace App\Models;

use App\Enums\MemberLedgerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'created_by',
    ];

    protected $casts = [
        'reference_type' => MemberLedgerType::class,
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
