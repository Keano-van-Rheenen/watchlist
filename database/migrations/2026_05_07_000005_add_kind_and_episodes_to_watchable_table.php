<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('watchable', 'type')) {
            Schema::table('watchable', function (Blueprint $table) {
                $table->string('type')->default('movie')->after('hierarchy_index');
                $table->unsignedInteger('episodes')->nullable()->after('duration');
                $table->string('duration')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('watchable', 'type') || Schema::hasColumn('watchable', 'episodes')) {
            Schema::table('watchable', function (Blueprint $table) {
                if (Schema::hasColumn('watchable', 'episodes')) {
                    $table->dropColumn('episodes');
                }

                if (Schema::hasColumn('watchable', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }
    }
};
