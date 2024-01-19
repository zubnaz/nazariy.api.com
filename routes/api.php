<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get("categories",[CategoryController::class,'getAll']);
Route::post("categories/create",[CategoryController::class,'createCategory']);
Route::get("categories/get_by_id/{id}",[CategoryController::class,"getById"]);
Route::post("categories/edit/{id}",[CategoryController::class,"edit"]);
Route::get("categories/delete/{id}",[CategoryController::class,"delete"]);

Route::post("/register",[AuthController::class,'register']);
Route::post("/login",[AuthController::class,'login']);
Route::get("/leave",[AuthController::class,'leave']);

