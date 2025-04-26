<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhook\WebhookController;
use App\Http\Controllers\Calendario\ApiController;

Route::post('/receber-mensagem', [WebhookController::class, 'receberMensagem']);

Route::prefix('calendario')->group(function () {
    Route::post('/disponiveis', [ApiController::class, 'horariosDisponiveis']);
    Route::post('/agendar', [ApiController::class, 'agendarReuniao']);
});
