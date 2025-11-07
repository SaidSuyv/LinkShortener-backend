<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Link;

class CleanExpiredLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes the expired links';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now('UTC');

        $this->info("Ahora (UTC) -> " . $today->toDateTimeString());

        // Buscar registros vencidos
        $expiredRecords = Link::whereDate('expiration_at', '<=', $today)->get();

        if ($expiredRecords->isEmpty()) {
            $this->info("✅ No hay registros expirados para eliminar hoy ({$today->toDateString()}).");
            return 0;
        }

        // Guardar log antes de eliminar
        $logPath = storage_path('logs/cleaned-records.log');
        $logEntries = "=== Limpieza ejecutada: {$today->toDateTimeString()} ===\n";

        foreach ($expiredRecords as $record) {
            $logEntries .= json_encode($record->toArray(), JSON_UNESCAPED_UNICODE) . "\n";
        }

        file_put_contents($logPath, $logEntries, FILE_APPEND);

        // Eliminar los registros
        $count = Link::whereDate('expiration_at', '<=', $today)->delete();

        $this->info("🧹 {$count} registros expirados fueron eliminados y logueados en cleaned-records.log");

        return 0;
    }
}
