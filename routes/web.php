<?php

use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamFormatController;
use App\Http\Controllers\StreamKvalityController;
use App\Http\Controllers\TranscoderController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth -> prihlasení užiavatele
Route::post('login', [UserController::class, 'login']);
// Auth -> logout
Route::get('logout', [UserController::class, 'lognout']);
// Auth -> zobrazení infomrací o aktualně přihlášeném uzivateli
Route::get('user', [UserController::class, 'getLoggedUser']);

// výpis všech uživatelů
Route::get('users', [UserController::class, 'users'])->middleware('access');
// výpis uživatelských rolí
Route::get('users/role', [UserController::class, 'users_role'])->middleware('access');
// zalozeni
Route::post('user/create', [UserController::class, 'create_user'])->middleware('access');
// vyhledání uzivatele
Route::post('user/search', [UserController::class, 'search_user'])->middleware('access');
// editace uzivatele
Route::post('user/edit', [UserController::class, 'edit_user'])->middleware('access');
// delete
Route::post('user/delete', [UserController::class, 'delete_user'])->middleware('access');
// update password
Route::post('user/password', [UserController::class, 'update_password'])->middleware('access');


/**
 * TRANSCODER BLOK
 */
// transcodery -> telemetrie
Route::get('transcoders/telemetrie', [TranscoderController::class, 'transcoders_and_telemetrie'])->middleware('access');
// transcodery
Route::get('transcoders', [TranscoderController::class, 'get_transcoders'])->middleware('access');
// transcoder -> hw stats
Route::post('gpuStat', [TranscoderController::class, 'transcoder_hardware_usage'])->middleware('access');
// transcoder -> take Ram usage
Route::post('ramUsage', [TranscoderController::class, 'transcoder_ram_usage'])->middleware('access');
// transcoder -> take streams on transcoder
Route::post('transcoder/streams', [StreamController::class, 'get_streams_for_current_transcoder'])->middleware('access');
// transcoder -> stop running stream
Route::post('transcoder/stream/stop', [TranscoderController::class, 'stop_running_stream'])->middleware('access');
// transcoder -> start stream
Route::post('transcoder/stream/start', [TranscoderController::class, 'start_stream'])->middleware('access');
// transcoder -> create
Route::post('transcoder/create', [TranscoderController::class, 'create_transcoder'])->middleware('access');
// transcoder -> search
Route::post('transcoder/search', [TranscoderController::class, 'search_transcoder'])->middleware('access');
// transcoder -> edit
Route::post('transcoder/edit', [TranscoderController::class, 'edit_transcoder'])->middleware('access');
// transcoder -> delete
Route::post('transcoder/delete', [TranscoderController::class, 'delete_transcoder'])->middleware('access');



/**
 * Streamy
 */
Route::get('streams', [StreamController::class, 'get_streams'])->middleware('access');
// vraci streamy se statusem issue
Route::get('streams/issue', [StreamController::class, 'return_status_issue'])->middleware('access');


/**
 * Nastavení
 */

//  FORMÁTY
// výpis všech formátů
Route::get('formats', [StreamFormatController::class, 'get_formats'])->middleware('access');
// vytvoření nového formátu
Route::post('format/create', [StreamFormatController::class, 'create_format'])->middleware('access');
// vyhledání formátu
Route::post('format/get', [StreamFormatController::class, 'search_format'])->middleware('access');
// editace formátu
Route::post('format/edit', [StreamFormatController::class, 'format_edit'])->middleware('access');
// format delete
Route::post('format/delete', [StreamFormatController::class, 'format_delete'])->middleware('access');


// Výpis všech kvalit
Route::get('kvality', [StreamKvalityController::class, 'get_stream_kvality'])->middleware('access');
// Kvality -> vytvoření nové
Route::post('kvality/create', [StreamKvalityController::class, 'create'])->middleware('access');
// kvality -> odebrnání kvality
Route::post('kvality/delete', [StreamKvalityController::class, 'remove_kvality'])->middleware('access');
// kvality -> vyhledání kvality dle formatu
Route::post('kvality/search', [StreamKvalityController::class, 'search_kvality_by_format'])->middleware('access');
// kvality -> vypsání jedné kvality pro editaci
Route::post('kvality/get', [StreamKvalityController::class, 'get_one_kvality_for_edit'])->middleware('access');
// kvality -> edit
Route::post('kvality/edit', [StreamKvalityController::class, 'kvalita_edit'])->middleware('access');



/**
 * Nastavení streamů
 */
// založení nového streamu -> analýza , výpis z ffproby
Route::post('stream/analyze', [TranscoderController::class, 'stream_analyse'])->middleware('access');
// zalození
Route::post('stream/create', [StreamController::class, 'stream_create'])->middleware('access');
// vyhledání streamu
Route::post('stream/info', [StreamController::class, 'stream_search'])->middleware('access');
// editace
Route::post('stream/edit', [StreamController::class, 'stream_edit'])->middleware('access');
// výpis scriptu
Route::post('stream/script', [StreamController::class, 'stream_script'])->middleware('access');
// editace scriptu
Route::post('stream/script/edit', [StreamController::class, 'stream_script_edit'])->middleware('access');
// odebrání stream ze systému, pouze pokud je stream v jinem stavu nez active ( aktuálně transcodován )
Route::post('stream/delete', [StreamController::class, 'stream_delete'])->middleware('access');


// test¨
Route::get('test', function () {
    $response = Http::get('http://172.17.3.82/tcontrol.php', [
        'CMD' => "PROBE",
        'LOCK' => "FALSE",
        'SRC' => "udp://239.250.5.136:1234"
    ]);

    $response = json_decode($response, true);
    // return $response;
    if ($response["STATUS"] === "TRUE") {


        // status success => vyhledání streamů

        return TranscoderController::create_ffprobe_output_for_frontend($response);
    } else {
    }
});
