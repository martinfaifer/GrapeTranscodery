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
            return [
                'status' => "error",
                'msg' => "Formát je juž založen"
            ];
        }


        if (StreamFormat::where('code', $request->videoCode)->first()) {
            // nesmí existovat shoda
            return [
                'status' => "error",
                'msg' => "Označení již existuje"
            ];
        }

        try {
            StreamFormat::create([
                'video' => $request->videoFormat,
                'code' => $request->videoCode
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

            return [
                'status' => "success",
                'msg' => "Formát byl upraven"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Formát se nepodařilo upravit"
            ];
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

            return [
                'status' => "success",
                'msg' => "Formát byl odebrán"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Formát se nepodařilo odebrat"
            ];
        }
    }
}
