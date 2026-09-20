<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('purchases', function (Blueprint $table) { $table->id(); $table->foreignId('supplier_id')->constrained(); $table->string('invoice_no')->unique(); $table->date('purchase_date'); $table->decimal('subtotal',15,2); $table->decimal('discount',15,2)->default(0); $table->decimal('tax',15,2)->default(0); $table->decimal('total',15,2); $table->decimal('paid_amount',15,2)->default(0); $table->decimal('due_amount',15,2)->default(0); $table->string('status')->default('received'); $table->text('notes')->nullable(); $table->foreignId('created_by')->constrained('users'); $table->timestamps(); });
        Schema::create('purchase_items', function (Blueprint $table) { $table->id(); $table->foreignId('purchase_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained(); $table->decimal('quantity',15,2); $table->decimal('unit_cost',15,2); $table->decimal('line_total',15,2); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('purchase_items'); Schema::dropIfExists('purchases'); }
};
