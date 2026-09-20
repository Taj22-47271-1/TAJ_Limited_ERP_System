<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->text('description')->nullable(); $table->boolean('status')->default(true); $table->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('company')->nullable(); $table->string('phone')->nullable(); $table->string('email')->nullable(); $table->text('address')->nullable();
            $table->decimal('opening_balance',15,2)->default(0); $table->decimal('credit_limit',15,2)->default(0); $table->boolean('status')->default(true); $table->timestamps();
        });
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('name'); $table->string('phone')->nullable(); $table->string('email')->nullable(); $table->text('address')->nullable();
            $table->string('customer_type')->default('retail'); $table->decimal('opening_balance',15,2)->default(0); $table->decimal('credit_limit',15,2)->default(0); $table->boolean('status')->default(true); $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); $table->string('sku')->unique(); $table->string('barcode')->nullable()->unique(); $table->string('unit')->default('pcs');
            $table->decimal('purchase_price',15,2)->default(0); $table->decimal('selling_price',15,2)->default(0);
            $table->decimal('stock',15,2)->default(0); $table->decimal('reserved_stock',15,2)->default(0); $table->decimal('min_stock',15,2)->default(5);
            $table->string('image_path')->nullable(); $table->boolean('status')->default(true); $table->text('description')->nullable(); $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('products'); Schema::dropIfExists('customers'); Schema::dropIfExists('suppliers'); Schema::dropIfExists('categories');
    }
};
