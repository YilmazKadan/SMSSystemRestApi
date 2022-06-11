<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;


Route::post('giris', [ApiController::class, 'giris']);
Route::post('kayit', [ApiController::class, 'kayit']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('cikis', [ApiController::class, 'cikis']);
    Route::get('profil', [ApiController::class, 'profil']);
});
