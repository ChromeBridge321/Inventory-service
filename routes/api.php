<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MongoDB\Client;

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

Route::prefix(('v1'))->middleware(['api','jwt.verify'])->group(function(){
    Route::prefix(('products'))->group(function(){
        Route::post('/store',[ProductController::class,'store']);
        Route::get('/',[ProductController::class,'index']);
        Route::get('/low',[ProductController::class,'low']);
        Route::get('/{id}',[ProductController::class,'show']);
        Route::put('/{id}',[ProductController::class,'update']);
        Route::delete('/{id}',[ProductController::class,'destroy']);
        Route::get('/search/searchByName',[ProductController::class,'searchByName']);
    });   
});
Route::prefix(('v1'))->middleware(['api'])->group(function(){
    Route::prefix(('products'))->group(function(){
        Route::get('/return',[ProductController::class,'return']);
    });   
});

