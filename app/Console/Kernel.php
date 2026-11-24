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
        // Reminder masa berlaku lisensi (email Gmail + notifikasi lonceng). Jam bisa diubah via REMINDER_TIME di .env.
        $schedule->command('licenses:send-reminders')
            ->dailyAt(config('app.reminder_time'))
            ->timezone(config('app.timezone'))
            ->withoutOverlapping();
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
