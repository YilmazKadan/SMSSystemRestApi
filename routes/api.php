<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\SmsController;

Route::post('giris', [ApiController::class, 'giris']);
Route::post('kayit', [ApiController::class, 'kayit']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('cikis', [ApiController::class, 'cikis']);
    Route::get('profil', [ApiController::class, 'profil']);

    Route::resource("sms","SmsController");

    // SMS detay kısmı sms.show rotasına tanımlıdır.
});
