    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Auth\AuthController;
    use App\Http\Controllers\Kanban\KanbanController;
    use App\Http\Controllers\Kanban\StatusController;
    use App\Http\Controllers\Kanban\EvolutionController;
    use App\Http\Controllers\Historico\HistoricoConversaController;

    //rotas de login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    //rotas com proteção de login
    Route::middleware('auth')->group(function () {
        Route::get('/', function() {return view('user.index');});
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', function () { return view('user.index'); })->name('dashboard');
    });

    //Kanban
    Route::middleware('auth')->group(function(){
        Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
        Route::post('/kanban/atualizar-status', [KanbanController::class, 'atualizarStatus'])->name('kanban.atualizar-status');
        Route::get('/kanban/parcial', [KanbanController::class, 'parcial'])->name('kanban.parcial');
        Route::post('/kanban/status', [StatusController::class, 'store']);
        Route::delete('/kanban/status/{id}', [StatusController::class, 'destroy']);
        Route::put('/kanban/status/{id}/cor', [KanbanController::class, 'atualizarCor']);
    });

    //Historico de conversas abertas
    Route::middleware('auth')->group(function(){

        // Página com o histórico completo de um número
        Route::get('/kanban/historico/{numero}', [HistoricoConversaController::class, 'historico'])->name('kanban.historico');

        // Atualização em tempo real via AJAX
        Route::get('/kanban/historico/{numero}/atualizar', [HistoricoConversaController::class, 'atualizarHistorico']);
        Route::post('/kanban/enviar-mensagem', [EvolutionController::class, 'enviarMensagem'])->name('kanban.enviar-mensagem');

        // Desativar/ativar bot
        Route::get('/cliente/bot/alternar', [HistoricoConversaController::class, 'alternar'])->name('cliente.alternarBot');
    });


