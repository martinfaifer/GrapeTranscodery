<?php

use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamFormatController;
use App\Http\Controllers\StreamKvalityController;
use App\Http\Controllers\TranscoderController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('test', function () {
//     return Auth::user();
// });
// Auth -> prihlasení užiavatele
Route::post('login', [UserController::class, 'login']);
// Auth -> zobrazení infomrací o aktualně přihlášeném uzivateli
Route::get('user', [UserController::class, 'getLoggedUser'])->middleware('auth');

// výpis všech uživatelů
Route::get('users', [UserController::class, 'users'])->middleware('auth');
// výpis uživatelských rolí
Route::get('users/role', [UserController::class, 'users_role'])->middleware('auth');
// zalozeni
Route::post('user/create', [UserController::class, 'create_user'])->middleware('auth');
// vyhledání uzivatele
Route::post('user/search', [UserController::class, 'search_user'])->middleware('auth');
// editace uzivatele
Route::post('user/edit', [UserController::class, 'edit_user'])->middleware('auth');
// delete
Route::post('user/delete', [UserController::class, 'delete_user'])->middleware('auth');
// update password
Route::post('user/password', [UserController::class, 'update_password'])->middleware('auth');


/**
 * TRANSCODER BLOK
 */
// transcodery -> telemetrie
Route::get('transcoders/telemetrie', [TranscoderController::class, 'transcoders_and_telemetrie'])->middleware('auth');
// transcodery
Route::get('transcoders', [TranscoderController::class, 'get_transcoders'])->middleware('auth');
// transcoder -> hw stats
Route::post('gpuStat', [TranscoderController::class, 'transcoder_hardware_usage'])->middleware('auth');
// transcoder -> take Ram usage
Route::post('ramUsage', [TranscoderController::class, 'transcoder_ram_usage'])->middleware('auth');
// transcoder -> take streams on transcoder
Route::post('transcoder/streams', [StreamController::class, 'get_streams_for_current_transcoder'])->middleware('auth');
// transcoder -> stop running stream
Route::post('transcoder/stream/stop', [TranscoderController::class, 'stop_running_stream'])->middleware('auth');
// transcoder -> start stream
Route::post('transcoder/stream/start', [TranscoderController::class, 'start_stream'])->middleware('auth');
// transcoder -> create
Route::post('transcoder/create', [TranscoderController::class, 'create_transcoder'])->middleware('auth');
// transcoder -> search
Route::post('transcoder/search', [TranscoderController::class, 'search_transcoder'])->middleware('auth');
// transcoder -> edit
Route::post('transcoder/edit', [TranscoderController::class, 'edit_transcoder'])->middleware('auth');
// transcoder -> delete
Route::post('transcoder/delete', [TranscoderController::class, 'delete_transcoder'])->middleware('auth');



/**
 * Streamy
 */
Route::get('streams', [StreamController::class, 'get_streams'])->middleware('auth');
// vraci streamy se statusem issue
Route::get('streams/issue', [StreamController::class, 'return_status_issue'])->middleware('auth');


/**
 * Nastavení
 */

//  FORMÁTY
// výpis všech formátů
Route::get('formats', [StreamFormatController::class, 'get_formats'])->middleware('auth');
// vytvoření nového formátu
Route::post('format/create', [StreamFormatController::class, 'create_format'])->middleware('auth');
// vyhledání formátu
Route::post('format/get', [StreamFormatController::class, 'search_format'])->middleware('auth');
// editace formátu
Route::post('format/edit', [StreamFormatController::class, 'format_edit'])->middleware('auth');
// format delete
Route::post('format/delete', [StreamFormatController::class, 'format_delete'])->middleware('auth');


// Výpis všech kvalit
Route::get('kvality', [StreamKvalityController::class, 'get_stream_kvality'])->middleware('auth');
// Kvality -> vytvoření nové
Route::post('kvality/create', [StreamKvalityController::class, 'create'])->middleware('auth');
// kvality -> odebrnání kvality
Route::post('kvality/delete', [StreamKvalityController::class, 'remove_kvality'])->middleware('auth');
// kvality -> vyhledání kvality dle formatu
Route::post('kvality/search', [StreamKvalityController::class, 'search_kvality_by_format'])->middleware('auth');
// kvality -> vypsání jedné kvality pro editaci
Route::post('kvality/get', [StreamKvalityController::class, 'get_one_kvality_for_edit'])->middleware('auth');
// kvality -> edit
Route::post('kvality/edit', [StreamKvalityController::class, 'kvalita_edit'])->middleware('auth');



/**
 * Nastavení streamů
 */
// založení nového streamu -> analýza , výpis z ffproby
Route::post('stream/analyze', [TranscoderController::class, 'stream_analyse'])->middleware('auth');
// zalození
Route::post('stream/create', [StreamController::class, 'stream_create'])->middleware('auth');
// vyhledání streamu
Route::post('stream/info', [StreamController::class, 'stream_search'])->middleware('auth');
// editace
Route::post('stream/edit', [StreamController::class, 'stream_edit'])->middleware('auth');
// výpis scriptu
Route::post('stream/script', [StreamController::class, 'stream_script'])->middleware('auth');
// editace scriptu
Route::post('stream/script/edit', [StreamController::class, 'stream_script_edit'])->middleware('auth');
// odebrání stream ze systému, pouze pokud je stream v jinem stavu nez active ( aktuálně transcodován )
Route::post('stream/delete', [StreamController::class, 'stream_delete'])->middleware('auth');
