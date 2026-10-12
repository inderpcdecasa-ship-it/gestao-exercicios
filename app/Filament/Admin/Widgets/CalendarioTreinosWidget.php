<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Execucao;
use App\Models\SessaoTreino;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class CalendarioTreinosWidget extends Widget
{
    protected static string $view = 'filament.admin.widgets.calendario-treinos-widget';

    // Ocupa a largura total no painel do Filament
    protected int | string | array $columnSpan = 'full';

    public $currentMonth;
    public $currentYear;
    public $diasTreinadosNoMes = [];

    public $totalGeral = 0;
    public $totalMensal = 0;
    public $totalSemanal = 0;
    public $sequenciaDias = 0;

    public function mount()
    {
        $hoje = Carbon::now();
        $this->currentMonth = $hoje->month;
        $this->currentYear = $hoje->year;

        $this->calcularEstatisticas();
    }

    // Sessões que contam como treino: marcadas como concluídas E com pelo menos
    // uma série realmente registrada.
    protected function sessoesConcluidas()
    {
        return SessaoTreino::query()
            ->where('concluido', true)
            ->whereIn('id', Execucao::query()->where('concluido', true)->select('sessao_id'));
    }

    public function calcularEstatisticas()
    {
        $this->calcularEstatisticasGerais();
        $this->carregarDiasDoMes();
    }

    public function calcularEstatisticasGerais()
    {
        $hoje = Carbon::now();

        // 1. Total geral de treinos concluídos
        $this->totalGeral = $this->sessoesConcluidas()->count();

        // 2. Total semanal (domingo a sábado)
        $this->totalSemanal = $this->sessoesConcluidas()
            ->whereDate('data_treino', '>=', $hoje->copy()->startOfWeek(Carbon::SUNDAY)->toDateString())
            ->whereDate('data_treino', '<=', $hoje->copy()->endOfWeek(Carbon::SATURDAY)->toDateString())
            ->count();

        // 3. Sequência atual (streak)
        $this->sequenciaDias = $this->calcularSequencia();
    }

    public function carregarDiasDoMes()
    {
        $inicio = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        $this->diasTreinadosNoMes = $this->sessoesConcluidas()
            ->whereDate('data_treino', '>=', $inicio->toDateString())
            ->whereDate('data_treino', '<=', $fim->toDateString())
            ->pluck('data_treino')
            ->map(fn ($data) => Carbon::parse($data)->day)
            ->unique()
            ->values()
            ->toArray();

        $this->totalMensal = count($this->diasTreinadosNoMes);
    }

    // Método para verificar se um dia é feriado nacional (Brasil)
    protected function ehFeriado(Carbon $dia): bool
    {
        $ano = $dia->year;
        $dataStr = $dia->toDateString();

        // Feriados Nacionais Fixos
        $feriadosFixos = [
            "{$ano}-01-01", // Confraternização Universal
            "{$ano}-04-21", // Tiradentes
            "{$ano}-05-01", // Dia do Trabalhador
            "{$ano}-09-07", // Independência do Brasil
            "{$ano}-10-12", // Nossa Senhora Aparecida
            "{$ano}-11-02", // Finados
            "{$ano}-11-15", // Proclamação da República
            "{$ano}-12-25", // Natal
        ];

        if (in_array($dataStr, $feriadosFixos)) {
            return true;
        }

        // Feriados Móveis baseados na Páscoa
        $pascoaTimestamp = easter_date($ano);
        $pascoa = Carbon::createFromTimestamp($pascoaTimestamp);

        $carnaval = $pascoa->copy()->subDays(47)->toDateString();
        $sextaSanta = $pascoa->copy()->subDays(2)->toDateString();
        $corpusChristi = $pascoa->copy()->addDays(60)->toDateString();

        $feriadosMoveis = [
            $carnaval,
            $sextaSanta,
            $corpusChristi,
        ];

        return in_array($dataStr, $feriadosMoveis);
    }

    protected function calcularSequencia(): int
    {
        $diasComTreino = $this->sessoesConcluidas()
            ->pluck('data_treino')
            ->map(fn ($data) => Carbon::parse($data)->toDateString())
            ->unique()
            ->flip()
            ->all();

        $dia = Carbon::today();

        if (!isset($diasComTreino[$dia->toDateString()])) {
            $dia->subDay();
        }

        $sequencia = 0;

        while (true) {
            if (isset($diasComTreino[$dia->toDateString()])) {
                $sequencia++;
            } elseif ($dia->isWeekday() && !$this->ehFeriado($dia)) {
                // Dia útil comum (segunda a sexta, não feriado) sem treino: quebra
                break;
            }
            // Sábado, domingo ou feriado sem treino: não quebra a sequência

            $dia->subDay();
        }

        return $sequencia;
    }

    public function mudarMes($direcao)
    {
        $direcao = ((int) $direcao) > 0 ? 1 : -1;

        $data = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonths($direcao);
        $this->currentMonth = $data->month;
        $this->currentYear = $data->year;

        $this->carregarDiasDoMes();
    }
}