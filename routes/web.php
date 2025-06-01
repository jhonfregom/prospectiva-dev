<?php

use Illuminate\Support\Facades\Route;

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