<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\TranscoderController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/transcoder', [ApiController::class, 'return_transcoder']);
Route::post('/transcoder/find', [ApiController::class, 'return_transcoder_name']);
Route::post('/stream', [ApiController::class, 'find_stream']);
Route::post('/stream/status', [ApiController::class, 'return_streamStatus_by_streamId']);
Route::post('/stream/restart', [ApiController::class, 'return_reboot_status']);
Route::post('/stream/manage', [ApiController::class, 'return_stream_action_status']);
Route::post('/transcoder/create', [TranscoderController::class, 'create_transcoder']);
Route::post('alerts', [StreamController::class, 'return_status_issue']);
Route::get('streams/log', [StreamLogController::class, 'index']);
