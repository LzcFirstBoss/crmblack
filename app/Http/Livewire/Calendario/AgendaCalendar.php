<?php

namespace App\Http\Livewire\Calendario;

use App\Models\Calendario\Evento;
use Carbon\Carbon;
use Livewire\Component;

class AgendaCalendar extends Component
{
    public $startsAt;
    public $endsAt;
    public $gridStartsAt;
    public $totalDays = 42;

    public $showCriarModal = false;
    public $showDetalhesModal = false;
    public $modoEdicao = false;

    public $showModal = false;
    public $modoCriar = false;


    public $titulo, $descricao, $inicio, $fim;
    public $eventoSelecionado;

    public $numerocliente;

public function mount()
{
    $this->startsAt = Carbon::now()->startOfWeek(Carbon::SUNDAY);
    $this->gridStartsAt = $this->startsAt->copy();
    $this->endsAt = $this->startsAt->copy()->endOfWeek(Carbon::SATURDAY);

    $this->totalDays = 7;
}

public function goToPreviousWeek()
{
    $this->startsAt = $this->startsAt->copy()->subWeek();
    $this->gridStartsAt = $this->startsAt->copy();
    $this->endsAt = $this->startsAt->copy()->endOfWeek(Carbon::SATURDAY);
}

public function goToNextWeek()
{
    $this->startsAt = $this->startsAt->copy()->addWeek();
    $this->gridStartsAt = $this->startsAt->copy();
    $this->endsAt = $this->startsAt->copy()->endOfWeek(Carbon::SATURDAY);
}


    public function events()
    {
        return Evento::query()
            ->whereDate('inicio', '>=', $this->gridStartsAt)
            ->whereDate('fim', '<=', $this->gridStartsAt->copy()->addDays($this->totalDays - 1))
            ->get();
    }

    // ---------- MODAL DE CRIAÇÃO ----------
    public function abrirCriacao()
    {
        $this->reset(['titulo', 'descricao', 'inicio', 'fim', 'eventoSelecionado']);
        $this->modoCriar = true;
        $this->modoEdicao = false;
        $this->showModal = true;
    }

    public function salvarEvento()
    {
        $this->validate([
            'titulo' => 'required|string|max:255',
            'inicio' => 'required|date',
            'fim' => 'required|date|after_or_equal:inicio',
            'numerocliente' => 'required|string',
        ]);
    
        Evento::create([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'inicio' => $this->inicio,
            'fim' => $this->fim,
            'numerocliente' => $this->numerocliente,
        ]);
    
        $this->fecharModal();
    }

    // ---------- MODAL DE DETALHES ----------
    public function abrirDetalhes($id)
    {
        $this->eventoSelecionado = Evento::findOrFail($id);
        $this->modoCriar = false;
        $this->modoEdicao = false;
        $this->showModal = true;
    }
    
    public function abrirEdicao()
    {
        if ($this->eventoSelecionado) {
            $this->eventoSelecionado = [
                'id' => $this->eventoSelecionado->id,
                'titulo' => $this->eventoSelecionado->titulo,
                'descricao' => $this->eventoSelecionado->descricao,
                'inicio' => Carbon::parse($this->eventoSelecionado->inicio)->format('Y-m-d\TH:i'),
                'fim' => Carbon::parse($this->eventoSelecionado->fim)->format('Y-m-d\TH:i'),
                'numerocliente' => $this->eventoSelecionado->numerocliente,
            ];
        }
    
        $this->modoEdicao = true;
    }

    public function atualizarEvento()
    {
        $this->validate([
            'eventoSelecionado.titulo' => 'required|string|max:255',
            'eventoSelecionado.inicio' => 'required|date',
            'eventoSelecionado.fim' => 'required|date|after_or_equal:eventoSelecionado.inicio',
        ]);
    
        Evento::find($this->eventoSelecionado['id'])?->update([
            'titulo' => $this->eventoSelecionado['titulo'],
            'descricao' => $this->eventoSelecionado['descricao'],
            'inicio' => $this->eventoSelecionado['inicio'],
            'fim' => $this->eventoSelecionado['fim'],
            'numerocliente' => $this->eventoSelecionado['numerocliente'],
        ]);
    
        $this->fecharModal();
    }
    

    public function excluirEvento()
    {
        if ($this->eventoSelecionado && isset($this->eventoSelecionado['id'])) {
            Evento::destroy($this->eventoSelecionado['id']);
        }
    
        $this->reset(['eventoSelecionado']); // limpa a variável completamente
        $this->fecharModal(); // fecha o modal
    }
    

    public function render()
    {
        return view('livewire.calendario.index', [
            'startsAt' => $this->startsAt,
            'gridStartsAt' => $this->gridStartsAt,
            'totalDays' => $this->totalDays,
        ]);
    }

    public function fecharModal()
    {
        $this->showModal = false;
        $this->modoCriar = false;
        $this->modoEdicao = false;
    }
}
