<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamFormat;
use App\Models\StreamKvality;
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
                'transcoder' => transcoder::where('id', $stream->transcoder)->first()->name,
                'transcoderIp' => transcoder::where('id', $stream->transcoder)->first()->ip

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

        StreamValidationController::validate_stream_inputs($request);

        // vsechny inputy jsou v pořádku, začíná se vytvářet script pro spustení kanálu pro ffmpeg, který se následně zašle do transcoderu

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
                false
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
                false
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
                false
            );
        }

        // mapování pidů
        $videoPids = " -map 0:" . $request->videoIndex;
        $audioPids =  " -map 0:" . $request->audioIndex;

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

        return [
            'status' => "success",
            'msg' => "Založen nový stream"
        ];
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
                'transcoder' => $stream->transcoder
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

        StreamValidationController::validate_stream_inputs($request);

        // vsechny inputy jsou v pořádku, začíná se vytvářet script pro spustení kanálu pro ffmpeg, který se následně zašle do transcoderu

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
                false
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
                false
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
                false
            );
        }

        // mapování pidů
        $videoPids = " -map 0:" . $request->videoIndex;
        $audioPids =  " -map 0:" . $request->audioIndex;

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

        return [
            'status' => "success",
            'msg' => "Stream byl upraven"
        ];
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

            return [
                'status' => "success",
                'msg' => "Script byl upraven"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Script se nepodařilo upravit"
            ];
        }
        return [];
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
            return [
                'status' => "error",
                'msg' => "Stream se nepodařilo vyhledat"
            ];
        }

        $stream =  Stream::where('id', $request->streamId)->first();

        if ($stream->status == "active") {
            return [
                'status' => "error",
                'msg' => "Stream se transcoduje"
            ];
        }

        $stream->delete();
        return [
            'status' => "success",
            'msg' => "Stream byl úspěšně odebrán"
        ];
    }


    /**
     * fn pro pokus spustit stream, který je ve stavu issue
     *
     * @return void
     */
    public static function try_start_stream_with_issue(): void
    {

        if (Stream::where('status', "issue")->first()) {

            foreach (Stream::where('status', "issue")->get() as $stream) {
                TranscoderController::start_stream_from_backend(
                    transcoder::where('id', $stream->id)->first()->ip,
                    $stream->id
                );
            }
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
