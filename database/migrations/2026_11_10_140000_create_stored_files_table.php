<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stored_files')) {
            Schema::create('stored_files', function (Blueprint $table) {
                $table->id();
                $table->string('path', 500)->unique()->index();
                $table->string('mime_type', 100)->default('image/png');
                $table->longText('content'); // Base64 encoded file payload
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};

