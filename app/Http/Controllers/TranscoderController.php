<?php

namespace App\Http\Controllers;

use App\Jobs\CheckStreamOnTranscoder;
use App\Models\Stream;
use App\Models\transcoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JJG\Ping;

class TranscoderController extends Controller
{
    /**
     * základní výpis transcoderů
     *
     * @return array
     */
    public function get_transcoders()
    {
        if (!transcoder::first()) {
            return [
                'status' => "empty"
            ];
        }

        foreach (transcoder::all()->sortBy('name') as $transcoder) {
            $output[] = array(
                'id' => $transcoder->id,
                'name' => $transcoder->name,
                'ip' => $transcoder->ip,
                'status' => $transcoder->status,
                // 'streamCount' => StreamController::count_streams_on_transcoder($transcoder->id)
            );
        }

        return [
            'status' => "success",
            'data' => $output
        ];
    }

    public function transcoder_status(Request $request): string
    {
        if ($transcoder = transcoder::where('ip', $request->ip)->first()) {
            return $transcoder->status;
        }

        return "offline";
    }

    /**
     * výpis transcodérů + hw vyuzití ( náhled )
     *
     * @return array
     */
    public function transcoders_and_telemetrie()
    {
        if (!transcoder::first()) {
            return [
                'status' => "empty"
            ];
        }

        foreach (transcoder::all()->sortBy('name') as $transcoder) {
            $output[] = array(
                'id' => $transcoder->id,
                'name' => $transcoder->name,
                'ip' => $transcoder->ip,
                'status' => $transcoder->status,
                'streamCount' => StreamController::count_streams_on_transcoder($transcoder->id),
                'streamIssueCount' => StreamController::count_issue_streams_on_transcoder($transcoder->id),
                'telemetrie' => $this->telemetrie(trim($transcoder->ip), $transcoder->status)
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
            return $response = Http::timeout(3)->get('http://' . $request->transcoderIp . '/tcontrol.php?CMD=NVSTATS');
        } catch (\Throwable $th) {
            return NotificationController::notify("error", "error", "Nepodařilo se připojit k Transcodéru");
        }
    }

    /**
     * fn pro výpis hw využití 
     *
     * @param string $transcoderIp, status
     * @return array
     */
    public static function telemetrie($transcoderIp, string $status)
    {
        if ($status == 'success') {
            try {
                $response = Http::timeout(3)->get('http://' . $transcoderIp . '/tcontrol.php?CMD=NVSTATS');
                return json_decode($response, true);
            } catch (\Throwable $th) {
                return [];
            }
        } else {
            return [];
        }
    }



    /**
     * funknce na zastavení již funknčího streamu a následné vrácení stavu do frontendu zda se povedlo ci nikoliv
     *
     * @param Request $request-> streamPid , streamId, transcoderIp , cmd
     * @return array
     */
    public static function stop_running_stream(Request $request): array
    {
        try {
            $response = Http::get('http://' . $request->transcoderIp . '/tcontrol.php', [
                'PID' => $request->streamPid,
                'CMD' => $request->cmd
            ]);

            $response = json_decode($response, true);
            if ($response["STATUS"] === "TRUE") {
                // zmena pidu na null a statusu na STOP
                Stream::where('id', $request->streamId)->update(['pid' => null, 'status' => "STOP"]);

                return NotificationController::notify("success", "success", "Stream zastaven");
            } else {
                // nepodarilo se zastavit ffmpeg
                return NotificationController::notify("error", "error", "Stream se nepodařilo zastavit!");
            }
            // odchytnutí error 500
        } catch (\Throwable $th) {
            return NotificationController::notify();
        }
    }

    /**
     * restart streamu na transcoderu
     *
     * @param [type] $streamPid
     * @param [type] $streamId
     * @param [type] $transcoderId
     * @return void
     */
    public static function restart_running_stream_by_api($streamPid, $streamId, $transcoderId)
    {

        $transcoderIp = transcoder::find($transcoderId)->ip;
        $stream = Stream::find($streamId);

        if ($stream->status === 'active') {

            // pouze aktivní streamu mohou být restartovány 

            try {
                $response = Http::get('http://' . $transcoderIp . '/tcontrol.php', [
                    'PID' => $streamPid,
                    'CMD' => "KILL"
                ]);
                $response = json_decode($response, true);
                if ($response["STATUS"] === "TRUE") {

                    // zmena pidu na null a statusu na STOP
                    $stream->update(['pid' => null, 'status' => "STOP"]);

                    // zavolání startu streamu
                    try {
                        $response = Http::get('http://' . $transcoderIp . '/tcontrol.php', [
                            'FFMPEG' => base64_encode($stream->script),
                            'CMD' => "START"
                        ]);
                        $response = json_decode($response, true);
                        if ($response["STATUS"] === "TRUE") {
                            // vyhledání pidu a aktualizace záznamu
                            $stream->update(['pid' => $response["PID"], 'status' => "active"]);

                            // stream se restartoval
                            return [
                                'status' => "success",
                                'msg' => "Restartovano"
                            ];
                        } else {
                            // stream se nepodařilo spustit
                            return [
                                'status' => "error",
                                'msg' => "Nepodařilo se spustit stream"
                            ];
                        }
                    } catch (\Throwable $th) {
                        // error 500
                        return [
                            'status' => "error",
                            'msg' => "Selhala komunikace s transcoderem pro Startu"
                        ];
                    }
                } else {
                    // nepodarilo se zastavit ffmpeg
                    return [
                        'status' => "error",
                        'msg' => "Stream se nepodařilo zastavit"
                    ];
                }
            } catch (\Throwable $th) {
                // error 500
                return [
                    'status' => "error",
                    'msg' => "Selhala komunikace s transcoderem po KILLu"
                ];
            }
        } else {
            return [
                'status' => "error",
                'msg' => "Stream nemohl být restartován jelikož nebyl ve stavu active"
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

        if ($stavTranscoderu['status'] === "offline") {
            return NotificationController::notify("error", "error", "Transcoder neodpovídá na PING!");
        }

        if ($stavTranscoderu['status'] == "noRam") {
            return NotificationController::notify("error", "error", "Není dostatek RAM pro spuštění streamu!");
        }


        // vyhledání skriptu pro spustení u streamu
        if (!Stream::where('id', $request->streamId)->first()) {
            return NotificationController::notify("error", "error", "Stream neexistuje!");
        }

        try {
            $response = Http::get('http://' . $request->transcoderIp . '/tcontrol.php', [
                'FFMPEG' => base64_encode(Stream::where('id', $request->streamId)->first()->script),
                'CMD' => $request->cmd
            ]);

            $response = json_decode($response, true);
            if ($response["STATUS"] === "TRUE") {
                // vyhledání pidu a aktualizace záznamu
                Stream::where('id', $request->streamId)->update(['pid' => $response["PID"], 'status' => "active"]);

                return NotificationController::notify("success", "success", "Stream spuštěn!");
            } else {
                // nepodarilo se spustit ffmpeg
                return NotificationController::notify("error", "error", "Stream se nepodařilo spustit!");
            }
            // odchytnutí error 500 , aby funkce nespadla
        } catch (\Throwable $th) {
            return NotificationController::notify();
        }
    }

    /**
     * fn pro automaticke spusteni streamu
     *
     * @param string $transcoderIp
     * @param string $streamId
     * @return void
     */
    public static function start_stream_from_backend($transcoderIp,  $streamId): void
    {

        try {
            $response = Http::get('http://' . $transcoderIp . '/tcontrol.php', [
                'FFMPEG' => base64_encode(Stream::where('id', $streamId)->first()->script),
                'CMD' => "START"
            ]);

            $response = json_decode($response, true);
            if ($response["STATUS"] === "TRUE") {
                // vyhledání pidu a aktualizace záznamu
                Stream::where('id', $streamId)->update(['pid' => $response["PID"], 'status' => "active"]);

                // notifikace
                $stream = Stream::where('id', $streamId)->first();

                StreamLogController::store($streamId, "reboot");

                MailController::send_success_stream($stream->nazev);
            }
        } catch (\Throwable $th) {
            // stream selhal => nic neprovedeme a necháme být, aby necrashnul cely process
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
        if (transcoder::where('ip', $transcoderIp)->first()->status === "offline") {
            return [
                'status' => "offline"
            ];
        }

        try {
            $response = Http::timeout(3)->get('http://' . $transcoderIp . '/tcontrol.php?CMD=NVSTATS');
            $response = json_decode($response, true);
            foreach ($response as $data) {
                $free = $data["fb_memory_usage"]["free"];
                $freeRam = str_replace(" MiB", "", $free);
                if (str_replace(" MiB", "", $freeRam) < "750") {
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
    public static function stream_analyse(Request $request): array
    {

        if (
            empty($request->transcoderId) || is_null($request->transcoderId) ||
            empty($request->stream_src) || is_null($request->stream_src)
        ) {

            return NotificationController::notify("error", "error", "Neočekávaná chyba!");
        }

        if ($transcoder = transcoder::where('id', $request->transcoderId)->first()) {

            if ($transcoder->status != "success") {
                return NotificationController::notify("error", "error", "Transcodér je offline!");
            }

            try {
                $response = Http::get('http://' . $transcoder->ip . '/tcontrol.php', [
                    'CMD' => $request->cmd,
                    'LOCK' => "FALSE",
                    'SRC' => $request->stream_src
                ]);

                $response = json_decode($response, true);

                if ($response["STATUS"] === "TRUE") {

                    return self::create_ffprobe_output_for_frontend($response);
                } else {
                    return NotificationController::notify("error", "error", "Nepodařilo se provést analýzu!");
                }
            } catch (\Throwable $th) {
                return NotificationController::notify("error", "error", "Nepodařilo se provést analýzu!");
            }
        } else {
            return NotificationController::notify("error", "error", "Nepodařilo se připojit k transcodéru!");
        }
    }

    /**
     * Pomocná funkce pro vytvoření vazeb pro audio / video / subtitles
     *
     * @param array $ffprobe
     * @return array
     */
    public static function create_ffprobe_output_for_frontend(array $ffprobe): array
    {
        $outputVideo = array();
        $outputAudio = array();
        $outputSubtitles = array();

        if (!array_key_exists("streams", $ffprobe["PROBE"])) {
            return NotificationController::notify("error", "error", "Selhalo vytvoření výstupu!");
        }

        foreach ($ffprobe["PROBE"]["streams"] as $streamData) {
            if (array_key_exists("codec_type", $streamData)) {

                /**
                 * VIDEO
                 */
                if ($streamData["codec_type"] == "video") {

                    $outputVideo[] = array(
                        'index' =>  $streamData["index"] ?? null,
                        'popis' => $streamData["index"] . " / video bez popisu" ?? null,
                        'input_codec' => self::find_input_codec_for_video($streamData["codec_name"]),
                        'codec_name' => $streamData["codec_name"] ?? null,
                        'codec_type' => $streamData["codec_type"] ?? null
                    );
                }

                /**
                 * AUDIO
                 */
                if ($streamData["codec_type"] == "audio") {

                    if (array_key_exists("tags", $streamData)) {
                        if (array_key_exists("language", $streamData["tags"])) {
                            $lang = $streamData["tags"]["language"];
                        } else {
                            $lang = "nepodarilo se detekovat audio";
                        }
                    } else {
                        $lang = "nepodarilo se detekovat audio";
                    }

                    $outputAudio[] = array(
                        'index' => $streamData["index"] ?? null,
                        'popis' => $streamData["codec_name"] . (" / " . $lang),
                        'codec_name' => $streamData["codec_name"] ?? null,
                        'codec_type' => $streamData["codec_type"] ?? null,
                        'lang' => $lang ?? null
                    );
                }

                /**
                 * SUBTITLES
                 */
                if ($streamData["codec_type"] == "subtitle") {

                    if (array_key_exists("tags", $streamData)) {
                        if (array_key_exists("language", $streamData["tags"])) {
                            $title_lang = $streamData["tags"]["language"];
                        } else {
                            $title_lang = "nepodarilo se detekovat audio";
                        }
                    } else {
                        $title_lang = "nepodarilo se detekovat audio";
                    }
                    $outputSubtitles[] = array(
                        'index' => $streamData["index"] ?? null,
                        'popis' => $streamData["codec_name"] . (" / " . $title_lang),
                        'codec_name' => $streamData["codec_name"] ?? null,
                        'codec_type' => $streamData["codec_type"] ?? null,
                        'lang' => $title_lang ?? null
                    );;
                }
            }
        }

        /**
         * návrat hodnot se statusem, videem / audiem / titulkama
         */
        return [
            'status' => "success",
            'video' => $outputVideo,
            'audio' => $outputAudio,
            'subtitles' => $outputSubtitles
        ];
    }


    /**
     * fn pro vyhledání codecu pro odeslání do frontendu a následně do backendu pro vytvoreni scriptu
     *
     * @param string $codec_name
     * @return string  mpeg2_cuvid , h264_cuvid , hevc_cuvid
     */
    public static function find_input_codec_for_video(string $codec_name): string
    {
        switch ($codec_name) {
            case 'mpeg2video':
                return "mpeg2_cuvid";
                break;
            case 'h264':
                return "h264_cuvid";
                break;
            case 'hevc':
                return "hevc_cuvid";
                break;
        }
    }


    /**
     * fn pro založení nového transcoderu
     *
     * @param Request $request -> name , ip
     * @return array
     */
    public function create_transcoder(Request $request): array
    {
        // validace inputu
        if (
            is_null($request->name) || empty($request->name) ||
            is_null($request->ip) || empty($request->ip)
        ) {
            return NotificationController::notify("warning", "warning", "Není vše řádně vyplněno!");
        }

        // validace formátu ipv4
        if (!filter_var($request->ip, FILTER_VALIDATE_IP)) {
            return NotificationController::notify("warning", "warning", "Neplatný formát IPv4!");
        }

        if (transcoder::where('ip', $request->ip)->first()) {
            return NotificationController::notify("warning", "warning", "Tato IPv4 je již registrována!");
        }

        try {
            transcoder::create([
                'name' => $request->name,
                'ip' => $request->ip,
                'status' => "waiting"
            ]);
            return NotificationController::notify("success", "success", "Založeno!");
        } catch (\Throwable $th) {
            return NotificationController::notify("error", "error", "Nepodařilo se založit!");
        }
    }

    /**
     * fn pro vyhledání a vypsání informací o transcoderu
     *
     * @param Request $request -> trasncoderId
     * @return array
     */
    public function search_transcoder(Request $request): array
    {

        if (!transcoder::where('id', $request->trasncoderId)->first()) {
            return [];
        }

        $transcoder = transcoder::where('id', $request->trasncoderId)->first();
        return [
            'name' => $transcoder->name,
            'ip' => $transcoder->ip
        ];
    }

    /**
     * fn pro editaci transcoderu
     *
     * @param Request $request
     * @return array
     */
    public function edit_transcoder(Request $request): array
    {
        // validace
        if (
            is_null($request->transcoderId) || empty($request->transcoderId) ||
            is_null($request->name) || empty($request->name) ||
            is_null($request->ip) || empty($request->ip)
        ) {
            return NotificationController::notify("warning", "warning", "Není vše řádně vyplněno!");
        }

        try {
            transcoder::where('id', $request->transcoderId)->update([
                'name' => $request->name,
                'ip' => $request->ip
            ]);

            return NotificationController::notify("success", "success", "Upraveno!");
        } catch (\Throwable $th) {
            return NotificationController::notify();
        }
    }

    /**
     * fn pro odebrání transcoderu dle transcoderId
     *
     * @param Request $request
     * @return array
     */
    public function delete_transcoder(Request $request): array
    {
        try {
            if (Stream::where('transcoder', $request->transcoderId)->first()) {
                return NotificationController::notify("error", "error", "Na transcoder jsou vázané kanály!");
            }
            transcoder::where('id', $request->transcoderId)->delete();
            return NotificationController::notify("success", "success", "Odebráno!");
        } catch (\Throwable $th) {
            return NotificationController::notify("error", "error", "Nepodařilo se odebrat!");
        }
    }


    /**
     * oveření funkčnosti streamů na transcoderech
     *
     * @return void
     */
    public static function check_if_streams_running(): void
    {
        $start = microtime(true);
        // kontrola probíhá na všech transcoderech, které mají status active
        if (transcoder::where('status', "success")->first()) {

            transcoder::where('status', "success")->each(function ($transcoder) {

                dispatch(new CheckStreamOnTranscoder($transcoder));
                // připojení do transcoderu a získání statusu a pidu
                // $response = Http::get('http://' . $transcoder->ip . '/tcontrol.php?CMD=GETPIDS&LOCK=FALSE');
                // $response = json_decode($response, true);

                // if ($response["STATUS"] == "TRUE") {

                //     if (!is_null($response["PIDS"])) {

                //         $pidsFromTranscodersString = implode(" ", $response["PIDS"]);

                //         Stream::where([
                //             ['status', "active"],
                //             ['transcoder', $transcoder->id]
                //         ])->each(function ($stream) use ($pidsFromTranscodersString) {

                //             if (!Str::contains($pidsFromTranscodersString, $stream->pid)) {
                //                 if ($stream->status != "issue") {
                //                     Stream::where('id', $stream->id)->update(['status' => "issue"]);
                //                     // MailController::send_error_stream($stream->nazev);
                //                 }
                //             }
                //         });
                //     }
                // }
            });
        }

        echo microtime(true) - $start . PHP_EOL;
    }


    /**
     * fn pro kontrolu zda jsou dostupne transcodery
     *
     * @return void
     */
    static function check_transcoder(): void
    {
        if (transcoder::first()) {
            foreach (transcoder::get() as $transcoder) {
                $ping = new Ping($transcoder->ip);
                $latency = $ping->ping();

                if ($latency !== false) {
                    // je ping na transcoder, nyní proběhne kontrola apache, zda opravdu funguje.

                    try {
                        if (Http::timeout(3)->get('http://' . $transcoder->ip . '/tcontrol.php?CMD=NVSTATS')->headers()['Content-Length'][0] === "0") {
                            // nejspíse není chyba přímo v transcodéru, ale označíme jej jako offline. jelikož transcodér nevrací správné hodnoty
                            transcoder::where('id', $transcoder->id)->update(['status' => "offline"]);
                        } else {
                            transcoder::where('id', $transcoder->id)->update(['status' => "success"]);
                        }
                    } catch (\Throwable $th) {
                        transcoder::where('id', $transcoder->id)->update(['status' => "offline"]);
                    }
                } else {

                    // transcodér neodpovídá na ping => označení jako offline a všechny streamy oznacit jako zastavené, pro případné spustení
                    transcoder::where('id', $transcoder->id)->update(['status' => "offline"]);
                    // update vsech streamu zalozenych na transcoderu na status STOP
                    if (Stream::where('transcoder', $transcoder->id)->first()) {
                        foreach (Stream::where('transcoder', $transcoder->id)->get() as $streamOnTranscoder) {
                            $streamOnTranscoder->update(
                                [
                                    'status' => "STOP"
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
