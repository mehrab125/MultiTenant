<?php

use App\Http\Controllers\Api\ProductHistoryController;
use App\Http\Controllers\Api\StockLevelController;
use App\Http\Controllers\Api\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::middleware(\App\Http\Middleware\TenantMiddleaware::class)->group(function (){
    Route::post('stock-movements', [StockMovementController::class,'store'])->name('api.stock.movements');
    Route::get('stock-levels', [StockLevelController::class,'index'])->name('api.stock.levels');
    Route::get('/products/{sku}/history', [ProductHistoryController::class,'index'])->name('api.products.history');
});
