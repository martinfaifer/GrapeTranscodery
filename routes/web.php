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

// Výpis všech kvalit
Route::get('kvality', [StreamKvalityController::class, 'get_stream_kvality']);
// Kvality -> vytvoření nové
Route::post('kvality/create', [StreamKvalityController::class, 'create']);
// kvality -> odebrnání kvality
Route::post('kvality/delete', [StreamKvalityController::class, 'remove_kvality']);
// kvality -> vyhledání kvality dle formatu
Route::post('kvality/search', [StreamKvalityController::class, 'search_kvality_by_format']);


/**
 * Nastavení streamů
 */
// založení nového streamu -> analýza , výpis z ffproby
Route::post('stream/analyze', [TranscoderController::class, 'stream_analyse']);
// zalození
Route::post('stream/create', [StreamController::class, 'stream_create']);
