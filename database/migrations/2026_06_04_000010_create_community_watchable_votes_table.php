<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_watchable_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('community_watchable_id')->constrained('community_watchables')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['community_watchable_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_watchable_votes');
    }
};