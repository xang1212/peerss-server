<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PackageRentalController;
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

Route::get('/district',[RentalController::class,'getDistrict']);

//Equipment
Route::get('/equipment/sel-all',[EquipmentController::class,'index']);
Route::get('/equipment/sel-one/{id}',[EquipmentController::class,'selOne']);

//food
Route::get('/food/sel-all',[FoodController::class,'index']);
Route::get('/food/sel-one/{id}',[FoodController::class,'show']);

//package
Route::get('/package/sel-all',[PackageController::class,'index']);
Route::get('/package/sel-one/{id}',[PackageController::class,'show']);

Route::group(['middleware'=>['auth:sanctum']], function () {

    // User 
    Route::post('/logout',[UserController::class,'logout']);
    Route::put('/user/update/{id}',[UserController::class,'update']);
    Route::put('/user/app-update/{id}',[UserController::class,'updateUserAppClient']);
    Route::get('/user/sel-all',[UserController::class,'selAll']);
    Route::get('/user/sel-employee-owner',[UserController::class,'selEmplyeeOwner']);
    Route::get('/user/sel-customer',[UserController::class,'selCUSTOMER']);
    Route::post('/user/employee-register',[UserController::class,'employeeRegister']);
    Route::delete('/user/delete/{id}',[UserController::class,'destroy']);

    //Equipment 
    Route::get('/equipment/sel-all-brokens',[EquipmentController::class,'sel_equipment_broken']);
    Route::post('/equipment/insert',[EquipmentController::class,'store']);
    Route::put('/equipment/update/{id}',[EquipmentController::class,'update']);
    Route::delete('/equipment/delete/{id}',[EquipmentController::class,'destroy']);

    //food 
    Route::post('/food/insert',[FoodController::class,'store']);
    Route::put('/food/update/{id}',[FoodController::class,'update']);
    Route::delete('/food/delete/{id}',[FoodController::class,'destroy']);
    Route::get('/sel-all-model',[FoodController::class,'all_index']);

    //rental 
    Route::post('/rental/insert',[RentalController::class,'store']);
    Route::post('/rental/app-insert',[RentalController::class,'rental_app']);
    Route::post('/rental/walk-in',[RentalController::class,'walk_in']);
    Route::get('/rental/sel-all',[RentalController::class,'index']);
    Route::get('/rental/by-user/{user_id}', [RentalController::class, 'getRentalsByUserId']);
    Route::get('/rental/sel-one/{id}',[RentalController::class,'show']);
    Route::get('/rental/sel-all-pending',[RentalController::class,'sel_pending']);
    Route::get('/rental/sel-all-shipping',[RentalController::class,'sel_shipping']);
    Route::get('/rental/sel-all-picking',[RentalController::class,'sel_picking']);
    Route::get('/rental/sel-shipping-date/{shipping_date}',[RentalController::class,'sel_shipping_date']);
    Route::get('/rental/sel-picking-date/{picking_date}',[RentalController::class,'sel_picking_date']);
    Route::put('/rental/update-address/{id}',[RentalController::class,'updateAddress']);
    Route::put('/rental/update/{id}',[RentalController::class,'update']);
    Route::put('/rental/update-status/{id}',[RentalController::class,'update_status']);
    Route::put('/rental/update-shipping/{id}',[RentalController::class,'update_shipping']);
    Route::put('/rental/update-picking/{id}',[RentalController::class,'update_picking']);
    Route::delete('/rental/delete/{id}',[RentalController::class,'destroy']);
    //Route::put('/rental/update-eq-broken/{id}',[RentalController::class,'update_eq_broken']);

    // package rental
    Route::post('/package-rental/insert',[PackageRentalController::class,'store']);
    Route::get('/package-rental/sel-all',[PackageRentalController::class,'index']);
    Route::get('/package-rental/sel-one/{id}',[PackageRentalController::class,'show']);

    //package 
    Route::post('/package/insert',[PackageController::class,'store']);
    Route::get('/package/sel-all',[PackageController::class,'index']);
    Route::get('/package/sel-one/{id}',[PackageController::class,'show']);
    Route::get('/package/by-food/{foodId}', [PackageController::class, 'getPackagesByFoodId']);
    Route::put('/package/update/{id}',[PackageController::class,'update']);
    Route::delete('/package/delete/{id}',[PackageController::class,'destroy']);

    // Route::put('/rental/update-address/{id}',[PackageController::class,'updateAddress']);
    // Route::put('/rental/update-all/{id}',[PackageController::class,'update']); eqm_bk_delete

    Route::delete('/equipment-broken/delete/{id}',[RentalController::class,'eqm_bk_delete']);

});
