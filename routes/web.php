<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => ['auth']], function(){
    Route::get('/app', function(){
        return view('app');
    })->name('home');}










);


// Sesion
Route::controller(LoginController::class)->group(function(){
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate')->name('start_login');
    Route::post('/logout', 'logout')->name('logout');
});

//Register
Route::get('/sign-up', function(){
    //TODO remplace by [CompanyController::class, 'showRegisterForm']
    return "TODO";
})->name('sign-up');


//Password reset
Route::controller(UserController::class)->group(function(){
    Route::get('/login/restore-password', 'showPasswordReset')->name('login_restore_password');
    Route::get('/user/password-reset/{data}', 'passwordReset')->name('user_password_reset');
    // Route::post('/user/password-update-restore','updatePasswordRestore')
    //     ->name('user_password_update_restore');
});



