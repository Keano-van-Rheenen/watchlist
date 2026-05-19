<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create movies table
        Schema::create('movies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('hierarchy_index');
            $table->string('title');
            $table->text('summary');
            $table->string('duration')->nullable();
            $table->binary('picture')->nullable();
            $table->timestamps();
        });

        // Create series table
        Schema::create('series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('hierarchy_index');
            $table->string('title');
            $table->text('summary');
            $table->integer('episodes')->nullable();
            $table->binary('picture')->nullable();
            $table->timestamps();
        });

        // Migrate data from watchable table
        if (Schema::hasTable('watchable')) {
            $watchables = DB::table('watchable')->get();
            
            foreach ($watchables as $watchable) {
                if ($watchable->type === 'movie') {
                    DB::table('movies')->insert([
                        'id' => $watchable->id,
                        'user_id' => $watchable->user_id,
                        'hierarchy_index' => $watchable->hierarchy_index,
                        'title' => $watchable->title,
                        'summary' => $watchable->summary,
                        'duration' => $watchable->duration,
                        'picture' => $watchable->picture,
                        'created_at' => $watchable->created_at,
                        'updated_at' => $watchable->updated_at,
                    ]);
                } else {
                    DB::table('series')->insert([
                        'id' => $watchable->id,
                        'user_id' => $watchable->user_id,
                        'hierarchy_index' => $watchable->hierarchy_index,
                        'title' => $watchable->title,
                        'summary' => $watchable->summary,
                        'episodes' => $watchable->episodes,
                        'picture' => $watchable->picture,
                        'created_at' => $watchable->created_at,
                        'updated_at' => $watchable->updated_at,
                    ]);
                }
            }
        }

        // Drop watchable table
        Schema::dropIfExists('watchable');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
        Schema::dropIfExists('series');
    }
};
