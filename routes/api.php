<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/login',[UserController::class,'login']);
Route::post('/register',[UserController::class,'register']);

Route::get('/user/sel-one/{id}',[UserController::class,'selOne']);
Route::group(['middleware'=>['auth:sanctum']], function () {

    Route::post('/logout',[UserController::class,'logout']);
    Route::put('/user/{id}',[UserController::class,'update']);
    Route::get('/user/sel-all',[UserController::class,'selAll']);
    
});
