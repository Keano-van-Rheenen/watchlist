<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_watchables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('uploader_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('hierarchy_index')->default(0);
            $table->string('kind');
            $table->string('title');
            $table->text('summary');
            $table->string('duration')->nullable();
            $table->unsignedInteger('episodes')->nullable();
            $table->binary('picture')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_watchables');
    }
};