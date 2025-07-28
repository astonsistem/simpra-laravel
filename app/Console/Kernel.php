<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('cron:per-day')->everyMinute();//CEK AKHIR - GANTI NANTI MENJADI TIAP HARI DAN PISAHKAN CRON YANG BEDA JADWAL, DIMANA INI SEMENTARA DIJADIKAN SATU CRON:PER-DAY
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
