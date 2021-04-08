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


    /**
     * fn pro založení noveho formátu
     *
     * @param Request $request videoFormat , videoCode
     * @return array
     */
    public function create_format(Request $request): array
    {
        if (StreamFormat::where('video', $request->videoFormat)->first()) {
            // nesmí existovat shoda
            return NotificationController::notify("error", "error", "Formát je juž založen!");
        }


        if (StreamFormat::where('code', $request->videoCode)->first()) {
            // nesmí existovat shoda
            return NotificationController::notify("error", "error", "Označení již existuje!");
        }

        try {
            StreamFormat::create([
                'video' => $request->videoFormat,
                'code' => $request->videoCode
            ]);

            return NotificationController::notify("success", "success", "Založeno!");
        } catch (\Throwable $th) {
            return NotificationController::notify();
        }
    }


    /**
     * fn pro vyhledání formátu pro frontend / editaci
     *
     * @param Request $request->formatId
     * @return array
     */
    public static function search_format(Request $request): array
    {
        if (!StreamFormat::where('id', $request->formatId)->first()) {
            return [];
        }

        $format = StreamFormat::where('id', $request->formatId)->first();
        return [
            'video' => $format->video,
            'code' => $format->code,
        ];
    }

    /**
     * fn pro editaci ormátu
     *
     * @param Request $request -> formatId, video , code
     * @return array
     */
    public function format_edit(Request $request): array
    {
        try {
            StreamFormat::where('id', $request->formatId)->update([
                'video' => $request->video,
                'code' => $request->code
            ]);

            return NotificationController::notify("success", "success", "Formát byl upraven!");
        } catch (\Throwable $th) {
            return NotificationController::notify();
        }
    }


    /**
     * fn pro odebrání kvality
     *
     * @param Request $request -> formatId
     * @return array
     */
    public function format_delete(Request $request): array
    {
        try {
            StreamFormat::where('id', $request->formatId)->delete();

            return NotificationController::notify("success", "success", "Formát odebrán!");
        } catch (\Throwable $th) {
            return NotificationController::notify();
        }
    }
}
