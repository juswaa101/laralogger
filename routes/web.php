<?php

use App\Http\Controllers\LoggerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LoggerController::class, 'index'])->name('logger.index');
Route::get('/logs/data', [LoggerController::class, 'data'])->name('logs.data');
Route::post('/logs/clear', [LoggerController::class, 'clearLogs'])->name('logs.clear');
Route::get('/logs/download/{file}', [LoggerController::class, 'downloadLogs'])->name('logs.download');
Route::get('/logs/fetch', [LoggerController::class, 'fetchLogContents'])->name('logs.fetch');
