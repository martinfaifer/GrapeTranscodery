<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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
Route::post('/stream', [ApiController::class, 'find_stream']);
Route::post('/stream/status', [ApiController::class, 'return_streamStatus_by_streamId']);
// Route::post('/stream/restart', [ApiController::class, 'return_reboot_status']);
