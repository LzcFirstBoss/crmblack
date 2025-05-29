    <?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Webhook\WebhookController;
    use App\Http\Controllers\Calendario\ApiController;
    use App\Models\Cliente\Cliente;


        Route::post('/receber-mensagem', [WebhookController::class, 'receberMensagem']);

        Route::prefix('calendario')->group(function () {
            Route::post('/disponiveis', [ApiController::class, 'horariosDisponiveis']);
            Route::post('/agendar', [ApiController::class, 'agendarReuniao']);
            Route::post('/reagendar', [ApiController::class, 'reagendarReuniao']);
            Route::post('/cancelar', [ApiController::class, 'cancelarReuniao']);
        });

        Route::get('/status/funis', [ApiController::class, 'listarFunis']);
        Route::post('/cliente/mudar-funil', [ApiController::class, 'mudarFunilCliente']);

        // Obter status do bot
        Route::get('/bot/status/{numero}', function ($numero) {
            $cliente = Cliente::where('telefoneWhatsapp', $numero . '@s.whatsapp.net')->first();
            return response()->json([
                'botativo' => (bool) ($cliente->botativo ?? false),
            ]);
        });

        // Alternar status do bot
        Route::post('/bot/toggle', function (Request $request) {
            $numero = $request->input('numero');

            if (!$numero) return response()->json(['erro' => 'Número não informado'], 400);

            $cliente = Cliente::where('telefoneWhatsapp', $numero . '@s.whatsapp.net')->first();

            if (!$cliente) return response()->json(['erro' => 'Cliente não encontrado'], 404);

            $cliente->botativo = !$cliente->botativo;
            $cliente->save();

            return response()->json([
                'botativo' => (bool) $cliente->botativo
            ]);
        });