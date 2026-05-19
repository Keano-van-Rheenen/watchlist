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
        Schema::table('watchable', function (Blueprint $table) {
            $table->unsignedInteger('hierarchy_index')->default(0)->after('user_id');
        });

        $groupedIds = DB::table('watchable')
            ->select(['id', 'user_id'])
            ->orderBy('user_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');

        foreach ($groupedIds as $ids) {
            $index = 1;

            foreach ($ids as $row) {
                DB::table('watchable')
                    ->where('id', $row->id)
                    ->update(['hierarchy_index' => $index]);

                $index++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watchable', function (Blueprint $table) {
            $table->dropColumn('hierarchy_index');
        });
    }
};
