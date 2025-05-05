    <?php
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Auth\AuthController;
    use App\Http\Controllers\Kanban\KanbanController;
    use App\Http\Controllers\Kanban\StatusController;
    use App\Http\Controllers\Kanban\EvolutionController;
    use App\Http\Controllers\Historico\HistoricoConversaController;
    use App\Http\Controllers\Bots\BotController;
    use App\Http\Controllers\Calendario\EventoController;
    use App\Http\Controllers\Conversar\ConversasController;
    //rotas de login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    //rotas padrão
    Route::middleware('auth')->group(function () {
        Route::get('/', function () {return view('user.index');});
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', function () {return view('user.index');})->name('dashboard');
    });

    //Kanban - crm
    Route::middleware('auth')->group(function () {
        Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
        Route::post('/kanban/atualizar-status', [KanbanController::class, 'atualizarStatus'])->name('kanban.atualizar-status');
        Route::get('/kanban/parcial', [KanbanController::class, 'parcial'])->name('kanban.parcial');
        Route::post('/kanban/status', [StatusController::class, 'store']);
        Route::delete('/kanban/status/{id}', [StatusController::class, 'destroy']);
        Route::put('/kanban/status/{id}/cor', [KanbanController::class, 'atualizarCor']);
    });

    //Historico de conversas abertas
    Route::middleware('auth')->group(function () {
        // Página com o histórico completo de um número
        Route::get('/kanban/historico/{numero}', [HistoricoConversaController::class, 'historico'])->name('kanban.historico');

        // Atualização em tempo real via AJAX
        Route::get('/kanban/historico/{numero}/atualizar', [HistoricoConversaController::class, 'atualizarHistorico']);

        // Envia mensagem pela api evolution
        Route::post('/kanban/enviar-mensagem', [EvolutionController::class, 'enviarMensagem'])->name('kanban.enviar-mensagem');

        // Desativar/ativar bot
        Route::get('/cliente/bot/alternar', [HistoricoConversaController::class, 'alternar'])->name('cliente.alternarBot');
    });

    // configs whatsapp
    Route::middleware('auth')->group(function () {
        //conectar/desconectar
        Route::get('/config', [EvolutionController::class, 'painelWhatsapp'])->name('evolution.qrcpde');
        Route::get('/evolution/conectar', [EvolutionController::class, 'conectarInstancia'])->name('evolution.conectar');
        Route::get('/evolution/status', [EvolutionController::class, 'verificarStatus'])->name('evolution.status.check');
        Route::get('/painel/whatsapp', [EvolutionController::class, 'painelWhatsapp'])->name('painel.whatsapp');
        Route::post('/evolution/logout', [EvolutionController::class, 'logout'])->name('evolution.logout');

        //criar bots
        Route::post('/bots/store', [BotController::class, 'store'])->name('bots.store');
        // Deletar bots
        Route::delete('/bots/{id}', [BotController::class, 'destroy'])->name('bots.destroy');

        Route::put('/bots/update/{id}', [BotController::class, 'update'])->name('bots.update');
    });

    // Calendario/agenada
    Route::middleware('auth')->group(function () {
        Route::view('/calendario', 'calendario.index')->name('agenda.index');
        Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
    });

    // Conversar
    Route::middleware('auth')->group(function () {
        Route::get('/conversar', [ConversasController::class, 'index'])->name('conversas.index');
        Route::get('/conversar/{numero}', [ConversasController::class, 'historico'])->name('conversas.historico');
        Route::get('/conversar-parcial', [ConversasController::class, 'parcial'])->name('conversas.parcial');
        Route::post('/zerar-mensagens-novas/{numero}', [ConversasController::class, 'zerarMensagensNovas'])->name('conversas.zerarMensagensNovas');
    });


    Route::post('/api/evolution/enviar-audio-base64', [EvolutionController::class, 'audioEnviar'])->name('teste.evolution.audio.enviar');
    
