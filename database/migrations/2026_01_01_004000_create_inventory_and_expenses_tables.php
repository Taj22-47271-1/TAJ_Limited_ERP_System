<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) { $table->id(); $table->foreignId('product_id')->constrained(); $table->string('movement_type'); $table->string('reference_type')->nullable(); $table->unsignedBigInteger('reference_id')->nullable(); $table->decimal('quantity_in',15,2)->default(0); $table->decimal('quantity_out',15,2)->default(0); $table->decimal('balance_after',15,2); $table->decimal('unit_cost',15,2)->default(0); $table->string('note')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['reference_type','reference_id']); });
        Schema::create('expenses', function (Blueprint $table) { $table->id(); $table->date('expense_date'); $table->string('category'); $table->string('description'); $table->decimal('amount',15,2); $table->string('payment_method')->default('cash'); $table->string('reference')->nullable(); $table->foreignId('created_by')->constrained('users'); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('expenses'); Schema::dropIfExists('stock_movements'); }
};
