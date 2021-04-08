<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Support\Str;

class FFmpegScriptController extends Controller
{
    /**
     * funknce na vytvorení outputu
     *
     * @param [type] $videoFormat
     * @param [type] $bitrate
     * @param [type] $bitrateMin
     * @param [type] $bitrateMax
     * @param [type] $transcoderSpeed
     * @param [type] $dst
     * @param [type] $bitrateScale
     * @param [type] $isRadioChannel
     * @param [type] $serviceProvider ( příklad GRAPE_Q10)
     * @param [type] $serviceName (příklad HBO_1080p )
     * @return string
     */
    public static function ffmpeg_script_dst_output_part($videoFormat, $bitrate, $bitrateMin, $bitrateMax, $transcoderSpeed, $dst, $bitrateScale, $isRadioChannel, $serviceProvider, $serviceName): string
    {
        $script = " -c:v " . $videoFormat . " -qmin 0 -qmax 50 -c:a libfdk_aac -b:a 128k -b:v "
            . $bitrate . "k -minrate:v "
            . $bitrateMin . "k -maxrate:v "
            . $bitrateMax . "k -g 50 -bufsize 6M -preset "
            . $transcoderSpeed . " -vf scale_npp=" . $bitrateScale . " -metadata service_provider=\"{$serviceProvider}\" -metadata service_name=\"{$serviceName}\"" . " -flush_packets 0 -f mpegts "
            . $dst . "?pkt_size=1316";

        return $script;
    }

    /**
     * fn pro vytvoření skriptu pro spustení streamu
     *
     * @param string $formatCode
     * @param string  $src
     * @param string $videoPids
     * @param string  $audioPids
     * @param string  $dst1
     * @param string  $dst2
     * @param string  $dst3
     * @return string
     */
    public static function ffmpeg_create_script(
        string $formatCode,
        string $src,
        string $videoPids,
        string $audioPids,
        string $dst1,
        string $dst2,
        string $dst3
    ): string {

        return $script = "ffmpeg -loglevel quiet -hwaccel cuvid -c:v "
            . $formatCode .
            " -deint 2 -drop_second_field 1 -i "
            . $src . "?overrun_nonfatal=1\"&\"fifo_size=57344\"&\"buffer_size=256000"
            . $videoPids . $audioPids .
            " -rc-lookahead 32 -profile:v main" .
            // Vystupni format vcetne dst ....
            $dst1 .
            $dst2 .
            $dst3 . " > /dev/null 2>&1 & echo $!";
    }


    public static function upgrade_script(): void
    {
        if (!Stream::first()) {
            exit();
        }

        Stream::all()->each(function ($stream) {

            if (!Str::contains($stream->script, '-flush_packets 0 -f mpegts')) {
                $new_script = str_replace("-f mpegts", "-flush_packets 0 -f mpegts", $stream->script);

                $stream->update([
                    'script' => $new_script
                ]);

                unset($new_script);

                $restart_output = TranscoderController::restart_running_stream_by_api($stream->pid, $stream->id, $stream->transcoder);

                if ($restart_output['status'] === "error") {
                    StreamLogController::store($stream->id, "restart_error");
                } else {
                    StreamLogController::store($stream->id, "restart_ok");
                }
            }
        });
    }

    /**
     * fn pro vytvoření scriptu pro vpalování titulků do obrazu
     *
     * @param string $src
     * @param string $videoIndex
     * @param string $subtitleIndex
     * @param string $videoFormat
     * @param string $bitrate
     * @param string $bitrateMin
     * @param string $bitrateMax
     * @param string $transcoderSpeed
     * @param string $dst
     * @param string $bitrateScale
     * @param string $isRadioChannel
     * @param string $serviceProvider
     * @param string $serviceName
     * @param string $videoPids
     * @param string $audioPids
     * @return string
     */
    public static function stream_with_subtitles(string $src, string $videoIndex, string $subtitleIndex, string $videoFormat, string $bitrate, string $bitrateMin, string $bitrateMax, string $transcoderSpeed, string $dst, string $bitrateScale, string $isRadioChannel, string $serviceProvider, string $serviceName, string $videoPids, string $audioPids): string
    {
        $script = "ffmpeg -loglevel quiet -hwaccel cuvid -deint 2 -drop_second_field 1 -i "
            . $src . "?overrun_nonfatal=1\"&\"fifo_size=57344\"&\"buffer_size=256000 -filter_complex \"[" . $videoIndex . ":v][0:s:" . $subtitleIndex . "]overlay=main_w/2-overlay_w/2:main_h/2-overlay_h/2\" " . $videoPids . $audioPids . " -rc-lookahead 32 -profile:v main " .
            self::ffmpeg_script_dst_output_part($videoFormat, $bitrate, $bitrateMin, $bitrateMax, $transcoderSpeed, $dst, $bitrateScale, $isRadioChannel, $serviceProvider, $serviceName) . "  > /dev/null 2>&1 & echo $!";
        return $script;
    }
}
