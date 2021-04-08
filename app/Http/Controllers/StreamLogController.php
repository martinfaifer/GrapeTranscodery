<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamLog;

class StreamLogController extends Controller
{

    public $output;

    public static function store(string $stream_id, string $status, $payload = null): void
    {
        StreamLog::create([
            'stream_id' => $stream_id,
            'status' => $status,
            'payload' => $payload
        ]);
    }


    public function show_last_twentyfour_hours_log(): array
    {
        return (!StreamLog::where('status', "reboot")->where('created_at', '<', now()->subDay())->first())
            ? []
            : $this->count(3);
    }


    public function count(int $number): array
    {
        foreach (Stream::all() as $stream) {
            if (StreamLog::where('stream_id', $stream->id)->where('status', "reboot")->where('created_at', '>', now()->subDay())->first()) {
                if ($streamLog = StreamLog::where('stream_id', $stream->id)->where('status', "reboot")->where('created_at', '>', now()->subDay())->get()->count() >= $number) {
                    // streamLog -> pocet restartu

                    // počet restartů
                    $this->output[] = array(
                        'stream' => $stream->nazev,
                        'stream_dst' => $stream->dst,
                        'pocet_restartu' => StreamLog::where('stream_id', $stream->id)->where('status', "reboot")->where('created_at', '>', now()->subDay())->get()->count()
                    );
                }
            }
        }

        return $this->output;
    }

    public function index(): array
    {
        return (!StreamLog::where('status', "reboot")->where('created_at', '<', now()->subDay())->first())
            ? []
            : $this->count(0);
    }
}
