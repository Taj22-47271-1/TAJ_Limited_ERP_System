<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('accounts', function (Blueprint $table) { $table->id(); $table->string('code')->unique(); $table->string('name'); $table->string('type'); $table->boolean('is_system')->default(false); $table->timestamps(); });
        Schema::create('journal_entries', function (Blueprint $table) { $table->id(); $table->date('entry_date'); $table->string('reference_type')->nullable(); $table->unsignedBigInteger('reference_id')->nullable(); $table->string('memo')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['reference_type','reference_id']); });
        Schema::create('journal_lines', function (Blueprint $table) { $table->id(); $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete(); $table->foreignId('account_id')->constrained(); $table->decimal('debit',15,2)->default(0); $table->decimal('credit',15,2)->default(0); $table->string('description')->nullable(); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('journal_lines'); Schema::dropIfExists('journal_entries'); Schema::dropIfExists('accounts'); }
};
