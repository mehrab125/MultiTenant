<?php

use Illuminate\Support\Facades\Route;

Route::middleware(\App\Http\Middleware\TenantMiddleaware::class)->group(function (){
  Route::get('/', function () {
      abort(404);
  })->name('api.site');
});
