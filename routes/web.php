<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});


/**
 * Document handler
 */
Route::prefix('sign')->as('sign')->group(function (){
    Route::post('/create/',[\App\Http\Controllers\SignController::class,'create'])->name('.create');
    Route::get('/verify/{sign}',[\App\Http\Controllers\SignController::class,'verify'])->name('.verify');
});

Route::get('/test',function (){

    return \SimpleSoftwareIO\QrCode\Facades\QrCode::generate('Make me into a QrCode!');
});
