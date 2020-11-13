<?php

namespace App\Console\Commands;

use App\Http\Controllers\TranscoderController;
use Illuminate\Console\Command;

class pingTranscoder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcoder:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz na kontrolu dostupnosti transcodérů';

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
        TranscoderController::check_transcoder();
    }
}
