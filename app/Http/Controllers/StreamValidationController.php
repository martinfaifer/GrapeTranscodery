<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class StreamValidationController extends Controller
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return array
     */
    public static function validate_stream_inputs(Request $request): array
    {

        if (is_null($request->stream_name) || empty($request->stream_name)) {
            return NotificationController::notify("warning", "warning", "Není vyplněn popis");
        }

        if (is_null($request->videoIndex) || empty($request->videoIndex)) {
            return NotificationController::notify("warning", "warning", "Není vyplněn video formát");
        }

        if (is_null($request->audioIndex) || empty($request->audioIndex)) {
            return NotificationController::notify("warning", "warning", "Není vyplněn audio formát");
        }

        if (is_null($request->formatCode) || empty($request->formatCode)) {
            return NotificationController::notify("warning", "warning", "Není vyplněn výstupní formát");
        }
        if (is_null($request->dst1) || empty($request->dst1)) {
            return NotificationController::notify("warning", "warning", "Není vyplněno dst 1");
        }

        if (is_null($request->dst1_kvality) || empty($request->dst1_kvality)) {
            return NotificationController::notify("warning", "warning", "Není vyplněno rozlišení u dst 1");
        }

        // pokud existuje dst2 a dst3
        if (!is_null($request->dst2) || !empty($request->dst2)) {
            // kontrola dst2_kvality , pokud empty nebo null => return warning
            if (is_null($request->dst2_kvality) || empty($request->dst2_kvality)) {
                return NotificationController::notify("warning", "warning", "Není vyplněno rozlišení u dst 2");
            }
        }

        if (!is_null($request->dst3) || !empty($request->dst3)) {
            // kontrola dst2_kvality , pokud empty nebo null => return warning
            if (is_null($request->dst3_kvality) || empty($request->dst3_kvality)) {
                return NotificationController::notify("warning", "warning", "Není vyplněno rozlišení u dst 3");
            }
        }

        return [
            'status' => "success"
        ];
    }
}
