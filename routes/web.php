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
    use App\Http\Controllers\Notificacao\NotificacaoController;
    use App\Http\Controllers\Leads\LeadsController;


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
        Route::get('/conversar-parcial-item/{numero}', [ConversasController::class, 'parcialItem']);
        Route::post('/zerar-mensagens-novas/{numero}', [ConversasController::class, 'zerarMensagensNovas'])->name('conversas.zerarMensagensNovas');
    });

    // API EVOLUTION ENVIOS DE MENSAGENS
    Route::middleware('auth')->group(function () {
        Route::post('/kanban/enviar-mensagem', [EvolutionController::class, 'enviarMensagem'])->name('kanban.enviar-mensagem');
        Route::post('/api/evolution/enviar-audio-base64', [EvolutionController::class, 'audioEnviar'])->name('teste.evolution.audio.enviar');
        Route::post('/api/evolution/enviar-midia', [EvolutionController::class, 'enviarMidia'])->name('evolution.enviarMidia');
        Route::delete('/mensagem/apagar', [EvolutionController::class, 'apagarMensagemParaTodos'])->name('mensagem.apagar');
        Route::post('/mensagem/editar', [EvolutionController::class, 'editarMensagem'])->name('mensagem.editar');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/disparo', [DisparoController::class, 'criar'])->name('disparo.criar');
        Route::post('/disparo/enviar', [DisparoController::class, 'enviar'])->name('disparo.enviar');
        Route::get('/disparo/{id}', [DisparoController::class, 'show'])->name('disparo.show');
        Route::post('/disparo/{id}/cancelar', [DisparoController::class, 'cancelar'])->name('disparo.cancelar');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/notificacoes/listar', [NotificacaoController::class, 'listar']);
        Route::post('/notificacoes/marcar-todas-como-lidas', [NotificacaoController::class, 'marcarTodasComoLidas']);
        Route::delete('/notificacoes/{id}', [NotificacaoController::class, 'deletar'])->middleware('auth');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/leads', [LeadsController::class, 'index'])->name('leads.index');
        Route::get('/leads/criar', [LeadsController::class, 'create'])->name('leads.create');
        Route::post('/leads', [LeadsController::class, 'store'])->name('leads.store');
        Route::get('/leads/{id}', [LeadsController::class, 'show'])->name('leads.show');
        Route::get('/leads/{id}/editar', [LeadsController::class, 'edit'])->name('leads.edit');
        Route::put('/leads/{id}', [LeadsController::class, 'update'])->name('leads.update');
        Route::delete('/leads/{id}', [LeadsController::class, 'destroy'])->name('leads.destroy');
    });
