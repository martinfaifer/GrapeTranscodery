<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\transcoder;
use App\Models\Stream;

class ApiController extends Controller
{
    public $hello_dokumentace = "a42e5729-1d05-4698-b11b-0c96e51959a0";


    public function return_transcoder(Request $request)
    {
        // zatím jen obecne, kdy se vyhledá transcoder dle názvu

        if (is_null($request->transcoder) || empty($request->transcoder)) {
            return [
                'status' => "error"
            ];
        }

        if (!transcoder::where('name', $request->transcoder)->first()) {
            return [
                'status' => "error"
            ];
        }

        try {
            $transcoder = transcoder::where('name', $request->transcoder)->first();
            return [
                'status' => "success",
                'transcoder' => $request->transcoder,
                'ip' => $transcoder->ip,
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se připojit k Transcodéru"
            ];
        }
    }

    public function find_stream(Request $request): array
    {
        if (is_null($request->address) || empty($request->address)) {
            return [
                'status' => "error"
            ];
        }

        // vytvrorení uri
        $searcheableUri = "udp://" . $request->address;

        if ($stream = Stream::where('dst', $searcheableUri)->orWhere('dst2', $searcheableUri)->orWhere('dst3', $searcheableUri)->orWhere('dst4', $searcheableUri)->first()) {
            // vyhledáno

            return [
                'status' => "success",
                'streamId' => $stream->id,
            ];
        } else {

            // neexistuje záznam
            return [
                'status' => "error",
                'msg' => "Nepodařilo se připojit k Transcodéru"
            ];
        }
    }


    public function return_streamStatus_by_streamId(Request $request): array
    {
        if (is_null($request->streamId) || empty($request->streamId)) {
            return [
                'status' => "error"
            ];
        }

        if ($stream = Stream::where('id', $request->streamId)->first()) {
            // vyhledáno

            return [
                'status' => "success",
                'streamStatus' => $stream->status,
            ];
        } else {

            // neexistuje záznam
            return [
                'status' => "error",
                'msg' => "Nepodařilo se připojit k Transcodéru"
            ];
        }
    }


    public function return_reboot_status(Request $request): array
    {
        if (is_null($request->streamId) || empty($request->streamId)) {
            return [
                'status' => "error",
                'msg' => "Prázdné nebo null streamId"
            ];
        }

        if ($stream = Stream::where('id', $request->streamId)->first()) {
            // vyhledáno

            // zavolání fn pro restart streamu
            return TranscoderController::restart_running_stream_by_api($stream->pid, $stream->id, $stream->transcoder);
        } else {

            // neexistuje záznam
            return [
                'status' => "error",
                'msg' => "Neexistující streamId"
            ];
        }
    }
}
