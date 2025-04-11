<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Kanban\KanbanController;
use App\Http\Controllers\Kanban\StatusController;

//rotas de login
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

//rotas com proteção de login
Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () { return view('user.index'); })->name('dashboard');
    Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::post('/kanban/atualizar-status', [KanbanController::class, 'atualizarStatus'])->name('kanban.atualizar-status');
    Route::get('/kanban/parcial', [KanbanController::class, 'parcial'])->name('kanban.parcial');
    Route::post('/kanban/status', [StatusController::class, 'store']);
    Route::delete('/kanban/status/{id}', [StatusController::class, 'destroy']);
});





