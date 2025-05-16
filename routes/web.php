    <?php
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Auth\AuthController;
    use App\Http\Controllers\Kanban\KanbanController;
    use App\Http\Controllers\Kanban\StatusController;
    use App\Http\Controllers\Kanban\EvolutionController;
    use App\Http\Controllers\Bots\BotController;
    use App\Http\Controllers\Calendario\EventoController;
    use App\Http\Controllers\Conversar\ConversasController;
    use App\Http\Controllers\Dashboard\DashboardController;
    use App\Http\Controllers\Disparo\DisparoController;


    //rotas de login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    //rotas padrão
    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    //Kanban - crm
    Route::middleware('auth')->group(function () {
        Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
        Route::post('/kanban/atualizar-status', [KanbanController::class, 'atualizarStatus'])->name('kanban.atualizar-status');
        Route::get('/kanban/parcial', [KanbanController::class, 'parcial'])->name('kanban.parcial');
        Route::post('/kanban/status', [StatusController::class, 'store']);
        Route::delete('/kanban/status/{id}', [StatusController::class, 'destroy']);
        Route::put('/kanban/status/{id}/cor', [StatusController::class, 'atualizarCor']);
    });

    // configs whatsapp
    Route::middleware('auth')->group(function () {
        // Conectar/desconectar
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

    // API EVOLUTION ENVIOS DE MENSAGENS
    Route::middleware('auth')->group(function () {
        Route::post('/kanban/enviar-mensagem', [EvolutionController::class, 'enviarMensagem'])->name('kanban.enviar-mensagem');
        Route::post('/api/evolution/enviar-audio-base64', [EvolutionController::class, 'audioEnviar'])->name('teste.evolution.audio.enviar');
        Route::post('/api/evolution/enviar-midia', [EvolutionController::class, 'enviarMidia'])->name('evolution.enviarMidia');
    });

    Route::get('/disparo', [DisparoController::class, 'criar'])->name('disparo.criar');
    Route::post('/disparo/enviar', [DisparoController::class, 'enviar'])->name('disparo.enviar');
