<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            . $transcoderSpeed . " -vf scale_npp=" . $bitrateScale . " -metadata service_provider=\"{$serviceProvider}\" -metadata service_name=\"{$serviceName}\"" . " -f mpegts "
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
}
