<?php

use App\Services\Bitrix24\Facades\Bitrix24;
use GuzzleHttp\Client;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Ramsey\Uuid\Uuid;

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

Route::get('/test22',function (\Illuminate\Http\Request $request){
    \Illuminate\Support\Facades\Storage::put('test/test.json',json_encode($request->all()));
});

Route::get('/test11',function (){
    $uuid = Uuid::uuid4()->getHex();
    $uuid = base_convert($uuid,16,36);
    $hashed_time = hash('sha1',microtime(true));
    $hashed_time = base_convert($hashed_time,16,36);
    dd([
        $uuid,$hashed_time
    ]);
});

Route::get('/test33',function (){
    \App\Services\Bitrix24\Facades\Bitrix24::selectSpa(143);
    \App\Services\Bitrix24\Facades\Bitrix24::selectItem(144);
    return \App\Services\Bitrix24\Facades\Bitrix24::getSPA();
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

Route::get('/test',function (){

    return view('test');
});

/**
 * Signing page
 */
Route::get('/sign/{token}',[\App\Http\Controllers\SigningController::class,'index'])->name('signing');
Route::post('/sign/{token}',[\App\Http\Controllers\SigningController::class,'index']);


/**
 * Document handler
 */
Route::prefix('sign')->as('sign')->group(function (){
    Route::get('/v/{sign}',[\App\Http\Controllers\SignController::class,'verify'])->name('.verify');
});

/**
 * SPA event receiver
 */
Route::get('/request-signature',[\App\Http\Controllers\SPAReceiverController::class,'requestSignature']);
