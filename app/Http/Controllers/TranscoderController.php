<?php

namespace App\Http\Controllers;

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

        foreach (transcoder::get() as $transcoder) {
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
            return $response = Http::get('http://' . $request->transcoderIp . '/tcontrol.php?CMD=NVSTATS');
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se připojit k Transcodéru"
            ];
        }
    }

    /**
     * fn pro výpis telemtrie
     *
     * @param string $transcoderIp, status
     */
    public static function telemetrie($transcoderIp, string $status)
    {
        if ($status == 'success') {
            try {
                $response = Http::get('http://' . $transcoderIp . '/tcontrol.php?CMD=NVSTATS');
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
    public static function stop_running_stream(Request $request)
    {
        try {
            $response = Http::get('http://' . $request->transcoderIp . '/tcontrol.php', [
                'PID' => $request->streamPid,
                'CMD' => $request->cmd
            ]);

            //
            //
            //
            $response = json_decode($response, true);
            if ($response["STATUS"] === "TRUE") {
                // zmena pidu na null a statusu na STOP
                Stream::where('id', $request->streamId)->update(['pid' => null, 'status' => "STOP"]);

                return [
                    'status' => "success",
                    'msg' => "Stream zastaven"
                ];
            } else {
                // nepodarilo se zastavit ffmpeg
                return [
                    'status' => "error",
                    'msg' => "Stream se nepodařilo zastavit"
                ];
            }
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
            $response = json_decode($response, true);
            if ($response["STATUS"] === "TRUE") {
                // vyhledání pidu a aktualizace záznamu
                Stream::where('id', $request->streamId)->update(['pid' => $response["PID"], 'status' => "active"]);

                return [
                    'status' => "success",
                    'msg' => "Stream spuštěn"
                ];
            } else {
                // nepodarilo se spustit ffmpeg
                return [
                    'status' => "error",
                    'msg' => "Stream se nepodařilo spustit"
                ];
            }
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
            //
            //
            //
            $response = json_decode($response, true);
            if ($response["STATUS"] === "TRUE") {
                // vyhledání pidu a aktualizace záznamu
                Stream::where('id', $streamId)->update(['pid' => $response["PID"], 'status' => "active"]);

                // notifikace
                $stream = Stream::where('id', $streamId)->first();
                MailController::send_success_stream($stream->nazev);
            }
            //
            //
            //
        } catch (\Throwable $th) {
            //
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

        if (
            empty($request->transcoderId) || is_null($request->transcoderId) ||
            empty($request->stream_src) || is_null($request->stream_src)
        ) {

            return [
                'status' => "error",
                'msg' => "Neočekávaná chyba"
            ];
        }

        if ($transcoder = transcoder::where('id', $request->transcoderId)->first()) {


            try {
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
            } catch (\Throwable $th) {
                return [
                    'status' => "error",
                    'msg' => "Nepodařilo se provést analýzu"
                ];
            }
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
                        'input_codec' => self::find_input_codec_for_video($streamData["codec_name"]),
                        'codec_name' => $streamData["codec_name"] ?? null,
                        'codec_type' => $streamData["codec_type"] ?? null
                    );
                }


                if ($streamData["codec_type"] == "audio") {

                    if (array_key_exists("tags", $streamData)) {
                        $lang = $streamData["tags"]["language"];
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
            }
        }

        return [
            'status' => "success",
            'video' => $outputVideo,
            'audio' => $outputAudio
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
            return [
                'status' => "warning",
                'msg' => "Není vše řádně vyplněno"
            ];
        }

        // validace formátu ipv4
        if (!filter_var($request->ip, FILTER_VALIDATE_IP)) {
            return [
                'status' => "warning",
                'msg' => "Neplatný formát IPv4"
            ];
        }

        if (transcoder::where('ip', $request->ip)->first()) {
            return [
                'status' => "warning",
                'msg' => "Tato IPv4 je již registrována"
            ];
        }

        try {
            transcoder::create([
                'name' => $request->name,
                'ip' => $request->ip,
                'status' => "waiting"
            ]);
            return [
                'status' => "success",
                'msg' => "Založeno"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se založit"
            ];
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
            return [
                'status' => "warning",
                'msg' => "Není vše řádně vyplněno!"
            ];
        }

        try {
            transcoder::where('id', $request->transcoderId)->update([
                'name' => $request->name,
                'ip' => $request->ip
            ]);

            return [
                'status' => "success",
                'msg' => "Upraveno"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "nepodařilo se upravit"
            ];
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
            transcoder::where('id', $request->transcoderId)->delete();
            return [
                'status' => "success",
                'msg' => "Odebráno"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se odebrat!"
            ];
        }
    }


    public static function check_if_streams_running(): void
    {
        // kontrola probíhá na všech transcoderech, které mají status active
        if (transcoder::where('status', "success")->first()) {

            foreach (transcoder::where('status', "success")->get() as $transcoder) {
                echo $transcoder->ip . "\n";
                // připojení do transcoderu a získání statusu a pidu
                $response = Http::get('http://' . $transcoder->ip . '/tcontrol.php?CMD=GETPIDS&LOCK=FALSE');
                $response = json_decode($response, true);

                if ($response["STATUS"] == "TRUE") {
                    // pole $response["PIDS"]
                    if (!is_null($response["PIDS"])) {

                        $pidsFromTranscodersString = implode(" ", $response["PIDS"]);
                        // dd($pidsFromTranscodersString);
                        foreach (Stream::where('status', "active")->where('transcoder', $transcoder->id)->get() as $stream) {
                            // dd($stream->pid);
                            if (!Str::contains($pidsFromTranscodersString, $stream->pid)) {
                                if ($stream->status != "issue") {
                                    Stream::where('id', $stream->id)->update(['status' => "issue"]);
                                    MailController::send_error_stream($stream->nazev);
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * fn pro kontrolu zda jsou dostupne transcodery
     *
     * @return void
     */
    public static function check_transcoder(): void
    {
        if (transcoder::first()) {
            foreach (transcoder::get() as $transcoder) {
                $ping = new Ping($transcoder->ip);
                $latency = $ping->ping();
                if ($latency !== false) {
                    transcoder::where('id', $transcoder->id)->update(['status' => "success"]);
                } else {
                    transcoder::where('id', $transcoder->id)->update(['status' => "offline"]);
                }
            }
        }
    }
}
