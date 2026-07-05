<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $uid = auth()->id();

        $mes = (string) $request->input('mes');
        $ref = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)
            ? Carbon::createFromFormat('Y-m', $mes)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $inicio = $ref->copy()->startOfMonth();
        $fim    = $ref->copy()->endOfMonth();

        $doMes = Transaction::where('user_id', $uid)
            ->whereDate('data', '>=', $inicio)
            ->whereDate('data', '<=', $fim);

        $entradas = (clone $doMes)->where('tipo', 'entrada')->sum('valor');
        $saidas   = (clone $doMes)->where('tipo', 'saida')->sum('valor');

        // Fixo vs variável: classifica cada saída pela origem do pagamento
        //   fixo    → pagamento de conta recorrente (aluguel, luz, internet...)
        //   contas  → pagamento de conta avulsa ou parcela
        //   diaADia → lançamento manual (o gasto de escolha do dia a dia)
        $saidasDoMes = (clone $doMes)->where('tipo', 'saida')->with('bill')->get();
        $gastoFixo   = (float) $saidasDoMes->filter(fn($t) => $t->bill?->recorrente)->sum('valor');
        $gastoContas = (float) $saidasDoMes->filter(fn($t) => $t->bill && !$t->bill->recorrente)->sum('valor');
        $gastoDiaADia = (float) $saidasDoMes->whereNull('bill_id')->sum('valor');

        $custoFixoBase = Bill::custoFixoMensal($uid);

        // Evolução: entradas × saídas dos últimos 6 meses (terminando no mês exibido)
        $evolucaoInicio = $ref->copy()->subMonths(5)->startOfMonth();
        $porMesTipo = Transaction::where('user_id', $uid)
            ->whereDate('data', '>=', $evolucaoInicio)
            ->whereDate('data', '<=', $fim)
            ->selectRaw("DATE_FORMAT(data, '%Y-%m') as ym, tipo, SUM(valor) as total")
            ->groupBy('ym', 'tipo')
            ->get()
            ->groupBy('ym');

        $evolucao = collect(range(5, 0))->map(function ($i) use ($ref, $porMesTipo) {
            $mes = $ref->copy()->subMonths($i);
            $ym  = $mes->format('Y-m');
            return [
                'mes'     => $mes,
                'entrada' => (float) ($porMesTipo->get($ym)?->firstWhere('tipo', 'entrada')->total ?? 0),
                'saida'   => (float) ($porMesTipo->get($ym)?->firstWhere('tipo', 'saida')->total ?? 0),
            ];
        })->values();

        // Comparação com o mês anterior (contexto: estou melhorando ou piorando?)
        $mesAnteriorInicio = $ref->copy()->subMonth()->startOfMonth();
        $mesAnteriorFim    = $ref->copy()->subMonth()->endOfMonth();
        $doMesAnterior = Transaction::where('user_id', $uid)
            ->whereDate('data', '>=', $mesAnteriorInicio)
            ->whereDate('data', '<=', $mesAnteriorFim);
        $saidasAnterior   = (float) (clone $doMesAnterior)->where('tipo', 'saida')->sum('valor');
        $entradasAnterior = (float) (clone $doMesAnterior)->where('tipo', 'entrada')->sum('valor');

        $deltaSaidas   = $saidasAnterior   > 0 ? round((((float) $saidas   - $saidasAnterior)   / $saidasAnterior)   * 100) : null;
        $deltaEntradas = $entradasAnterior > 0 ? round((((float) $entradas - $entradasAnterior) / $entradasAnterior) * 100) : null;

        // Saídas agrupadas por categoria, da maior pra menor
        $porCategoria = (clone $doMes)->with('categoria')
            ->where('tipo', 'saida')
            ->get()
            ->groupBy('categoria_id')
            ->map(fn($grupo) => [
                'categoria' => $grupo->first()->categoria,
                'total'     => (float) $grupo->sum('valor'),
                'qtd'       => $grupo->count(),
            ])
            ->sortByDesc('total')
            ->values();

        return view('reports.index', [
            'ref'          => $ref,
            'mesAnterior'  => $ref->copy()->subMonth()->format('Y-m'),
            'mesSeguinte'  => $ref->copy()->addMonth()->format('Y-m'),
            'ehMesAtual'   => $ref->isSameMonth(Carbon::now()),
            'entradas'      => (float) $entradas,
            'saidas'        => (float) $saidas,
            'gastoFixo'     => $gastoFixo,
            'gastoContas'   => $gastoContas,
            'gastoDiaADia'  => $gastoDiaADia,
            'custoFixoBase' => $custoFixoBase,
            'deltaSaidas'   => $deltaSaidas,
            'deltaEntradas' => $deltaEntradas,
            'evolucao'      => $evolucao,
            'porCategoria'  => $porCategoria,
        ]);
    }
}
