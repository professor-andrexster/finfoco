<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Bill;
use App\Models\Reminder;
use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje        = Carbon::today();
        $semanaInicio = Carbon::now()->startOfWeek();
        $mesInicio   = Carbon::now()->startOfMonth();
        $mesFim      = Carbon::now()->endOfMonth();

        // Saldo total
        $saldoTotal = Transaction::selectRaw(
            "SUM(CASE WHEN tipo='entrada' THEN valor ELSE -valor END) as saldo"
        )->value('saldo') ?? 0;

        // Resumos
        $gastosHoje    = Transaction::where('tipo', 'saida')->whereDate('data', $hoje)->sum('valor');
        $gastosSemanais = Transaction::where('tipo', 'saida')->whereDate('data', '>=', $semanaInicio)->sum('valor');
        $entradaMes    = Transaction::where('tipo', 'entrada')->whereDate('data', '>=', $mesInicio)->sum('valor');
        $saidaMes      = Transaction::where('tipo', 'saida')->whereDate('data', '>=', $mesInicio)->sum('valor');

        // Gastos do dia (para toggle dia/semana)
        $gastosHojeDetalhes = Transaction::with('categoria')
            ->where('tipo', 'saida')
            ->whereDate('data', $hoje)
            ->orderBy('created_at', 'desc')
            ->get();

        // Pode Gastar (Safe to Spend)
        $diasRestantesNoMes = $hoje->diffInDays($mesFim) + 1;
        $contasPendentesMes = Bill::where('status', 'pendente')
            ->whereBetween('vencimento', [$hoje, $mesFim])
            ->where('tipo', 'pagar')
            ->sum('valor');
        $entradasEsperadasMes = Bill::where('status', 'pendente')
            ->whereBetween('vencimento', [$hoje, $mesFim])
            ->where('tipo', 'receber')
            ->sum('valor');

        $podeGastarMes  = (float) $saldoTotal + (float) $entradasEsperadasMes - (float) $contasPendentesMes;
        $podeGastarHoje = $diasRestantesNoMes > 0 ? $podeGastarMes / $diasRestantesNoMes : 0;

        $semaforoPodeGastar = 'green';
        if ($podeGastarHoje < 0) {
            $semaforoPodeGastar = $podeGastarHoje < -50 ? 'red' : 'yellow';
        }

        // Avisos inteligentes
        $avisos = $this->gerarAvisos($hoje, $mesFim);

        // Alertas de categoria disparados (manter compatibilidade)
        $alertasDisparados = $this->verificarAlertas();

        // Lembretes não concluídos do mês atual (data >= hoje)
        $lembretes = Reminder::where('data_lembrete', '>=', $hoje)
            ->orderBy('data_lembrete')
            ->get();

        // Últimas transações
        $ultimasTransacoes = Transaction::with('categoria')
            ->orderBy('data', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'saldoTotal',
            'gastosHoje',
            'gastosSemanais',
            'entradaMes',
            'saidaMes',
            'gastosHojeDetalhes',
            'podeGastarHoje',
            'semaforoPodeGastar',
            'avisos',
            'alertasDisparados',
            'lembretes',
            'ultimasTransacoes'
        ));
    }

    private function gerarAvisos(Carbon $hoje, Carbon $mesFim): array
    {
        $avisos = [];

        // 1. Contas vencidas
        $vencidas = Bill::where('status', 'atrasado')
            ->orWhere(function ($q) use ($hoje) {
                $q->where('status', 'pendente')->where('vencimento', '<', $hoje);
            })
            ->count();

        if ($vencidas > 0) {
            $avisos[] = [
                'tipo'     => 'danger',
                'mensagem' => "{$vencidas} conta(s) vencida(s) aguardando pagamento.",
                'link'     => route('bills.index'),
                'icone'    => 'alert-circle',
            ];
        }

        // 2. Contas vencendo nos próximos 3 dias
        $proximas = Bill::where('status', 'pendente')
            ->whereBetween('vencimento', [$hoje, $hoje->copy()->addDays(3)])
            ->get();

        if ($proximas->count() > 0) {
            $totalProximas = $proximas->sum('valor');
            $avisos[] = [
                'tipo'     => 'warning',
                'mensagem' => "{$proximas->count()} conta(s) vence(m) nos próximos 3 dias — total R$ " . number_format($totalProximas, 2, ',', '.'),
                'link'     => route('bills.index'),
                'icone'    => 'clock',
            ];
        }

        // 3. Alertas de categoria próximos do limite (>= 80%)
        $alertas = Alert::where('ativo', 1)->with('categoria')->get();
        foreach ($alertas as $alerta) {
            $inicio = match ($alerta->periodo) {
                'dia'    => Carbon::today(),
                'semana' => Carbon::now()->startOfWeek(),
                'mes'    => Carbon::now()->startOfMonth(),
            };

            $gasto = Transaction::where('tipo', 'saida')
                ->where('categoria_id', $alerta->categoria_id)
                ->whereDate('data', '>=', $inicio)
                ->sum('valor');

            $percentual = $alerta->limite_valor > 0 ? ($gasto / $alerta->limite_valor) * 100 : 0;

            if ($percentual >= 100) {
                $avisos[] = [
                    'tipo'     => 'danger',
                    'mensagem' => "Limite de {$alerta->categoria->nome} atingido: R$ " . number_format($gasto, 2, ',', '.') . " / R$ " . number_format($alerta->limite_valor, 2, ',', '.'),
                    'link'     => route('alerts.index'),
                    'icone'    => 'triangle-alert',
                ];
            } elseif ($percentual >= 80) {
                $avisos[] = [
                    'tipo'     => 'warning',
                    'mensagem' => "{$alerta->categoria->nome}: " . round($percentual) . "% do limite usado (R$ " . number_format($gasto, 2, ',', '.') . ")",
                    'link'     => route('alerts.index'),
                    'icone'    => 'trending-up',
                ];
            }
        }

        // 4. Se nenhum aviso, adicionar mensagem positiva
        if (empty($avisos)) {
            $avisos[] = [
                'tipo'     => 'success',
                'mensagem' => 'Tudo em dia! Continue assim.',
                'link'     => null,
                'icone'    => 'check-circle',
            ];
        }

        return $avisos;
    }

    private function verificarAlertas(): array
    {
        $alertas  = Alert::where('ativo', 1)->with('categoria')->get();
        $disparados = [];

        foreach ($alertas as $alerta) {
            $inicio = match ($alerta->periodo) {
                'dia'    => Carbon::today(),
                'semana' => Carbon::now()->startOfWeek(),
                'mes'    => Carbon::now()->startOfMonth(),
            };

            $gasto = Transaction::where('tipo', 'saida')
                ->where('categoria_id', $alerta->categoria_id)
                ->whereDate('data', '>=', $inicio)
                ->sum('valor');

            if ($gasto >= $alerta->limite_valor) {
                $disparados[] = [
                    'alerta'    => $alerta,
                    'gasto'     => $gasto,
                    'categoria' => $alerta->categoria,
                ];
            }
        }

        return $disparados;
    }
}
