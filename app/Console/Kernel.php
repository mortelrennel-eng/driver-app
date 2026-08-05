<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Auto-offline heartbeat tracker: Runs every minute
        $schedule->call(function () {
            // Find users who are marked online but haven't been seen for more than 2 minutes
            $offlineUsers = \App\Models\User::where('is_online', true)
                ->where('last_seen_at', '<', now()->subMinutes(2))
                ->get();
                
            foreach ($offlineUsers as $u) {
                $u->update(['is_online' => false]);
                \App\Models\LoginAudit::log('logout', $u, 'Auto-offline: Session timed out or browser closed.');
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
