<?php

use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamFormatController;
use App\Http\Controllers\StreamKvalityController;
use App\Http\Controllers\TranscoderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth -> prihlasení užiavatele
Route::post('login', [UserController::class, 'login']);
// Auth -> zobrazení infomrací o aktualně přihlášeném uzivateli
Route::get('user', [UserController::class, 'getLoggedUser']);


/**
 * TRANSCODER BLOK
 */
Route::get('transcoders', [TranscoderController::class, 'get_transcoders']);
// transcoder -> hw stats
Route::post('gpuStat', [TranscoderController::class, 'transcoder_hardware_usage']);
// transcoder -> take Ram usage
Route::post('ramUsage', [TranscoderController::class, 'transcoder_ram_usage']);
// transcoder -> take streams on transcoder
Route::post('transcoder/streams', [StreamController::class, 'get_streams_for_current_transcoder']);
// transcoder -> stop running stream
Route::post('transcoder/stream/stop', [TranscoderController::class, 'stop_running_stream']);
// transcoder -> start stream
Route::post('transcoder/stream/start', [TranscoderController::class, 'start_stream']);
// transcoder -> create
Route::post('transcoder/create', [TranscoderController::class, 'create_transcoder']);
// transcoder -> search
Route::post('transcoder/search', [TranscoderController::class, 'search_transcoder']);
// transcoder -> edit
Route::post('transcoder/edit', [TranscoderController::class, 'edit_transcoder']);
// transcoder -> delete
Route::post('transcoder/delete', [TranscoderController::class, 'delete_transcoder']);



/**
 * Streamy
 */
Route::get('streams', [StreamController::class, 'get_streams']);


/**
 * Nastavení
 */

//  FORMÁTY
// výpis všech formátů
Route::get('formats', [StreamFormatController::class, 'get_formats']);
// vytvoření nového formátu
Route::post('format/create', [StreamFormatController::class, 'create_format']);
// vyhledání formátu
Route::post('format/get', [StreamFormatController::class, 'search_format']);
// editace formátu
Route::post('format/edit', [StreamFormatController::class, 'format_edit']);
// format delete
Route::post('format/delete', [StreamFormatController::class, 'format_delete']);


// Výpis všech kvalit
Route::get('kvality', [StreamKvalityController::class, 'get_stream_kvality']);
// Kvality -> vytvoření nové
Route::post('kvality/create', [StreamKvalityController::class, 'create']);
// kvality -> odebrnání kvality
Route::post('kvality/delete', [StreamKvalityController::class, 'remove_kvality']);
// kvality -> vyhledání kvality dle formatu
Route::post('kvality/search', [StreamKvalityController::class, 'search_kvality_by_format']);
// kvality -> vypsání jedné kvality pro editaci
Route::post('kvality/get', [StreamKvalityController::class, 'get_one_kvality_for_edit']);
// kvality -> edit
Route::post('kvality/edit', [StreamKvalityController::class, 'kvalita_edit']);



/**
 * Nastavení streamů
 */
// založení nového streamu -> analýza , výpis z ffproby
Route::post('stream/analyze', [TranscoderController::class, 'stream_analyse']);
// zalození
Route::post('stream/create', [StreamController::class, 'stream_create']);
// vyhledání streamu
Route::post('stream/info', [StreamController::class, 'stream_search']);
// editace
Route::post('stream/edit', [StreamController::class, 'stream_edit']);
// výpis scriptu
Route::post('stream/script', [StreamController::class, 'stream_script']);
// editace scriptu
Route::post('stream/script/edit', [StreamController::class, 'stream_script_edit']);
// odebrání stream ze systému, pouze pokud je stream v jinem stavu nez active ( aktuálně transcodován )
Route::post('stream/delete', [StreamController::class, 'stream_delete']);
