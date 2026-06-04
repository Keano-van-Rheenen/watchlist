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
        if (! Schema::hasColumn('movies', 'seen')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->boolean('seen')->default(false)->after('picture');
            });

            DB::table('movies')->update(['seen' => false]);
        }

        if (! Schema::hasColumn('series', 'seen')) {
            Schema::table('series', function (Blueprint $table) {
                $table->boolean('seen')->default(false)->after('picture');
            });

            DB::table('series')->update(['seen' => false]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('movies', 'seen')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropColumn('seen');
            });
        }

        if (Schema::hasColumn('series', 'seen')) {
            Schema::table('series', function (Blueprint $table) {
                $table->dropColumn('seen');
            });
        }
    }
};