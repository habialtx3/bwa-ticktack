<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login',[AuthController::class, 'login']);
Route::post('/register',[AuthController::class, 'register']);


Route::middleware('auth:sanctum')->group(function(){
    Route::get('/me',[AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);
    
    Route::get('/dashboard/statistics',[DashboardController::class,'getStatistic']);
    
    Route::post('/ticket',[TicketController::class,'store']);
    Route::get('/ticket/{code}',[TicketController::class, 'show']);
    Route::get('/ticket',[TicketController::class, 'index']);
    Route::post('/ticket-reply/{code}',[TicketController::class,'storeReply']);

});