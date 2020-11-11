<?php

namespace App\Http\Controllers;

use App\Models\StreamFormat;
use Illuminate\Http\Request;

class StreamFormatController extends Controller
{

    /**
     * funkcne na vrácení pole všech formátů, které umíme transcodovat
     *
     * @return array
     */
    public static function get_formats(): array
    {
        if (!StreamFormat::first()) {
            return [
                'status' => "empty"
            ];
        }

        foreach (StreamFormat::get() as $format) {
            $output[] = array(
                'id' => $format->id,
                'video' => $format->video,
                'code' => $format->code
            );
        }

        return [
            'status' => "success",
            'data' => $output
        ];
    }
}
