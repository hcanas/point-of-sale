<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'name',
        'description',
        'stock',
        'stock_warning_level',
        'price',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            $product->created_by = Auth::id();
            $product->stock ??= 0;
        });

        static::updating(function (Product $product): void {
            $product->updated_by = Auth::id();
        });
    }

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'stock_warning_level' => 'integer',
        'is_active' => 'boolean',
    ];

    protected function deletable(): Attribute
    {
        return Attribute::get(fn () => ! $this->stockMovements()->exists());
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
