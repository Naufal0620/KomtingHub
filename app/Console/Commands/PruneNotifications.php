<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune {--days=30 : Hapus notifikasi yang sudah dibaca lebih tua dari jumlah hari ini}';

    protected $description = 'Hapus notifikasi yang sudah dibaca dan lebih tua dari jumlah hari tertentu.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoff)
            ->delete();

        $this->info("Dihapus {$deleted} notifikasi yang sudah dibaca lebih tua dari {$days} hari.");

        return self::SUCCESS;
    }
}