<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use Illuminate\Console\Command;

class tryStartIssuedStream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:tryStartIssuedStream';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz o pokus spustit stream, který je ve stavu issue';

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
        StreamController::try_start_stream_with_issue();
    }
}
