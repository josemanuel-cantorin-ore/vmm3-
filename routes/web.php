<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\AulaController;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ReporteAsistenciaController;
use App\Http\Controllers\ReporteTareaController;
use App\Http\Controllers\ReporteRendimientoController;

// 1. Redirección inicial
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 2. Autenticación (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// 3. Cambio de idioma
Route::get('/idioma/{lang}', function ($lang) {
    if (in_array($lang, ['es', 'en'])) {
        Session::put('locale', $lang);
    }
    return redirect()->back();
})->name('cambiar.idioma');

// 4. Dashboard Principal
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// 5. Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ⚠️ IMPORTANTE: NO EXISTE RUTA GET /tareas. Las tareas viven DENTRO del aula.

// 6. Grupo de rutas administrativas protegidas
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    
    // Vistas estáticas del template AdminKit
    Route::view('/analytics', 'dashb-index')->name('index');
    Route::get('/profile', [AulaController::class, 'index'])->name('profile');
    Route::get('/aula/{id}/control', [AulaController::class, 'control'])->name('control-aulas');
    Route::view('/profile1', 'dashb-profile')->name('profile1');
    Route::view('/sign-in', 'dashb-pages-sign-in')->name('sign-in');
    Route::view('/sign-up', 'dashb-pages-sign-up')->name('sign-up');
    Route::view('/blank', 'dashb-pages-blank')->name('blank');
    Route::view('/buttons', 'dashb-ui-buttons')->name('buttons');
    Route::view('/forms', 'dashb-ui-forms')->name('forms');
    Route::view('/cards', 'dashb-ui-cards')->name('cards');
    Route::view('/typography','dashb-ui-typography')->name('typography');
    Route::view('/icons', 'dashb-icons-feather')->name('icons');
    Route::view('/charts', 'dashb-chartjs')->name('charts');
    Route::view('/maps', 'dashb-maps-google')->name('maps');
    Route::view('/upgrade', 'dashb-upgrade-to-pro')->name('upgrade');

    // Acciones funcionales integradas en el aula
    Route::post('/aulas/guardar-asistencia', [AsistenciaController::class, 'guardarAsistencia'])->name('guardar.asistencia');

    Route::prefix('/aula/{asignacion}/reportes')->name('reportes.')->group(function () {
        Route::get('/asistencia', [ReporteAsistenciaController::class, 'index'])->name('asistencia');
        Route::post('/asistencia/exportar', [ReporteAsistenciaController::class, 'exportar'])
            ->name('asistencia.exportar');

        Route::get('/tareas', [ReporteTareaController::class, 'index'])->name('tareas');
        Route::post('/tareas/exportar', [ReporteTareaController::class, 'exportar'])
            ->name('tareas.exportar');

        Route::get('/rendimiento', [ReporteRendimientoController::class, 'index'])->name('rendimiento');
        Route::post('/rendimiento/exportar', [ReporteRendimientoController::class, 'exportar'])
            ->name('rendimiento.exportar');
    });
    
    // Gestión de tareas, vinculada a aulas asignadas al docente autenticado.
    Route::get('/aula/{id}/tareas/create', [TareaController::class, 'create'])->name('tareas.create');
    Route::post('/aula/{id}/tareas', [TareaController::class, 'store'])->name('tareas.store');
    Route::get('/aula/{id}/tareas/papelera', [TareaController::class, 'papelera'])->name('tareas.papelera');
    Route::get('/tareas/{tarea}/edit', [TareaController::class, 'edit'])->name('tareas.edit');
    Route::put('/tareas/{tarea}', [TareaController::class, 'update'])->name('tareas.update');
    Route::delete('/tareas/{tarea}', [TareaController::class, 'destroy'])->name('tareas.destroy');
    Route::post('/tareas/{tarea}/restore', [TareaController::class, 'restore'])->name('tareas.restore');
    Route::get('/tareas/{tarea}/calificar', [TareaController::class, 'calificar'])->name('tareas.calificar');
    Route::post('/tareas/{tarea}/calificaciones', [TareaController::class, 'guardarCalificacion'])->name('tareas.guardarCalificacion');
});