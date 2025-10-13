<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/users/login', [UserController::class, 'login']);

Route::get('/zip-codes', [\App\Http\Controllers\ZipController::class, 'index']);

Route::get('/zip-codes/{id}', [\App\Http\Controllers\ZipController::class, 'show']);

Route::put('/zip-codes/{id}', [\App\Http\Controllers\ZipController::class, 'update'])
    ->middleware('auth:sanctum');

Route::post('/zip-codes', [\App\Http\Controllers\ZipController::class, 'store'])
    ->middleware('auth:sanctum');

Route::delete('/zip-codes/{id}', [\App\Http\Controllers\ZipController::class, 'destroy'])
    ->middleware('auth:sanctum');


Route::get('/counties', [\App\Http\Controllers\CountyController::class, 'index']);

Route::get('/counties/{id}', [\App\Http\Controllers\CountyController::class, 'show']);

Route::get('/counties/{id}/abc', [\App\Http\Controllers\CountyController::class, 'placeInitials']);

Route::get('/counties/{id}/place-names', [\App\Http\Controllers\CountyController::class, 'placeNames']);

Route::get('/counties/{county}/place-names/{placeName}', [\App\Http\Controllers\CountyController::class, 'placeName']);

Route::post('/counties', [\App\Http\Controllers\CountyController::class, 'store'])
    ->middleware('auth:sanctum');

Route::put('/counties/{id}', [\App\Http\Controllers\CountyController::class, 'update'])
    ->middleware('auth:sanctum');

Route::delete('/counties/{id}', [\App\Http\Controllers\CountyController::class, 'destroy'])
    ->middleware('auth:sanctum');