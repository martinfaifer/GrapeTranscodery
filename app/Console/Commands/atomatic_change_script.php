<?php

namespace App\Console\Commands;

use App\Http\Controllers\FFmpegScriptController;
use Illuminate\Console\Command;

class atomatic_change_script extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'script:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatická úprava scriptů pro ffmpeg s následným restartem';

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
        FFmpegScriptController::upgrade_script();
    }
}
