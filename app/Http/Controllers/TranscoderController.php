<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\transcoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TranscoderController extends Controller
{
    public static function get_transcoders()
    {
        if (!transcoder::first()) {
            return [
                'status' => "empty"
            ];
        }

        foreach (transcoder::get() as $transcoder) {
            $output[] = array(
                'id' => $transcoder->id,
                'name' => $transcoder->name,
                'ip' => $transcoder->ip,
                'status' => $transcoder->status,
                'streamCount' => StreamController::count_streams_on_transcoder($transcoder->id)
            );
        }

        return [
            'status' => "success",
            'data' => $output
        ];
    }


    /**
     * funkce na připojení do transcodéru a získání informací
     *
     * @param Request $request->transcoderIp
     * @return void
     */
    public static  function transcoder_hardware_usage(Request $request)
    {

        try {
            return $response = Http::get('http://' . $request->transcoderIp . '/tcontrol.php?CMD=NVSTATS');
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se připojit k Transcodéru"
            ];
        }
    }



    /**
     * funknce na zastavení již funknčího streamu a následné vrácení stavu do frontendu zda se povedlo ci nikoliv
     *
     * @param Request $request-> streamPid , streamId, transcoderIp , cmd
     * @return array
     */
    public static function stop_running_stream(Request $request)
    {

        return [
            'status' => "error",
            'msg' => "Nepodařilo se zastavit stream"
        ];
        try {
            $response = Http::post('http://' . $request->transcoderIp . '/tcontrol.php', [
                'PID' => $request->streamPid,
                'CMD' => $request->cmd
            ]);

            //
            //
            //
            dd($response);
            //
            //
            //

        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se zastavit stream"
            ];
        }
    }


    /**
     * funknce na spustení streamu na pozadovnaem treanscoderu
     *
     * @param Request $response->streamId , transcoderIp , cmd
     * @return void
     */
    public static function start_stream(Request $request)
    {

        // vyhledání stavu transcodéru, zda je dostatek volné paměti
        $stavTranscoderu = self::check_if_is_enough_memory_for_start_stream($request->transcoderIp);
        if ($stavTranscoderu['status'] == "noRam") {
            return [
                'status' => "error",
                'msg' => "Není dostatek RAM pro spuštění streamu"
            ];
        }


        // vyhledání skriptu pro spustení u streamu
        if (!Stream::where('id', $request->streamId)->first()) {
            return [
                'status' => "error",
                'msg' => "Stream neexistuje"
            ];
        }

        try {
            $response = Http::get('http://' . $request->transcoderIp . '/tcontrol.php', [
                'FFMPEG' => base64_encode(Stream::where('id', $request->streamId)->first()->script),
                'CMD' => $request->cmd
            ]);
            //
            //
            //
            return $response;
            //
            //
            //
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se spustit stream"
            ];
        }
    }



    /**
     * funknce, kde se zjistuje, zda je dostatek volné pameti ram
     *
     * pokud je méne nez 750, stream se nepodaří spustit
     *
     * @param string $transcoderIp
     * @return array
     */
    public static function check_if_is_enough_memory_for_start_stream(string $transcoderIp): array
    {

        try {
            $response = Http::get('http://' . $transcoderIp . '/tcontrol.php?CMD=NVSTATS');
            $response = json_decode($response, true);
            foreach ($response as $data) {
                $free = $data["fb_memory_usage"]["free"];
                $freeRam = str_replace(" MiB", "", $free);
                if (str_replace(" MiB", "", $free) < "750") {
                    return [
                        'status' => "noRam"
                    ];
                } else {
                    return [
                        'status' => "ramOk"
                    ];
                }
            }
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se připojit k Transcodéru"
            ];
        }
    }


    /**
     * funknce na analýzu streamu a následné vrácení ffproby ve formátu json
     *
     * @param Request $request transcoderId , stream_src, cmd
     * @return array
     */
    public static function stream_analyse(Request $request)
    {

        if ($transcoder = transcoder::where('id', $request->transcoderId)->first()) {


            // try {
            $response = Http::get('http://' . $transcoder->ip . '/tcontrol.php', [
                'CMD' => $request->cmd,
                'LOCK' => "FALSE",
                'SRC' => $request->stream_src
            ]);

            $response = json_decode($response, true);
            // return $response;
            if ($response["STATUS"] === "TRUE") {


                // status success => vyhledání streamů

                return self::create_ffprobe_output_for_frontend($response);
            } else {
            }
            // } catch (\Throwable $th) {
            //     return [
            //         'status' => "error",
            //         'msg' => "Nepodařilo se provést analýzu"
            //     ];
            // }
        } else {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se připojit k transcodéru"
            ];
        }
    }


    public static function create_ffprobe_output_for_frontend(array $ffprobe)
    {
        $outputVideo = array();
        $outputAudio = array();

        if (!array_key_exists("streams", $ffprobe["PROBE"])) {
            return [
                'status' => "error",
                'msg' => "Selhalo vytvoření výstupu"
            ];
        }

        foreach ($ffprobe["PROBE"]["streams"] as $streamData) {
            if (array_key_exists("codec_type", $streamData)) {
                if ($streamData["codec_type"] == "video") {
                    // return $streamData["index"];
                    $outputVideo[] = array(
                        'index' =>  $streamData["index"] ?? null,
                        'codec_name' => $streamData["codec_name"] ?? null,
                        'codec_type' => $streamData["codec_type"] ?? null
                    );
                }


                if ($streamData["codec_type"] == "audio") {
                    $outputAudio[] = array(
                        'index' => $streamData["index"] ?? null,
                        'popis' => $streamData["codec_name"] . (" / " . $streamData["tags"]["language"] ?? "nepodarilo se detekovat audio"),
                        'codec_name' => $streamData["codec_name"] ?? null,
                        'codec_type' => $streamData["codec_type"] ?? null,
                        'lang' => $streamData["tags"]["language"] ?? null
                    );
                }
            }
        }

        return [
            'status' => "success",
            'video' => $outputVideo,
            'audio' => $outputAudio
        ];
    }
}
