<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Freeze the membership at shuffle time so verification and display never
     * depend on today's (possibly changed) subject members.
     */
    public function up(): void
    {
        Schema::table('shuffle_runs', function (Blueprint $table) {
            $table->string('user_name')->nullable()->after('user_id');
            $table->json('member_snapshot')->nullable()->after('result');
        });

        // Backfill legacy rows from their recorded result so that past runs
        // (created before snapshots existed) can still be verified exactly.
        $runs = DB::table('shuffle_runs')->whereNull('member_snapshot')->get(['id', 'result']);

        foreach ($runs as $run) {
            $ids = $this->memberIdsFromResult(json_decode($run->result, true));

            DB::table('shuffle_runs')
                ->where('id', $run->id)
                ->update(['member_snapshot' => $ids === null ? null : json_encode($ids)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shuffle_runs', function (Blueprint $table) {
            $table->dropColumn(['member_snapshot', 'user_name']);
        });
    }

    /**
     * @return array<int, int>|null
     */
    private function memberIdsFromResult(?array $result): ?array
    {
        if ($result === null) {
            return null;
        }

        $ids = [];

        foreach ($result as $entry) {
            foreach ($entry['members'] ?? [] as $id) {
                $ids[(int) $id] = true;
            }
        }

        $sorted = array_keys($ids);
        sort($sorted);

        return $sorted;
    }
};