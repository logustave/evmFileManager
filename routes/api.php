<?php

use App\Http\Controllers\MediaController;
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

Route::post('/media/mediaTempUpload',[MediaController::class,'createTempMedia'])->name('createTempMedia');
Route::get('/media/queue/{filename}',[MediaController::class,'getHlsTreatment'])->name('getHlsTreatment');
