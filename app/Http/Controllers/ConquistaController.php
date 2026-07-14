<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\FocusSession;
use App\Models\Routine;
use App\Models\RoutineCheck;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConquistaController extends Controller
{
    public function index()
    {
        $uid    = auth()->id();
        $hoje   = today();
        $inicio = $hoje->copy()->subDays(139)->startOfWeek(); // ~20 semanas fechadas

        // ── Atividade por dia (rotinas feitas + compromissos concluídos + sessões de foco)
        $porDia = collect();

        RoutineCheck::query()
            ->join('routines', 'routines.id', '=', 'routine_checks.routine_id')
            ->where('routines.user_id', $uid)
            ->whereDate('routine_checks.data', '>=', $inicio)
            ->selectRaw('routine_checks.data as d, COUNT(*) as n')
            ->groupBy('d')->pluck('n', 'd')
            ->each(fn($n, $d) => $porDia[$d] = ($porDia[$d] ?? 0) + $n);

        Appointment::where('user_id', $uid)
            ->where('concluido', true)
            ->whereDate('data', '>=', $inicio)
            ->selectRaw('data as d, COUNT(*) as n')
            ->groupBy('d')->pluck('n', 'd')
            ->each(fn($n, $d) => $porDia[$d] = ($porDia[$d] ?? 0) + $n);

        FocusSession::where('user_id', $uid)
            ->whereDate('created_at', '>=', $inicio)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as n')
            ->groupBy('d')->pluck('n', 'd')
            ->each(fn($n, $d) => $porDia[$d] = ($porDia[$d] ?? 0) + $n);

        // Semanas → colunas (seg..dom) para o mapa de constância
        $semanas = [];
        $cursor  = $inicio->copy();
        while ($cursor->lte($hoje)) {
            $chaveSemana = $cursor->copy()->startOfWeek()->toDateString();
            $semanas[$chaveSemana][$cursor->dayOfWeekIso] = [
                'data' => $cursor->toDateString(),
                'n'    => (int) ($porDia[$cursor->toDateString()] ?? 0),
            ];
            $cursor->addDay();
        }

        // ── Sequências (streaks) das rotinas
        $rotinas = Routine::where('user_id', $uid)->with('checks')->get();

        $sequenciaAtual = (int) $rotinas->max(fn($r) => $r->streak());
        $melhorSequencia = 0;
        foreach ($rotinas as $r) {
            $melhorSequencia = max($melhorSequencia, $this->melhorSequencia($r));
        }

        // ── Totais
        $totalChecks = RoutineCheck::query()
            ->join('routines', 'routines.id', '=', 'routine_checks.routine_id')
            ->where('routines.user_id', $uid)->count();
        $totalCompromissos = Appointment::where('user_id', $uid)->where('concluido', true)->count();
        $sessoes           = FocusSession::where('user_id', $uid)
            ->selectRaw('COUNT(*) as n, COALESCE(SUM(minutos),0) as min')->first();
        $diasAtivos        = $porDia->filter(fn($n) => $n > 0)->count();

        // ── Marcos: progresso adulto, sem infantilizar
        $marcos = [
            ['icone' => 'check',       'nome' => 'Primeiro passo',        'desc' => 'Concluir a primeira rotina ou compromisso', 'atual' => min(1, $totalChecks + $totalCompromissos), 'alvo' => 1],
            ['icone' => 'flame',       'nome' => 'Uma semana firme',      'desc' => 'Sequência de 7 dias numa rotina',           'atual' => min(7, $melhorSequencia),   'alvo' => 7],
            ['icone' => 'flame',       'nome' => 'Um mês de constância',  'desc' => 'Sequência de 30 dias numa rotina',          'atual' => min(30, $melhorSequencia),  'alvo' => 30],
            ['icone' => 'zap',         'nome' => 'Primeiro hiperfoco',    'desc' => 'Concluir uma sessão de foco',               'atual' => min(1, $sessoes->n),        'alvo' => 1],
            ['icone' => 'zap',         'nome' => 'Dez mergulhos',         'desc' => '10 sessões de hiperfoco',                   'atual' => min(10, $sessoes->n),       'alvo' => 10],
            ['icone' => 'timer',       'nome' => 'Cinco horas de foco',   'desc' => '300 minutos acumulados em hiperfoco',       'atual' => min(300, (int) $sessoes->min), 'alvo' => 300],
            ['icone' => 'calendar-check', 'nome' => 'Cinquenta conquistados', 'desc' => '50 rotinas e compromissos concluídos', 'atual' => min(50, $totalChecks + $totalCompromissos), 'alvo' => 50],
            ['icone' => 'trending-up', 'nome' => 'Trinta dias ativos',    'desc' => '30 dias com pelo menos uma conclusão',      'atual' => min(30, $diasAtivos),       'alvo' => 30],
        ];

        return view('conquistas.index', [
            'semanas'           => $semanas,
            'sequenciaAtual'    => $sequenciaAtual,
            'melhorSequencia'   => $melhorSequencia,
            'totalConcluidos'   => $totalChecks + $totalCompromissos,
            'sessoesFoco'       => (int) $sessoes->n,
            'minutosFoco'       => (int) $sessoes->min,
            'diasAtivos'        => $diasAtivos,
            'marcos'            => $marcos,
        ]);
    }

    /** Maior sequência histórica de dias agendados cumpridos de uma rotina. */
    private function melhorSequencia(Routine $rotina): int
    {
        if ($rotina->checks->isEmpty()) {
            return 0;
        }

        $feitos = $rotina->checks->map(fn($c) => $c->data->toDateString())->flip();
        $cursor = $rotina->checks->min('data')->copy()->startOfDay();
        $hoje   = today();

        $melhor = 0;
        $atual  = 0;

        while ($cursor->lte($hoje)) {
            if ($rotina->agendadaEm($cursor)) {
                if (isset($feitos[$cursor->toDateString()])) {
                    $atual++;
                    $melhor = max($melhor, $atual);
                } elseif (!$cursor->isToday()) { // hoje pendente não quebra
                    $atual = 0;
                }
            }
            $cursor->addDay();
        }

        return $melhor;
    }
}
