<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('settings', function (Blueprint $table) { $table->id(); $table->string('company_name')->default('TAJ Limited'); $table->string('company_email')->nullable(); $table->string('company_phone')->nullable(); $table->text('company_address')->nullable(); $table->string('currency_symbol',10)->default('৳'); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('settings'); }
};
