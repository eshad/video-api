<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('videos', [VideoController::class, 'index']);
Route::get('allvideos', [VideoController::class, 'allVideos']);
Route::post('videos', [VideoController::class, 'upload']);
Route::delete('videos', [VideoController::class, 'destroy']);
Route::get('videos/{id}/class', [VideoController::class, 'updateClass']);
Route::post('/videos/thumbnail', [VideoController::class, 'updateThumbnail']);
