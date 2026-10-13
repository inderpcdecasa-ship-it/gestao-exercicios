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

        // Feriados Móveis baseados na Páscoa (Cálculo matemático puro)
        $a = $ano % 19;
        $b = intval($ano / 100);
        $c = $ano % 100;
        $d = intval($b / 4);
        $e = $b % 4;
        $f = intval(($b + 8) / 25);
        $g = intval(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intval($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intval(($a + 11 * $h + 22 * $l) / 451);
        $mes = intval(($h + $l - 7 * $m + 114) / 31);
        $diaPascoa = (($h + $l - 7 * $m + 114) % 31) + 1;

        $pascoa = Carbon::create($ano, $mes, $diaPascoa);

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
        $agora = Carbon::now();

        // Se hoje ainda não tem treino registado:
        if (!isset($diasComTreino[$dia->toDateString()])) {
            // Se ainda for antes das 21:00, o dia de hoje NÃO quebra a sequência (dá tempo de treinar)
            if ($agora->hour < 21) {
                $dia->subDay();
            }
            // Se já passou das 21:00, mantém $dia em hoje para avaliar se quebra
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