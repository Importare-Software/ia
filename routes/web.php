<?php

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

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/test-dusk', [App\Http\Controllers\API\V1\ByimageController::class, 'index'])->name('test-dusk');
Route::post('/generate-dusk-code', [App\Http\Controllers\API\V1\ByimageController::class, 'generateDusk'])->name('generate-dusk-code');

Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/upload-excel', [App\Http\Controllers\V1\ExcelToDuskController::class, 'index'])->name('upload-excel');
Route::post('/upload-excel', [App\Http\Controllers\V1\ExcelToDuskController::class, 'uploadExcel'])->name('uploadExcel');
Route::post('/generate-dusk', [App\Http\Controllers\V1\ExcelToDuskController::class, 'generateDusk'])->name('generateDusk');

Route::get('/manual-input', [App\Http\Controllers\V1\ManualInputDuskController::class, 'index'])->name('manual-input');
Route::post('/generate-dusk-manual', [App\Http\Controllers\V1\ManualInputDuskController::class, 'generateDusk'])->name('generateDuskManual');

Route::get('/test_results', [App\Http\Controllers\V1\TestResultController::class, 'index'])->name('test_results.index');
Route::get('/test_results/{id}/edit', [App\Http\Controllers\V1\TestResultController::class, 'edit'])->name('test_results.edit');
Route::put('/test_results/{id}', [App\Http\Controllers\V1\TestResultController::class, 'update'])->name('test_results.update');


Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');