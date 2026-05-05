<?php

use App\Http\Controllers\API\BitacoraController;
use App\Http\Controllers\API\CursoController;
use App\Http\Controllers\API\EstudianteController;
use App\Http\Controllers\API\GestionController;
use App\Http\Controllers\API\UsuarioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

Route::post("/login", [AuthController::class, "login"]);
Route::post("/logout", [AuthController::class, "logout"])->middleware("auth:sanctum");
Route::get("/user", [AuthController::class, "user"])->middleware("auth:sanctum");

Route::middleware('auth:sanctum')->group(function () {

    // CRUD Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);

    // Estudiantes
    Route::get('/estudiantes', [EstudianteController::class, 'index']);
    Route::get('/estudiantes/{id}', [EstudianteController::class, 'show']);
    Route::post('/estudiantes', [EstudianteController::class, 'store']);
    Route::put('/estudiantes/{id}', [EstudianteController::class, 'update']);
    Route::put('/estudiantes/{id}/estado', [EstudianteController::class, 'cambiarEstadoMatricula']);
    Route::delete('/estudiantes/{id}', [EstudianteController::class, 'destroy']);


    // Utilidades
    Route::get('/roles', [UsuarioController::class, 'roles']);
    Route::get('/permisos', [UsuarioController::class, 'permisos']);
    Route::get('/usuarios/{id}/permisos', [UsuarioController::class, 'getPermisosUsuario']);
    Route::post('/usuarios/{id}/permisos', [UsuarioController::class, 'asignarPermisos']);

    // Bitácora
    Route::get('/bitacora', [BitacoraController::class, 'index']);
    Route::get('/bitacora/{id}', [BitacoraController::class, 'show']);
    Route::get('/bitacora/exportar/csv', [BitacoraController::class, 'exportar']);

    // Gestiones (años lectivos)
    Route::get('/gestiones', [GestionController::class, 'index']);

    // Cursos
    Route::get('/cursos', [CursoController::class, 'index']);
});