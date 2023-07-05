<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\UserController;
use App\Models\Equipment;
use App\Models\Rental;
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
// User
Route::post('/login',[UserController::class,'login']);
Route::post('/register',[UserController::class,'register']);
Route::get('/user/sel-one/{id}',[UserController::class,'selOne']);

//Equipment
Route::get('/equipment/sel-all',[EquipmentController::class,'index']);
Route::get('/equipment/sel-one/{id}',[EquipmentController::class,'selOne']);

Route::group(['middleware'=>['auth:sanctum']], function () {

    // User
    Route::post('/logout',[UserController::class,'logout']);
    Route::put('/user/update/{id}',[UserController::class,'update']);
    Route::get('/user/sel-all',[UserController::class,'selAll']);
    Route::get('/user/sel-employee-owner',[UserController::class,'selEmplyeeOwner']);

    //Equipment 
    Route::post('/equipment/insert',[EquipmentController::class,'store']);
    Route::put('/equipment/update/{id}',[EquipmentController::class,'update']);
    Route::put('/equipment/delete/{id}',[EquipmentController::class,'destroy']);

    //rental 
    Route::post('/rental/insert',[RentalController::class,'store']);
    Route::get('/rental/sel-all',[RentalController::class,'index']);
    Route::put('/rental/update-address/{id}',[RentalController::class,'updateAddress']);
    Route::put('/rental/update-all/{id}',[RentalController::class,'update']);

});
