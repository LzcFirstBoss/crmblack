<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\webhook\WebhookController;

Route::post('/receber-mensagem', [WebhookController::class, 'receberMensagem']);
