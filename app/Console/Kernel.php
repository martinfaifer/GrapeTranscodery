<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // kontrola zda streamy funguje
        $schedule->command('stream:checkIfAllStreamsRunning')->everyMinute()->runInBackground();
        // pokus spustit stream, který nefunguje
        $schedule->command('stream:tryStartIssuedStream')->everyMinute()->runInBackground();
        // kontrola transcodérů
        $schedule->command('transcoder:ping')->everyMinute()->runInBackground();
        // kontrola streamů, zda nejsou OutOfSync
        $schedule->command('stream:check')->everyFifteenMinutes()->runInBackground()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
