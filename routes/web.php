<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\VariableController;
use App\Http\Controllers\MatrizController;
use App\Http\Controllers\GraphicsController;
use App\Http\Controllers\VariablesMapController;
use App\Http\Controllers\HypothesisController;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => ['auth']], function(){
    Route::get('/app', function(){
        return view('app');
    })->name('home');

    // Rutas de variables protegidas por autenticación
    Route::controller(VariableController::class)->group(function(){
        // Ruta GET que llama al método 'index' del controlador
        // Retorna todas las variables en formato JSON
        // Se accede mediante la URL: /variables
        // El nombre de la ruta es 'variables.index' para usar en enlaces
        Route::get('/variables', 'index')->name('variables.index');
        
        // Ruta POST que llama al método 'store' del controlador
        // Crea una nueva variable en la base de datos
        // Se accede mediante POST a la URL: /variables
        // El nombre de la ruta es 'variables.store' para usar en formularios
        Route::post('/variables', 'store')->name('variables.store');
        Route::put('/variables/{id}', 'update')->name('variables.update');
        Route::delete('/variables/{variable}', 'destroy')->name('variables.destroy');
    });

    // Rutas de matriz protegidas por autenticación
    Route::controller(MatrizController::class)->group(function(){
        Route::get('/matriz', 'index')->name('matriz.index');
        Route::post('/matriz', 'store')->name('matriz.store');
    });

    // Rutas de análisis protegidas por autenticación
    Route::controller(VariablesMapController::class)->group(function(){
        Route::get('/analysis', 'index')->name('analysis.index');
        Route::post('/analysis', 'store')->name('analysis.store');
        Route::put('/analysis/{id}', 'update')->name('analysis.update');
        Route::delete('/analysis/{id}', 'destroy')->name('analysis.destroy');
        Route::post('/analysis/reset-auto-increment', 'resetAutoIncrement')->name('analysis.reset-auto-increment');
        Route::post('/analysis/delete-all-reset', 'deleteAllAndReset')->name('analysis.delete-all-reset');
    });

    // Rutas de hipótesis protegidas por autenticación
    Route::controller(HypothesisController::class)->group(function(){
        Route::get('/hypothesis', 'index')->name('hypothesis.index');
        Route::post('/hypothesis', 'store')->name('hypothesis.store');
        Route::post('/hypothesis/reset-auto-increment', 'resetAutoIncrement')->name('hypothesis.reset-auto-increment');
        Route::post('/hypothesis/delete-all-reset', 'deleteAllAndReset')->name('hypothesis.delete-all-reset');
    });

    // Ruta de prueba para verificar autenticación
    Route::get('/test-auth', function() {
        return response()->json([
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user' => auth()->user()
        ]);
    });
});

// Sesion
Route::controller(LoginController::class)->group(function(){
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate')->name('start_login');
    Route::post('/logout', 'logout')->name('logout');
});

//Register
Route::get('/sign-up', function(){
    //TODO replace by [CompanyController::class, 'showRegisterForm']
    return "TODO";
})->name('sign-up');

//Password reset
Route::controller(UserController::class)->group(function(){
    Route::get('/login/restore-password', 'showPasswordReset')->name('login_restore_password');
    Route::get('/user/password-reset/{data}', 'passwordReset')->name('user_password_reset');
    // Route::post('/user/password-update-restore','updatePasswordRestore')
    //     ->name('user_password_update_restore');
});

Route::controller(RegisterController::class)->group(function(){
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register')->name('start_register');
});

Route::get('/graphics', [GraphicsController::class, 'index']);


