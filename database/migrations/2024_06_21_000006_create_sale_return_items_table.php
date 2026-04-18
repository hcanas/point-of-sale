<?php

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_return_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(SaleReturn::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(SaleItem::class)->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(Product::class)->nullable()->constrained()->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
