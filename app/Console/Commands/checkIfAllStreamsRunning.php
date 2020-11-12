<?php

namespace App\Console\Commands;

use App\Http\Controllers\TranscoderController;
use Illuminate\Console\Command;

class checkIfAllStreamsRunning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:checkIfAllStreamsRunning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vyhledání, zda všechny streamy fungují jak mají';

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
        TranscoderController::check_if_streams_running();
    }
}
