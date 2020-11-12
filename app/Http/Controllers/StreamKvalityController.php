<?php

namespace App\Http\Controllers;

use App\Models\StreamFormat;
use App\Models\StreamKvality;
use Illuminate\Http\Request;

class StreamKvalityController extends Controller
{
    // formát VxS
    public $kvalita;
    // formát V:S
    public $meritko;

    public static function get_stream_kvality(): array
    {
        if (!StreamKvality::first()) {
            return [
                'status' => "empty"
            ];
        }

        foreach (StreamKvality::get() as $kvality) {
            $output[] = array(
                'id' => $kvality->id,
                'stream_format' => StreamFormat::where('id', $kvality->format_id)->first()->video,
                'video_code' => StreamFormat::where('id', $kvality->format_id)->first()->code,
                'kvalita' => $kvality->kvalita,
                'minrate' => $kvality->minrate,
                'maxrate' => $kvality->maxrate,
                'bitrate' => $kvality->bitrate,
                'scale' => $kvality->scale
            );
        }

        return [
            'status' => "success",
            'data' => $output
        ];
    }


    /**
     * funknce na vytvoření nové kvality
     *
     * @param Request $request videoFormat , minBitrate, maxBitrate, bitrate, sirka, vyska
     * @return array
     */
    public function create(Request $request): array
    {
        // overení ze vsechna data existují
        if (
            empty($request->videoFormat) ||
            empty($request->minBitrate) ||
            empty($request->maxBitrate) ||
            empty($request->bitrate) ||
            empty($request->sirka) ||
            empty($request->vyska)
        ) {
            return [
                'status' => "error",
                'msg' => "Nejsou vyplněny všechny položky"
            ];
        }

        // overení, zda min bitrate je nejmensí a maxbitrate je nejvetsí
        if (
            $request->minBitrate < $request->bitrate &&
            $request->minBitrate < $request->maxBitrate &&
            $request->bitrate < $request->maxBitrate
        ) {
        } else {
            return [
                'status' => "error",
                'msg' => "Hodnoty bitratu nedávají smysl, prosím překontrolujte hodnoty"
            ];
        }

        $this->kvalita = $request->vyska . "x" . $request->sirka;
        $this->meritko = $request->vyska . ":" . $request->sirka;

        // overení, že není shoda kvalit
        if (StreamKvality::where('format_id', $request->videoFormat)->where('scale', $this->meritko)->first()) {
            return [
                'status' => "error",
                'msg' => "Formát už má takto evidované parametry"
            ];
        }

        try {

            StreamKvality::create([
                'format_id' => $request->videoFormat,
                'kvalita' => $this->kvalita,
                'minrate' => $request->minBitrate,
                'maxrate' => $request->maxBitrate,
                'bitrate' => $request->bitrate,
                'scale' => $this->meritko
            ]);

            return [
                'status' => "success",
                'msg' => "Kvalita vytvořena"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se založit"
            ];
        }
    }


    /**
     * funkcne na odebrání
     *
     * @param Request $request kvalitaId
     * @return array
     */
    public function remove_kvality(Request $request): array
    {
        if (!StreamKvality::where('id', $request->kvalitaId)->first()) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se odebrat"
            ];
        }

        StreamKvality::where('id', $request->kvalitaId)->delete();

        return [
            'status' => "success",
            'msg' => "Odebráno"
        ];
    }


    /**
     * fn pro vyhledání kvality dle formátu videa
     *
     * ajax req
     *
     * @param Request $request->formatCode
     * @return array
     */
    public function search_kvality_by_format(Request $request): array
    {

        if (!StreamFormat::where('code', $request->formatCode)->first()) {
            return [];
        }

        return StreamKvality::where('format_id', StreamFormat::where('code',  $request->formatCode)->first()->id)->get()->toArray();
    }



    /**
     * fn pro vyhledání jedné kvality pro editaci
     *
     * @param Request $request->kvalitaId
     * @return array
     */
    public function get_one_kvality_for_edit(Request $request): array
    {
        if (!StreamKvality::where('id', $request->kvalitaId)->first()) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se vyhledat kvalitu!"
            ];
        }

        // výpis kvality
        $kvalita = StreamKvality::where('id', $request->kvalitaId)->first();
        $formatKvality = explode("x", $kvalita->kvalita);
        return [
            'sirka' => intval($formatKvality[0]),
            'vyska' => intval($formatKvality[1]),
            'minrate' => intval($kvalita->minrate),
            'maxrate' => intval($kvalita->maxrate),
            'bitrate' => intval($kvalita->bitrate)
        ];
    }


    /**
     * fn pro editaci kvality
     *
     * @param Request $request->kvalitaId, videoFormat, minBitrate, maxBitrate, bitrate, sirka, vyska
     * @return array
     */
    public function kvalita_edit(Request $request): array
    {
        try {
            $this->kvalita = $request->vyska . "x" . $request->sirka;
            $this->meritko = $request->vyska . ":" . $request->sirka;
            StreamKvality::where('id', $request->kvalitaId)->update(
                [
                    'format_id' => $request->videoFormat,
                    'kvalita' => $this->kvalita,
                    'scale' => $this->meritko,
                    'minrate' => $request->minBitrate,
                    'maxrate' => $request->maxBitrate,
                    'bitrate' => $request->bitrate
                ]
            );

            return [
                'status' => "success",
                'msg' => "Kvalita byla upraven"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Kvalitu se nepodařilo upravit"
            ];
        }
    }
}
