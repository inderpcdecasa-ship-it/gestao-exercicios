<?php

namespace App\Filament\Admin\Pages;

use App\Models\Exercicio;
use App\Models\Execucao;
use App\Models\SessaoTreino;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

class ExecutarTreino extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static string $view = 'filament.admin.pages.executar-treino';
    protected static ?string $title = 'Modo Treino';

    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Exercicio> */
    public $exercicios = [];

    public $currentIndex = 0;
    public $currentSerie = 1;
    public $pesoUtilizado = 0;
    public $repeticoesFeitas = 10;

    /** @var int|null */
    #[Locked]
    public $sessaoId;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $novaFoto;

    public $treinoConcluido = false;
    public $mostrarCronometro = false;
    public $exercicioConcluido = false;

    /** Resumo do último treino deste exercício: ['data' => 'dd/mm', 'resumo' => '20 kg × 10 · ...'] */
    #[Locked]
    public $ultimoTreino = null;

    public function mount()
    {
        $mapaDias = [
            'Monday'    => 'segunda',
            'Tuesday'   => 'terca',
            'Wednesday' => 'quarta',
            'Thursday'  => 'quinta',
            'Friday'    => 'sexta',
            'Saturday'  => 'sabado',
            'Sunday'    => 'domingo',
        ];

        $diaIngles = Carbon::now()->englishDayOfWeek;
        $diaAtual = $mapaDias[$diaIngles] ?? 'segunda';

        $this->exercicios = Exercicio::where('dia_semana', $diaAtual)
            ->where('ativo', 1)
            ->orderBy('ordem')
            ->get();

        // Dia sem exercícios: não cria sessão nem marca nada como concluído
        if ($this->exercicios->isEmpty()) {
            return;
        }

        $sessao = SessaoTreino::firstOrCreate(
            ['data_treino' => Carbon::today()->toDateString()],
            ['concluido' => false, 'inicio' => now()]
        );
        $this->sessaoId = $sessao->id;
        $this->treinoConcluido = (bool) $sessao->concluido;

        $this->retomarProgresso();
        $this->verificarSeTodosEstaoConcluidos();
        $this->ajustarIndiceSeguro();
    }

    public function hasExercises()
    {
        return count($this->exercicios) > 0;
    }

    // Exercício do índice atual (lido direto da lista, sem depender de cache)
    protected function exercicioNoIndice()
    {
        return $this->exercicios[$this->currentIndex] ?? null;
    }

    // Usado pela Blade: $this->exercicioAtual
    public function getExercicioAtualProperty()
    {
        return $this->exercicioNoIndice();
    }

    // Usado pela Blade: $this->temSerieRegistrada (mostra o botão "Desfazer")
    public function getTemSerieRegistradaProperty(): bool
    {
        if (!$this->sessaoId) {
            return false;
        }

        return Execucao::where('sessao_id', $this->sessaoId)
            ->where('concluido', true)
            ->exists();
    }

    // Quantidade de séries concluídas por exercício, em uma única consulta
    protected function seriesConcluidasPorExercicio()
    {
        return Execucao::where('sessao_id', $this->sessaoId)
            ->where('concluido', true)
            ->selectRaw('exercicio_id, COUNT(*) as total')
            ->groupBy('exercicio_id')
            ->pluck('total', 'exercicio_id');
    }

    // Verifica se TODOS os exercícios já estão concluídos e, se sim, finaliza o treino
    public function verificarSeTodosEstaoConcluidos()
    {
        if (count($this->exercicios) === 0) {
            return false;
        }

        $feitas = $this->seriesConcluidasPorExercicio();

        foreach ($this->exercicios as $exercicio) {
            if ((int) ($feitas[$exercicio->id] ?? 0) < (int) $exercicio->series) {
                return false; // Ainda tem pelo menos um para fazer
            }
        }

        $sessao = SessaoTreino::find($this->sessaoId);
        if ($sessao && !$sessao->concluido) {
            $sessao->concluido = true;
            $sessao->fim = now();
            $sessao->save();
        }

        $this->treinoConcluido = true;
        $this->exercicioConcluido = true;

        return true;
    }

    // Vai para o próximo exercício com séries pendentes (dando a volta na lista)
    protected function irParaProximoPendente(): void
    {
        $total = count($this->exercicios);
        if ($total === 0) return;

        $feitas = $this->seriesConcluidasPorExercicio();

        for ($i = 1; $i <= $total; $i++) {
            $indice = ($this->currentIndex + $i) % $total;
            $ex = $this->exercicios[$indice];

            if ((int) ($feitas[$ex->id] ?? 0) < (int) $ex->series) {
                $this->currentIndex = $indice;
                $this->carregarProgressoExercicioAtual();
                return;
            }
        }
    }

    public function exercicioAnterior()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
            $this->carregarProgressoExercicioAtual();
        }
    }

    public function proximoExercicio()
    {
        if ($this->currentIndex < count($this->exercicios) - 1) {
            $this->currentIndex++;
            $this->carregarProgressoExercicioAtual();

            // Se o próximo JÁ está concluído, pula até achar um que não esteja
            while ($this->exercicioConcluido && $this->currentIndex < count($this->exercicios) - 1) {
                $this->currentIndex++;
                $this->carregarProgressoExercicioAtual();
            }

            // Se chegou no último e ele também está concluído → verifica se o treino todo terminou
            if ($this->exercicioConcluido && $this->currentIndex === count($this->exercicios) - 1) {
                $this->verificarSeTodosEstaoConcluidos();
            }
        }
    }

    public function carregarProgressoExercicioAtual()
    {
        $exercicio = $this->exercicioNoIndice();
        if (!$exercicio) return;

        $this->mostrarCronometro = false;
        $this->exercicioConcluido = false;

        // Carga da última vez que este exercício foi feito
        $this->carregarUltimoTreino($exercicio);

        // Conta só as concluídas
        $qtdSeriesConcluidas = Execucao::where('sessao_id', $this->sessaoId)
            ->where('exercicio_id', $exercicio->id)
            ->where('concluido', true)
            ->count();

        if ($qtdSeriesConcluidas >= (int) $exercicio->series) {
            $this->exercicioConcluido = true;
            $this->currentSerie = (int) $exercicio->series;

            $ultima = Execucao::where('sessao_id', $this->sessaoId)
                ->where('exercicio_id', $exercicio->id)
                ->latest('serie')
                ->first();
            if ($ultima) {
                $this->pesoUtilizado = $ultima->peso_utilizado ?? $exercicio->peso_padrao ?? 0;
                $this->repeticoesFeitas = $ultima->repeticoes_feitas ?? $exercicio->repeticoes ?? 10;
            }
            return;
        }

        $this->currentSerie = $qtdSeriesConcluidas + 1;

        $ultimaExecucao = Execucao::where('sessao_id', $this->sessaoId)
            ->where('exercicio_id', $exercicio->id)
            ->latest('serie')
            ->first();

        if ($ultimaExecucao) {
            $this->pesoUtilizado = $ultimaExecucao->peso_utilizado ?? $exercicio->peso_padrao ?? 0;
            $this->repeticoesFeitas = $ultimaExecucao->repeticoes_feitas ?? $exercicio->repeticoes ?? 10;
        } else {
            $this->pesoUtilizado = $exercicio->peso_padrao ?? 0;
            $this->repeticoesFeitas = $exercicio->repeticoes ?? 10;
        }
    }

    // Busca o resumo do último treino (outra sessão) deste exercício
    protected function carregarUltimoTreino($exercicio): void
    {
        $this->ultimoTreino = null;

        $sessaoAnteriorId = Execucao::where('exercicio_id', $exercicio->id)
            ->where('sessao_id', '!=', $this->sessaoId)
            ->where('concluido', true)
            ->max('sessao_id');

        if (!$sessaoAnteriorId) {
            return;
        }

        $series = Execucao::where('sessao_id', $sessaoAnteriorId)
            ->where('exercicio_id', $exercicio->id)
            ->where('concluido', true)
            ->orderBy('serie')
            ->get(['serie', 'peso_utilizado', 'repeticoes_feitas']);

        if ($series->isEmpty()) {
            return;
        }

        $sessao = SessaoTreino::find($sessaoAnteriorId);
        $data = ($sessao && $sessao->data_treino)
            ? Carbon::parse($sessao->data_treino)->format('d/m')
            : null;

        $this->ultimoTreino = [
            'data'   => $data,
            'resumo' => $series
                ->map(fn ($s) => $this->formatarCarga($s->peso_utilizado) . ' kg × ' . (int) $s->repeticoes_feitas)
                ->implode(' · '),
        ];
    }

    // 20.0 → "20" | 22.5 → "22,5"
    protected function formatarCarga($valor): string
    {
        return rtrim(rtrim(number_format((float) $valor, 2, ',', ''), '0'), ',');
    }

    public function retomarProgresso()
    {
        if ($this->treinoConcluido) {
            $this->carregarProgressoExercicioAtual();
            return;
        }

        $ultimaExecucao = Execucao::where('sessao_id', $this->sessaoId)
            ->latest('id')
            ->first();

        if ($ultimaExecucao && $ultimaExecucao->exercicio) {
            $posicao = $this->exercicios->search(fn($ex) => $ex->id === $ultimaExecucao->exercicio_id);

            if ($posicao !== false) {
                $this->currentIndex = $posicao;
                $exercicioAtual = $this->exercicios[$this->currentIndex] ?? null;

                if ($exercicioAtual) {
                    $qtdFeitas = Execucao::where('sessao_id', $this->sessaoId)
                        ->where('exercicio_id', $exercicioAtual->id)
                        ->where('concluido', true)
                        ->count();

                    if ($qtdFeitas >= (int) $exercicioAtual->series) {
                        $this->currentIndex++;
                    } else {
                        $this->currentSerie = $qtdFeitas + 1;
                    }
                }
            }
        }

        $this->ajustarIndiceSeguro();
        $this->carregarProgressoExercicioAtual();
    }

    public function ajustarIndiceSeguro()
    {
        $total = count($this->exercicios);

        if ($total === 0) {
            $this->currentIndex = 0;
            $this->currentSerie = 1;
            return;
        }

        if ($this->currentIndex >= $total) {
            $this->currentIndex = $total - 1;
        }

        if ($this->currentIndex < 0) {
            $this->currentIndex = 0;
        }
    }

    public function registrarSerie()
    {
        if ($this->treinoConcluido || $this->exercicioConcluido) return;

        $exercicio = $this->exercicioNoIndice();
        if (!$exercicio) return;

        $totalSeries = (int) $exercicio->series;
        if ((int) $this->currentSerie > $totalSeries) return;

        // Limpa os valores digitados (aceita vírgula, evita vazio/negativo)
        $peso = max(0, (float) str_replace(',', '.', (string) $this->pesoUtilizado));
        $reps = max(1, (int) $this->repeticoesFeitas);

        Execucao::updateOrCreate(
            [
                'sessao_id'    => $this->sessaoId,
                'exercicio_id' => $exercicio->id,
                'serie'        => $this->currentSerie,
            ],
            [
                'repeticoes_feitas' => $reps,
                'peso_utilizado'    => $peso,
                'concluido'         => true,
            ]
        );

        // Ainda há séries neste exercício
        if ((int) $this->currentSerie < $totalSeries) {
            $this->currentSerie++;
            $this->mostrarCronometro = true;
            $this->dispatch('serie-registrada');
            return;
        }

        // Terminou todas as séries deste exercício
        $this->mostrarCronometro = false;

        if ($this->verificarSeTodosEstaoConcluidos()) {
            $this->dispatch('treino-concluido');
            return;
        }

        $this->irParaProximoPendente();
        $this->dispatch('serie-registrada');
    }

    // Desfaz a última série registrada na sessão (mesmo que de outro exercício)
    public function desfazerUltimaSerie()
    {
        if (!$this->sessaoId) return;

        $ultima = Execucao::where('sessao_id', $this->sessaoId)
            ->where('concluido', true)
            ->latest('id')
            ->first();

        if (!$ultima) return;

        $pesoDesfeito = $ultima->peso_utilizado;
        $repsDesfeitas = $ultima->repeticoes_feitas;

        // Volta para o exercício dessa série
        $posicao = $this->exercicios->search(fn($ex) => (int) $ex->id === (int) $ultima->exercicio_id);
        if ($posicao !== false) {
            $this->currentIndex = $posicao;
        }

        $ultima->delete();

        // Se o treino estava concluído, reabre
        $sessao = SessaoTreino::find($this->sessaoId);
        if ($sessao && $sessao->concluido) {
            $sessao->concluido = false;
            $sessao->fim = null;
            $sessao->save();
        }
        $this->treinoConcluido = false;

        $this->carregarProgressoExercicioAtual();

        // Deixa nos campos os valores da série desfeita, para corrigir e registrar de novo
        $this->pesoUtilizado = $pesoDesfeito ?? $this->pesoUtilizado;
        $this->repeticoesFeitas = $repsDesfeitas ?? $this->repeticoesFeitas;

        $this->dispatch('serie-desfeita');
    }

    public function reiniciarExercicioAtual()
    {
        $exercicio = $this->exercicioNoIndice();
        if (!$exercicio) return;

        Execucao::where('sessao_id', $this->sessaoId)
            ->where('exercicio_id', $exercicio->id)
            ->delete();

        $sessao = SessaoTreino::find($this->sessaoId);
        if ($sessao) {
            $sessao->concluido = false;
            $sessao->fim = null;
            $sessao->save();
        }
        $this->treinoConcluido = false;
        $this->exercicioConcluido = false;
        $this->mostrarCronometro = false;

        $this->currentSerie = 1;
        $this->pesoUtilizado = $exercicio->peso_padrao ?? 0;
        $this->repeticoesFeitas = $exercicio->repeticoes ?? 10;
    }

    public function salvarFoto()
    {
        $this->validate([
            'novaFoto' => 'required|image|max:10240',
        ]);

        $exercicioAtual = $this->exercicioNoIndice();
        if (!$exercicioAtual) return;

        $exercicioModel = Exercicio::find($exercicioAtual->id);
        if (!$exercicioModel) return;

        $caminho = $this->novaFoto->store('aparelhos', 'public');

        // Apaga a foto antiga, se existir
        if ($exercicioModel->foto_aparelho) {
            Storage::disk('public')->delete($exercicioModel->foto_aparelho);
        }

        $exercicioModel->foto_aparelho = $caminho;
        $exercicioModel->save();

        $this->novaFoto = null;

        // Recarrega os exercícios para a foto nova aparecer já nesta resposta
        $this->exercicios = $this->exercicios->fresh();
    }
}