<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sales', function (Blueprint $table) { $table->id(); $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); $table->string('invoice_no')->unique(); $table->date('sale_date'); $table->decimal('subtotal',15,2); $table->decimal('discount',15,2)->default(0); $table->decimal('tax',15,2)->default(0); $table->decimal('total',15,2); $table->decimal('paid_amount',15,2)->default(0); $table->decimal('due_amount',15,2)->default(0); $table->string('payment_method')->default('cash'); $table->string('status')->default('completed'); $table->text('notes')->nullable(); $table->foreignId('created_by')->constrained('users'); $table->timestamps(); });
        Schema::create('sale_items', function (Blueprint $table) { $table->id(); $table->foreignId('sale_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained(); $table->decimal('quantity',15,2); $table->decimal('unit_price',15,2); $table->decimal('unit_cost',15,2); $table->decimal('line_total',15,2); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('sale_items'); Schema::dropIfExists('sales'); }
};
