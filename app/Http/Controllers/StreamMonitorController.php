<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\transcoder;
use Illuminate\Support\Facades\Http;

class StreamMonitorController extends Controller
{
    /**
     * main function
     *
     * @return void
     */
    public static function check_if_stream_video_and_audio_are_resync(): void
    {

        // soupis streamů co aktuálně jsou transcodovany na danem transcoderu

        // napojeni na transcoder , parametry CMD = CHECKSTREAM a SRC streamUrl
        $transcoder = self::get_transcoder_and_return_ip_and_id();
        if ($transcoder['status'] === "error") {
            exit();
        }

        $streamsOnTranscoder = self::show_streams_belongsTo_this($transcoder['id']);

        if (!empty($streamsOnTranscoder)) {

            foreach ($streamsOnTranscoder as $stream) {

                $ffprobeOutput = self::connect($transcoder['ip'], $stream['url']);

                if (!$ffprobeOutput === "error") {

                    // ffprobe výstup
                    $analyzeResult = self::analyze(json_decode($ffprobeOutput, true));
                    if ($analyzeResult['status'] === 'restart') {

                        // případný restart streamu   
                        self::reboot($stream['pid'], $stream['id'], $transcoder['id']);
                    }
                }
            }
        }
    }


    /**
     * získání transcoderu id a ip
     *
     * @return array
     */
    public static function get_transcoder_and_return_ip_and_id(): array
    {
        if (!transcoder::first()) {
            return ['status' => "error"];
        }

        foreach (transcoder::all() as $transcoder) {
            if ($transcoder->status === 'success') {
                return [
                    'status' => "success",
                    'id' => $transcoder->id,
                    'ip' => $transcoder->ip
                ];
            }
        }
    }


    /**
     * vypsání streamů co jsou na transcoderu
     *
     * @param string $transcoderId
     * @return array
     */
    public static function show_streams_belongsTo_this(string $transcoderId): array
    {
        if (!Stream::where('transcoder', $transcoderId)->first()) {
            return [];
        }

        foreach (Stream::where('transcoder', $transcoderId)->get() as $stream) {
            $streams[] = array(
                'id' => $stream->id,
                'pid' => $stream->pid,
                'url' => $stream->dst
            );
        }

        return $streams;
    }

    /**
     * napojení na GQL transcoderů a získání ffproby
     *
     * @param string $transcoderIp
     * @param string $streamUrl
     * @return void
     */
    public static function connect(string $transcoderIp, string $streamUrl)
    {
        try {
            $response = Http::get('http://' . $transcoderIp . '/tcontrol.php', [
                'SRC' => $streamUrl,
                'CMD' => "CHECKSTREAM"
            ]);

            $response = json_decode($response, true);
            if ($response["STATUS"] === "TRUE") {
                return $response['StreamCheck'];
            }
            return "error";
        } catch (\Throwable $th) {
            return "error";
        }
    }


    /**
     * rozebrání ffproby a zsjiětení zda stream má posunuté audio nebo video
     *
     * @param array $ffprobe
     * @return boolean
     */
    public static function analyze(array $ffprobe): array
    {
        foreach ($ffprobe["programs"] as $program) {

            if (array_key_exists("start_time", $program)) {
                $program_start_time = round($program["start_time"], 0);
            }
        }

        foreach ($ffprobe["programs"][0]["streams"] as $streams) {

            if (array_key_exists("codec_type", $streams)) {
                if ($streams["codec_type"] == "video") {

                    if (array_key_exists("start_time", $streams)) {
                        $video_start_time = round($streams["start_time"], 0);
                    }
                }

                if ($streams["codec_type"] == "audio") {

                    if (array_key_exists("start_time", $streams)) {
                        $audio_start_time = round($streams["start_time"], 0);
                    }
                }
            }
        }

        if (isset($program_start_time) && isset($video_start_time) && isset($audio_start_time)) {

            if ($program_start_time == $video_start_time && $program_start_time == $audio_start_time) {
                // hodnoty jsou totozne, stream se nebude restartovat
                return ['status' => "stream_ok"];
            }

            $checkPrimarToVideo = intval($video_start_time) - intval($program_start_time);
            $checkPrimarToAudio = intval($audio_start_time) - intval($program_start_time);

            if ($checkPrimarToVideo <= 1 &&  $checkPrimarToAudio <= 1) {
                // rozdíl hodnot je mensi nebo roven 1 , je to tedy v normě a nebude se stream restartovat
                return ['status' => "stream_ok"];
            }

            // stream má nejspíše posunutý zvuk oproti videu, pro jistotu jej restartujeme
            return ['status' => "restart"];
        }

        // neexistují hodnoty, nebudeme restartovat pro jistotu
        return ['status' => "stream_ok"];
    }

    /**
     * restart streamu, zavoláním funkce v TranscoderController
     *
     * @param string $streamPid
     * @param string $streamId
     * @param string $transcoderId
     * @return void
     */
    public static function reboot(string $streamPid, string $streamId, string $transcoderId)
    {
        TranscoderController::restart_running_stream_by_api($streamPid, $streamId, $transcoderId);
    }
}
