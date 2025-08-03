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
use App\Http\Controllers\ScenariosController;
use App\Http\Controllers\ConclusionController;
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OllamaProxyController;
use App\Http\Controllers\ChatGPTProxyController;
use App\Http\Controllers\DeepSeekProxyController;
use App\Http\Controllers\GeminiProxyController;
use App\Http\Controllers\EconomicSectorController;
use App\Http\Controllers\OpenRouterProxyController;


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
        Route::post('/analysis/reset-auto-increment', 'resetAutoIncrement')->name('analysis.resetAutoIncrement');
        Route::post('/analysis/delete-all-reset', 'deleteAllAndReset')->name('analysis.deleteAllAndReset');
        Route::post('/analysis/close-all', 'closeAllAnalyses')->name('analysis.closeAllAnalyses');
        Route::post('/analysis/reopen-all', 'reopenAllAnalyses')->name('analysis.reopenAllAnalyses');
    });

    // Rutas de hipótesis protegidas por autenticación
    Route::controller(HypothesisController::class)->group(function(){
        Route::get('/hypothesis', 'index')->name('hypothesis.index');
        Route::post('/hypothesis', 'store')->name('hypothesis.store');
        Route::put('/hypothesis/{id}', 'update')->name('hypothesis.update');
        Route::post('/hypothesis/reset-auto-increment', 'resetAutoIncrement')->name('hypothesis.reset-auto-increment');
        Route::post('/hypothesis/delete-all-reset', 'deleteAllAndReset')->name('hypothesis.delete-all-reset');
        Route::post('/hypothesis/close-all', 'closeAllHypotheses')->name('hypothesis.closeAllHypotheses');
        Route::post('/hypothesis/reopen-all', 'reopenAllHypotheses')->name('hypothesis.reopenAllHypotheses');
    });

    // Ruta de prueba para verificar autenticación
    Route::get('/test-auth', function() {
        return response()->json([
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user' => auth()->user()
        ]);
    });

    // Condiciones iniciales del sistema
    Route::get('/initial-conditions', [\App\Http\Controllers\VariableController::class, 'getInitialConditions']);
    Route::put('/initial-conditions/{id}', [\App\Http\Controllers\VariableController::class, 'updateInitialCondition']);
    Route::post('/initial-conditions/close-all', [\App\Http\Controllers\VariableController::class, 'closeAllInitialConditions']);

    // Rutas de escenarios protegidas por autenticación
    Route::get('/scenarios', [\App\Http\Controllers\ScenariosController::class, 'index']);
    Route::put('/scenarios/{id}', [\App\Http\Controllers\ScenariosController::class, 'update']);
    Route::post('/scenarios', [\App\Http\Controllers\ScenariosController::class, 'store']);
    Route::get('/scenarios/strategic', [\App\Http\Controllers\StrategicScenarioController::class, 'show']);
    Route::put('/scenarios/strategic', [\App\Http\Controllers\StrategicScenarioController::class, 'update']);
    
    // Rutas de conclusiones protegidas por autenticación
    Route::controller(ConclusionController::class)->group(function(){
        Route::get('/conclusions', 'index')->name('conclusions.index');
        Route::post('/conclusions', 'store')->name('conclusions.store');
        Route::put('/conclusions/{id}', 'update')->name('conclusions.update');
        Route::delete('/conclusions/{id}', 'destroy')->name('conclusions.destroy');
        Route::put('/conclusions/{id}/state', 'updateState')->name('conclusions.updateState');
        Route::put('/conclusions/{id}/block', 'block')->name('conclusions.block');
        Route::put('/conclusions/{id}/unblock', 'unblock')->name('conclusions.unblock');
        Route::put('/conclusions/{id}/field', 'updateField')->name('conclusions.updateField');
        Route::post('/conclusions/reset-auto-increment', 'resetAutoIncrement')->name('conclusions.reset-auto-increment');
        Route::post('/conclusions/close-all', 'closeAll')->name('conclusions.closeAll');
    });

    // Rutas de notas protegidas por autenticación
    Route::controller(NoteController::class)->group(function(){
        Route::get('/notes', 'index')->name('notes.index');
        Route::get('/notes/latest', 'getLatest')->name('notes.latest');
        Route::post('/notes', 'store')->name('notes.store');
        Route::put('/notes/{id}', 'update')->name('notes.update');
        Route::delete('/notes/{id}', 'destroy')->name('notes.destroy');
    });

    // Rutas de traceability protegidas por autenticación
    Route::controller(TraceabilityController::class)->group(function(){
        Route::get('/traceability/user', 'getUserTraceability')->name('traceability.user');
        Route::post('/traceability/can-access', 'canAccessSection')->name('traceability.canAccess');
        Route::post('/traceability/mark-completed', 'markSectionCompleted')->name('traceability.markCompleted');
        Route::get('/traceability/available-sections', 'getAvailableSections')->name('traceability.availableSections');
        Route::post('/traceability/reverse-section-completed', 'reverseSectionCompleted')->middleware('auth');
        Route::post('/traceability/reset-edit-locks', [TraceabilityController::class, 'resetEditLocksFromSection'])->middleware('auth');
        Route::put('/traceability/tried', [App\Http\Controllers\TraceabilityController::class, 'updateTried']);
        Route::get('/traceability/tried', [App\Http\Controllers\TraceabilityController::class, 'getTried']);
        Route::put('/traceability/state', [App\Http\Controllers\TraceabilityController::class, 'updateState']);
        Route::get('/traceability/state', [App\Http\Controllers\TraceabilityController::class, 'getState']);
        
        // Nuevas rutas para sistema de múltiples rutas
        Route::post('/traceability/create-new-route', 'createNewRoute')->name('traceability.createNewRoute');
        Route::get('/traceability/current-route', 'getCurrentRoute')->name('traceability.getCurrentRoute');
        Route::get('/traceability/user-routes', 'getUserRoutes')->name('traceability.getUserRoutes');
        
        // Rutas para manejar el estado de la ruta actual
        Route::get('/traceability/current-route-state', 'getCurrentRouteState')->name('traceability.getCurrentRouteState');
        Route::put('/traceability/current-route-state', 'updateCurrentRouteState')->name('traceability.updateCurrentRouteState');
        Route::get('/traceability/section-closed/{section}', 'isSectionClosed')->name('traceability.isSectionClosed');
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
Route::get('/results/users', [\App\Http\Controllers\UserController::class, 'apiList'])->name('results.users');
Route::get('/results/users-by-route', [\App\Http\Controllers\UserController::class, 'apiListByRoute'])->name('results.usersByRoute');

    // Rutas del proxy de Ollama
    Route::controller(OllamaProxyController::class)->group(function(){
        Route::post('/ollama/generate', 'generate')->name('ollama.generate');
        Route::get('/ollama/models', 'models')->name('ollama.models');
    });

    // Rutas del proxy de ChatGPT
    Route::controller(ChatGPTProxyController::class)->group(function(){
    Route::post('/chatgpt/generate', 'generate')->name('chatgpt.generate');
    Route::get('/chatgpt/health', 'healthCheck')->name('chatgpt.health');
});

Route::controller(DeepSeekProxyController::class)->group(function(){
    Route::post('/deepseek/generate', 'generate')->name('deepseek.generate');
    Route::get('/deepseek/health', 'healthCheck')->name('deepseek.health');
});

Route::controller(GeminiProxyController::class)->group(function(){
    Route::post('/gemini/generate', 'generate')->name('gemini.generate');
    Route::get('/gemini/health', 'healthCheck')->name('gemini.health');
});

    // Rutas del proxy de OpenRouter
    Route::controller(OpenRouterProxyController::class)->group(function(){
        Route::post('/openrouter/generate', 'generate')->name('openrouter.generate');
        Route::get('/openrouter/health', 'healthCheck')->name('openrouter.health');
    });

    // Rutas de sectores económicos
    Route::get('/economic-sectors', [EconomicSectorController::class, 'index']);
    Route::get('/economic-sectors/{id}', [EconomicSectorController::class, 'show']);


