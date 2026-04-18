<?php

use App\Models\Member;
use App\Models\Sale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Sale::class)->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(Member::class)->nullable()->constrained()->restrictOnDelete();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->decimal('amount_tendered', 12, 2)->default(0);
            $table->decimal('change_given', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
