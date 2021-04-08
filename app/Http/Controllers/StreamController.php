<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamFormat;
use App\Models\StreamKvality;
use App\Models\StreamLog;
use App\Models\transcoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StreamController extends Controller
{

    /**
     * funkce na výpočet, kolik streamů je na transcodéru
     *
     * @param string $transcoderId
     * @return string
     */
    public static function count_streams_on_transcoder(string $transcoderId): string
    {
        if (!Stream::where('transcoder', $transcoderId)->first()) {
            return "0";
        }
        return Stream::where('transcoder', $transcoderId)->get()->count();
    }

    /**
     * fn pro výpis poctu nefunknčních streamů na stra
     *
     * @param string $transcoderId
     * @return string
     */
    public static function count_issue_streams_on_transcoder(string $transcoderId): string
    {
        if (!Stream::where('transcoder', $transcoderId)->where('status', "issue")->first()) {
            return "0";
        }
        return Stream::where('transcoder', $transcoderId)->where('status', "issue")->get()->count();
    }


    /**
     * funkce na vrácení všech stramů
     *
     * @return array
     */
    public static function get_streams(): array
    {
        if (!Stream::first()) {
            return [];
        }

        foreach (Stream::get() as $stream) {
            $transcoder = transcoder::where('id', $stream->transcoder)->first();
            $output[] = array(
                'id' => $stream->id,
                'nazev' => $stream->nazev,
                'src' => $stream->src,
                'dst' => $stream->dst,
                'dst1_resolution' => $stream->dst1_resolution,
                'dst2' => $stream->dst2,
                'dst2_resolution' => $stream->dst2_resolution,
                'dst3' => $stream->dst3,
                'dst3_resolution' => $stream->dst3_resolution,
                'dst4' => $stream->dst4,
                'format' => $stream->format,
                'status' => $stream->status,
                'transcoder' => $transcoder->name ?? null,
                'transcoderIp' => $transcoder->ip ?? null

            );
        }
        return $output;
    }


    /**
     * funkcne na výpis vsech streamů co jsou připárovány na daný transcoder
     *
     * @param Request $request->transcoderIp
     * @return array
     */
    public function get_streams_for_current_transcoder(Request $request): array
    {

        if (!transcoder::where('ip', $request->transcoderIp)->first()) {
            // kde nic není ani kuře nehrabe
            return [
                'status' => "empty"
            ];
        }

        if (!Stream::where('transcoder', transcoder::where('ip', $request->transcoderIp)->first()->id)->first()) {
            return [
                'status' => "empty"
            ];
        }

        foreach (Stream::where('transcoder', transcoder::where('ip', $request->transcoderIp)->first()->id)->get() as $stream) {
            $output[] = array(
                'id' => $stream->id,
                'pid' => $stream->pid,
                'nazev' => $stream->nazev,
                'src' => $stream->src,
                'dst' => $stream->dst,
                'dst1_resolution' => $stream->dst1_resolution,
                'dst2' => $stream->dst2,
                'dst2_resolution' => $stream->dst2_resolution,
                'dst3' => $stream->dst3,
                'dst3_resolution' => $stream->dst3_resolution,
                'dst4' => $stream->dst4,
                'dst4_resolution' => $stream->dst4_resolution,
                'format' => $stream->format,
                'status' => $stream->status,
            );
        }

        return [
            'status' => "success",
            'data' => $output
        ];
    }



    /**
     * fn pro zalození nového streamu do systému pro následné spustené
     *
     * @param Request $request inputCodec, transcoderId, stream_src, videoIndex , dst3 , dst3_kvality , dst2 , dst2_kvality , dst1_kvality , dst1, formatCode, audioIndex , stream_name
     * @return array
     */
    public function stream_create(Request $request): array
    {
        $dst1 = "";
        $dst2 = "";
        $dst3 = "";
        // validace vstupů

        $validation = StreamValidationController::validate_stream_inputs($request);
        if ($validation['status'] != 'success') {
            return $validation;
        }

        // transcoder
        $transcoder = transcoder::where('id', $request->transcoderId)->first();
        $transcoderNameEploded = explode("_", $transcoder->name);
        // vsechny inputy jsou v pořádku, začíná se vytvářet script pro spustení kanálu pro ffmpeg, který se následně zašle do transcoderu

        // overení, že stream nemá k sobe prirazeny zádné titulky
        if (empty($request->subtitleIndex) || is_null($request->subtitleIndex)) {
            if ($request->dst1) {
                $kvalitaForBitrateDst1 = StreamKvality::where('id', $request->dst1_kvality)->first();
                $dst1 = " " . FFmpegScriptController::ffmpeg_script_dst_output_part(
                    $request->formatCode,
                    $kvalitaForBitrateDst1->bitrate,
                    $kvalitaForBitrateDst1->minrate,
                    $kvalitaForBitrateDst1->maxrate,
                    "fast",
                    trim($request->dst1),
                    $kvalitaForBitrateDst1->scale,
                    false,
                    "GRAPE_" . $transcoderNameEploded[1],
                    $request->stream_name . "_" . $kvalitaForBitrateDst1->scale
                );
            }

            if ($request->dst2) {
                $kvalitaForBitrateDst2 = StreamKvality::where('id', $request->dst2_kvality)->first();
                $dst2 = " " . FFmpegScriptController::ffmpeg_script_dst_output_part(
                    $request->formatCode,
                    $kvalitaForBitrateDst2->bitrate,
                    $kvalitaForBitrateDst2->minrate,
                    $kvalitaForBitrateDst2->maxrate,
                    "fast",
                    trim($request->dst2),
                    $kvalitaForBitrateDst2->scale,
                    false,
                    "GRAPE_" . $transcoderNameEploded[1],
                    $request->stream_name . "_" . $kvalitaForBitrateDst2->scale
                );
            }

            if ($request->dst3) {
                $kvalitaForBitrateDst3 = StreamKvality::where('id', $request->dst3_kvality)->first();
                $dst3 = " " . FFmpegScriptController::ffmpeg_script_dst_output_part(
                    $request->formatCode,
                    $kvalitaForBitrateDst3->bitrate,
                    $kvalitaForBitrateDst3->minrate,
                    $kvalitaForBitrateDst3->maxrate,
                    "fast",
                    trim($request->dst3),
                    $kvalitaForBitrateDst3->scale,
                    false,
                    "GRAPE_" . $transcoderNameEploded[1],
                    $request->stream_name . "_" . $kvalitaForBitrateDst3->scale
                );
            }
        }


        // mapování pidů
        $videoPids = " -map 0:" . $request->videoIndex;
        $audioPids =  " -map 0:" . $request->audioIndex;

        // stream má na sobě vázané titulky , oddělení od vnitřní logiky pro standartní vytvoření scriptu pro spustení streamu
        if (!empty($request->subtitleIndex) || !is_null($request->subtitleIndex)) {
            $kvalitaForBitrateDst1 = StreamKvality::where('id', $request->dst1_kvality)->first();
            $script_with_subtitles = FFmpegScriptController::stream_with_subtitles(
                trim($request->stream_src),
                $request->videoIndex,
                $request->subtitleIndex,
                $request->formatCode,
                $kvalitaForBitrateDst1->bitrate,
                $kvalitaForBitrateDst1->minrate,
                $kvalitaForBitrateDst1->maxrate,
                "fast",
                trim($request->dst1),
                $kvalitaForBitrateDst1->scale,
                false,
                "GRAPE_" . $transcoderNameEploded[1],
                $request->stream_name . "_" . $kvalitaForBitrateDst1->scale,
                $videoPids,
                $audioPids

            );
            return $this->create($request, $script_with_subtitles, $kvalitaForBitrateDst1);
        }

        // finální podoba scriptu
        $script = FFmpegScriptController::ffmpeg_create_script(
            $request->inputCodec,
            trim($request->stream_src),
            $videoPids,
            $audioPids,
            $dst1,
            $dst2,
            $dst3
        );
        // Založení streamu
        return $this->create($request, $script, $kvalitaForBitrateDst1, $kvalitaForBitrateDst2 ?? null, $kvalitaForBitrateDst3 ?? null);
    }

    /**
     * založení streamu do databaze
     *
     * @param Request $request
     * @param string $script
     * @param [type] $kvalitaForBitrateDst1
     * @param [type] $kvalitaForBitrateDst2
     * @param [type] $kvalitaForBitrateDst3
     * @return array
     */
    public function create(Request $request, string $script, $kvalitaForBitrateDst1, $kvalitaForBitrateDst2 = null, $kvalitaForBitrateDst3 = null): array
    {
        Stream::create([
            'nazev' => $request->stream_name,
            'src' => trim($request->stream_src),
            'dst' => trim($request->dst1),
            'dst1_resolution' => $kvalitaForBitrateDst1->kvalita,
            'dst2' => trim($request->dst2) ?? null,
            'dst2_resolution' => $kvalitaForBitrateDst2->kvalita ?? null,
            'dst3' => trim($request->dst3) ?? null,
            'dst3_resolution' => $kvalitaForBitrateDst3->kvalita ?? null,
            'format' => StreamFormat::where('code', $request->formatCode)->first()->video,
            'script' => $script,
            'transcoder' => $request->transcoderId,
            'status' => "STOP"
        ]);

        return NotificationController::notify("success", "success", "Založen nový stream");
    }


    /**
     * fn pro vyhledání streamu dle streamId
     *
     * @param Request $request-> streamId
     * @return array
     */
    public static function stream_search(Request $request): array
    {
        if ($stream = Stream::where('id', $request->streamId)->first()) {

            return [
                'id' => $stream->id,
                'nazev' => $stream->nazev,
                'src' => $stream->src,
                'dst' => $stream->dst,
                'dst1_resolution' => $stream->dst1_resolution,
                'dst2' => $stream->dst2,
                'dst2_resolution' => $stream->dst2_resolution,
                'dst3' => $stream->dst3,
                'dst3_resolution' => $stream->dst3_resolution,
                'transcoder' => $stream->transcoder,
                'format' => $stream->format
            ];
        }
        return [];
    }


    /**
     * fn pro editaci zastaveného streamu
     *
     * @param Request $request
     * @return array
     */
    public static function stream_edit(Request $request): array
    {

        $dst1 = "";
        $dst2 = "";
        $dst3 = "";
        // validace vstupů

        if (empty($request->videoIndex) && is_null($request->videoIndex)) {

            // edituje se pouze zmena na transcoderu

            Stream::where('id', $request->streamId)->update([
                'transcoder' => $request->transcoderId,
                'status' => "STOP"
            ]);

            return NotificationController::notify("success", "success", "Stream byl upraven");
        }

        // transcoder
        $transcoder = transcoder::where('id', $request->transcoderId)->first();
        $transcoderNameEploded = explode("_", $transcoder->name);

        // vsechny inputy jsou v pořádku, začíná se vytvářet script pro spustení kanálu pro ffmpeg, který se následně zašle do transcoderu

        try {
            // overení, že stream nemá k sobe prirazeny zádné titulky
            if (empty($request->subtitleIndex) || is_null($request->subtitleIndex)) {
                if ($request->dst1) {
                    $kvalitaForBitrateDst1 = StreamKvality::where('id', $request->dst1_kvality)->first();
                    $dst1 = " " . FFmpegScriptController::ffmpeg_script_dst_output_part(
                        $request->formatCode,
                        $kvalitaForBitrateDst1->bitrate,
                        $kvalitaForBitrateDst1->minrate,
                        $kvalitaForBitrateDst1->maxrate,
                        "fast",
                        trim($request->dst1),
                        $kvalitaForBitrateDst1->scale,
                        false,
                        "GRAPE_" . $transcoderNameEploded[1],
                        $request->stream_name . "_" . $kvalitaForBitrateDst1->scale
                    );
                }

                if ($request->dst2) {
                    $kvalitaForBitrateDst2 = StreamKvality::where('id', $request->dst2_kvality)->first();
                    $dst2 = " " . FFmpegScriptController::ffmpeg_script_dst_output_part(
                        $request->formatCode,
                        $kvalitaForBitrateDst2->bitrate,
                        $kvalitaForBitrateDst2->minrate,
                        $kvalitaForBitrateDst2->maxrate,
                        "fast",
                        trim($request->dst2),
                        $kvalitaForBitrateDst2->scale,
                        false,
                        "GRAPE_" . $transcoderNameEploded[1],
                        $request->stream_name . "_" . $kvalitaForBitrateDst2->scale
                    );
                }

                if ($request->dst3) {
                    $kvalitaForBitrateDst3 = StreamKvality::where('id', $request->dst3_kvality)->first();
                    $dst3 = " " . FFmpegScriptController::ffmpeg_script_dst_output_part(
                        $request->formatCode,
                        $kvalitaForBitrateDst3->bitrate,
                        $kvalitaForBitrateDst3->minrate,
                        $kvalitaForBitrateDst3->maxrate,
                        "fast",
                        trim($request->dst3),
                        $kvalitaForBitrateDst3->scale,
                        false,
                        "GRAPE_" . $transcoderNameEploded[1],
                        $request->stream_name . "_" . $kvalitaForBitrateDst3->scale
                    );
                }
            }
        } catch (\Throwable $th) {
            return NotificationController::notify("error", "error", "Nejspíše chybí rozlišení!");
        }

        // mapování pidů
        $videoPids = " -map 0:" . $request->videoIndex;
        $audioPids =  " -map 0:" . $request->audioIndex;

        // stream má na sobě vázané titulky , oddělení od vnitřní logiky pro standartní vytvoření scriptu pro spustení streamu
        if (!empty($request->subtitleIndex) || !is_null($request->subtitleIndex)) {
            $kvalitaForBitrateDst1 = StreamKvality::where('id', $request->dst1_kvality)->first();
            $script_with_subtitles = FFmpegScriptController::stream_with_subtitles(
                trim($request->stream_src),
                $request->videoIndex,
                $request->subtitleIndex,
                $request->formatCode,
                $kvalitaForBitrateDst1->bitrate,
                $kvalitaForBitrateDst1->minrate,
                $kvalitaForBitrateDst1->maxrate,
                "fast",
                trim($request->dst1),
                $kvalitaForBitrateDst1->scale,
                false,
                "GRAPE_" . $transcoderNameEploded[1],
                $request->stream_name . "_" . $kvalitaForBitrateDst1->scale,
                $videoPids,
                $audioPids

            );
            return self::update($request, $script_with_subtitles, $kvalitaForBitrateDst1,  $kvalitaForBitrateDst2 ?? null,  $kvalitaForBitrateDst3 ?? null);
        }

        // finální podoba scriptu
        $script = FFmpegScriptController::ffmpeg_create_script(
            $request->inputCodec,
            trim($request->stream_src),
            $videoPids,
            $audioPids,
            $dst1,
            $dst2,
            $dst3
        );
        // Editace streamu
        return self::update($request, $script, $kvalitaForBitrateDst1, $kvalitaForBitrateDst2 ?? null, $kvalitaForBitrateDst3 ?? null);
    }


    /**
     * update streamu
     *
     * @param Request $request
     * @param string $script
     * @param [type] $kvalitaForBitrateDst1
     * @param [type] $kvalitaForBitrateDst2
     * @param [type] $kvalitaForBitrateDst3
     * @return array
     */
    public static function update(Request $request,  string $script, $kvalitaForBitrateDst1, $kvalitaForBitrateDst2 = null, $kvalitaForBitrateDst3 = null): array
    {
        Stream::where('id', $request->streamId)->update([
            'nazev' => $request->stream_name,
            'src' => trim($request->stream_src),
            'dst' => trim($request->dst1),
            'dst1_resolution' => $kvalitaForBitrateDst1->kvalita,
            'dst2' => trim($request->dst2) ?? null,
            'dst2_resolution' => $kvalitaForBitrateDst2->kvalita ?? null,
            'dst3' => trim($request->dst3) ?? null,
            'dst3_resolution' => $kvalitaForBitrateDst3->kvalita ?? null,
            'format' => StreamFormat::where('code', $request->formatCode)->first()->video,
            'script' => $script,
            'transcoder' => $request->transcoderId,
            'status' => "STOP"
        ]);

        return NotificationController::notify("success", "success", "Upraveno!");
    }

    /**
     * fn pro výpis scriptu pro ffmepg
     *
     * @param Request $request->streamId
     * @return string
     */
    public function stream_script(Request $request): string
    {
        return Stream::where('id', $request->streamId)->first()->script;
    }


    /**
     * fn pro editaci scriptu , kdy se zasila req s parametry streamId a script
     *
     * @param Request $request streamId, script
     * @return array
     */
    public function stream_script_edit(Request $request): array
    {
        try {
            Stream::where('id', $request->streamId)->update([
                'script' => $request->script
            ]);

            return NotificationController::notify("success", "success", "Script byl upraven");
            return [
                'status' => "success",
                'msg' => "Script byl upraven"
            ];
        } catch (\Throwable $th) {
            return NotificationController::notify("error", "error", "Script se nepodařilo upravit");
        }
    }


    /**
     * fn pro odebrání streamu, který má status jiny nez active (neprobíhá transcoding)
     *
     * @param Request $request->streamId
     * @return array
     */
    public function stream_delete(Request $request): array
    {
        if (!Stream::where('id', $request->streamId)->first()) {
            return NotificationController::notify("error", "error", "Stream se nepodařilo vyhledat!");
        }

        $stream =  Stream::where('id', $request->streamId)->first();

        if ($stream->status == "active") {
            return NotificationController::notify("error", "error", "Stream se transcoduje!");
        }


        if (StreamLog::where('stream_id', $request->streamId)->first()) {
            StreamLog::where('stream_id', $request->streamId)->each(function ($streamLog) {
                $streamLog->delete();
            });
        }

        $stream->delete();
        return NotificationController::notify("success", "success", "Stream odebrán!");
    }


    /**
     * fn pro pokus spustit stream, který je ve stavu issue
     *
     * @return void
     */
    public static function try_start_stream_with_issue(): void
    {
        if (Stream::where('status', "issue")->first()) {

            Stream::where('status', "issue")->each(function ($stream) {
                TranscoderController::start_stream_from_backend(
                    transcoder::where('id', $stream->transcoder)->first()->ip,
                    $stream->id
                );
            });
        }
    }

    /**
     * fn pro vyhledání streamu pro status issue
     *
     * @return array
     */
    public static function return_status_issue(): array
    {
        if (!Stream::where('status', "issue")->first()) {
            return [
                'status' => "empty"
            ];
        }

        return [
            'count' => Stream::where('status', "issue")->get()->count(),
            'data' => Stream::where('status', "issue")->get('nazev')->toArray()

        ];
    }
}
