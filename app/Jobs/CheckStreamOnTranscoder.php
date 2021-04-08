<?php

namespace App\Jobs;

use App\Http\Controllers\MailController;
use App\Models\Stream;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckStreamOnTranscoder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transcoder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $transcoder)
    {
        $this->transcoder = $transcoder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::get('http://' . $this->transcoder->ip . '/tcontrol.php?CMD=GETPIDS&LOCK=FALSE');
        $response = json_decode($response, true);

        if ($response["STATUS"] == "TRUE") {

            if (!is_null($response["PIDS"])) {

                $pidsFromTranscodersString = implode(" ", $response["PIDS"]);

                Stream::where([
                    ['status', "active"],
                    ['transcoder', $this->transcoder->id]
                ])->each(function ($stream) use ($pidsFromTranscodersString) {

                    if (!Str::contains($pidsFromTranscodersString, $stream->pid)) {
                        if ($stream->status != "issue") {
                            Stream::where('id', $stream->id)->update(['status' => "issue"]);
                            // MailController::send_error_stream($stream->nazev);

                        }
                    }
                });
            }
        }
    }
}
