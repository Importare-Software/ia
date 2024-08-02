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

Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    Route::get('/test-dusk', [App\Http\Controllers\API\V1\ByimageController::class, 'index'])->name('test-dusk');
    Route::post('/generate-dusk-code', [App\Http\Controllers\API\V1\ByimageController::class, 'generateDusk'])->name('generate-dusk-code');


    Route::get('/upload-excel', [App\Http\Controllers\V1\ExcelToDuskController::class, 'index'])->name('upload-excel');
    Route::post('/upload-excel', [App\Http\Controllers\V1\ExcelToDuskController::class, 'uploadExcel'])->name('uploadExcel');
    Route::post('/generate-dusk', [App\Http\Controllers\V1\ExcelToDuskController::class, 'generateDusk'])->name('generateDusk');

    Route::get('/manual-input', [App\Http\Controllers\V1\ManualInputDuskController::class, 'index'])->name('manual-input');
    Route::post('/generate-dusk-manual', [App\Http\Controllers\V1\ManualInputDuskController::class, 'generateDusk'])->name('generateDuskManual');

    Route::get('/test_results', [App\Http\Controllers\V1\TestResultController::class, 'index'])->name('test_results.index');
    Route::get('/test_results/{id}/edit', [App\Http\Controllers\V1\TestResultController::class, 'edit'])->name('test_results.edit');
    Route::put('/test_results/{id}', [App\Http\Controllers\V1\TestResultController::class, 'update'])->name('test_results.update');

    Route::post('/documents/upload', [App\Http\Controllers\V1\DocumentController::class, 'upload'])->name('documents.upload');
    Route::get('/upload-data', function () {
        return view('upload-data');
    })->name('upload.data');
    Route::get('/chat', [App\Http\Controllers\V1\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send-message', [App\Http\Controllers\V1\ChatController::class, 'sendMessage'])->name('chat.sendMessage');
    Route::get('/chat/get-messages', [App\Http\Controllers\V1\ChatController::class, 'getMessages'])->name('chat.getMessages');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
