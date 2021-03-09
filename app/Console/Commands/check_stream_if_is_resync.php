<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamMonitorController;
use Illuminate\Console\Command;

class check_stream_if_is_resync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ověření, zda stream není outOfSync (posunutý zvuk oproti videu)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        StreamMonitorController::check_if_stream_video_and_audio_are_resync();
    }
}
