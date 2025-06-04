<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\RegisterController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login.login');
})->name('login');

Route::get('/sign-up', function () {
    return view('sign-up');
})->name('sign-up');

Route::get('/login/restore-password', function () {
    return view('restore-password');
})->name('login_restore_password');


Route::controller(LoginController::class)->group(function(){
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate')->name('start_login');
    Route::post('/logout', 'logout')->name('logout');
});

Route::get('/main', function () {
    return view('mainloginview');
})->name('main');


