<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('order_no')->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 30);
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal',15,2);
            $table->decimal('delivery_fee',15,2)->default(0);
            $table->decimal('total',15,2);
            $table->string('payment_method')->default('cod'); // cod|online
            $table->string('payment_channel')->nullable(); // card|bkash|nagad (demo/sandbox)
            $table->string('payment_status')->default('unpaid'); // unpaid|pending|paid|refunded
            $table->string('payment_reference')->nullable();
            $table->string('order_status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('stock_committed_at')->nullable();
            $table->timestamps();
            $table->index(['order_status','created_at']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('online_order_id')->nullable()->unique()->constrained('online_orders')->nullOnDelete();
        });

        Schema::create('online_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name');
            $table->decimal('quantity',15,2);
            $table->decimal('unit_price',15,2);
            $table->decimal('unit_cost',15,2);
            $table->decimal('line_total',15,2);
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::table('sales', function (Blueprint $table) { $table->dropConstrainedForeignId('online_order_id'); });
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('online_order_items');
        Schema::dropIfExists('online_orders');
    }
};
