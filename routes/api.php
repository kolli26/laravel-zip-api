<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/user/login', [UserController::class, 'login']);

Route::get('/zip-codes', [\App\Http\Controllers\ZipController::class, 'index']);

Route::post('/zip-code/create', [\App\Http\Controllers\ZipController::class, 'store'])
    ->middleware('auth:sanctum');

Route::post('/zip-code/{id}/delete', [\App\Http\Controllers\ZipController::class, 'destroy'])
    ->middleware('auth:sanctum');