<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Reminder;
use App\Models\Routine;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $uid  = auth()->id();
        $hoje = Carbon::today();
        $semanaInicio = Carbon::now()->startOfWeek();
        $semanaFim    = Carbon::now()->endOfWeek();
        $mesInicio = Carbon::now()->startOfMonth();
        $mesFim    = Carbon::now()->endOfMonth();

        $saldoTotal = Transaction::where('user_id', $uid)
            ->selectRaw("SUM(CASE WHEN tipo='entrada' THEN valor ELSE -valor END) as saldo")
            ->value('saldo') ?? 0;

        // Sempre com limite inferior E superior — sem o superior, lançamentos
        // com data futura contariam nas estatísticas do período atual.
        $gastosHoje     = Transaction::where('user_id', $uid)->where('tipo','saida')->whereDate('data',$hoje)->sum('valor');
        $gastosSemanais = Transaction::where('user_id', $uid)->where('tipo','saida')->whereDate('data','>=',$semanaInicio)->whereDate('data','<=',$semanaFim)->sum('valor');
        $entradasSemana = Transaction::where('user_id', $uid)->where('tipo','entrada')->whereDate('data','>=',$semanaInicio)->whereDate('data','<=',$semanaFim)->sum('valor');
        $entradaMes     = Transaction::where('user_id', $uid)->where('tipo','entrada')->whereDate('data','>=',$mesInicio)->whereDate('data','<=',$mesFim)->sum('valor');
        $saidaMes       = Transaction::where('user_id', $uid)->where('tipo','saida')->whereDate('data','>=',$mesInicio)->whereDate('data','<=',$mesFim)->sum('valor');

        // Safe-to-spend mensal (valor - valor_pago: abatimentos parciais reduzem o pendente)
        $contasPendentesMes   = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$mesFim])->where('tipo','pagar')->sum(DB::raw('valor - valor_pago'));
        $entradasEsperadasMes = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$mesFim])->where('tipo','receber')->sum(DB::raw('valor - valor_pago'));
        $diasRestantesMes     = $hoje->diffInDays($mesFim) + 1;
        $podeGastarMes        = (float)$saldoTotal + (float)$entradasEsperadasMes - (float)$contasPendentesMes;
        $podeGastarHoje       = $diasRestantesMes > 0 ? $podeGastarMes / $diasRestantesMes : 0;

        // Safe-to-spend semanal
        $contasPendentesSemana   = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$semanaFim])->where('tipo','pagar')->sum(DB::raw('valor - valor_pago'));
        $entradasEsperadasSemana = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$semanaFim])->where('tipo','receber')->sum(DB::raw('valor - valor_pago'));
        $diasRestantesSemana     = max(1, $hoje->diffInDays($semanaFim) + 1);
        $podeGastarSemana        = ((float)$saldoTotal + (float)$entradasEsperadasSemana - (float)$contasPendentesSemana) / $diasRestantesSemana;

        $semaforoPodeGastar = $podeGastarHoje < 0 ? ($podeGastarHoje < -50 ? 'red' : 'yellow') : 'green';

        // Onboarding: guia de 3 passos até o usuário ter conta fixa + lançamento
        $temLancamento = Transaction::where('user_id', $uid)->exists();
        $temContaFixa  = Bill::where('user_id', $uid)->where('recorrente', true)->exists();

        $avisos = $this->gerarAvisos($uid, $hoje, $mesFim);

        $gastosRecorrentes = Bill::custoFixoMensal($uid);

        // Meta do dia a dia: gastos manuais do mês (sem bill_id) vs meta definida
        $metaDiaADia  = (float) \App\Models\Setting::get('meta_dia_a_dia', 0);
        $gastoDiaADia = $metaDiaADia > 0
            ? (float) Transaction::where('user_id', $uid)->where('tipo', 'saida')
                ->whereNull('bill_id')
                ->whereDate('data', '>=', $mesInicio)->whereDate('data', '<=', $mesFim)
                ->sum('valor')
            : 0.0;

        // Lembretes pendentes NUNCA somem, mesmo vencidos (essencial pra TDAH);
        // concluídos antigos saem da lista pra não acumular ruído.
        $lembretes = Reminder::where('user_id', $uid)
            ->where(fn($q) => $q->where('concluido', false)
                                ->orWhere('data_lembrete', '>=', $hoje))
            ->orderBy('concluido')
            ->orderBy('data_lembrete')
            ->get();

        $ultimasTransacoes = Transaction::with('categoria')
            ->where('user_id', $uid)
            ->orderBy('data','desc')->orderBy('created_at','desc')
            ->limit(5)->get();

        // Meu dia: o painel abre com o dia, não com dinheiro
        $compromissosHoje = Appointment::where('user_id', $uid)->doDia($hoje)->get();
        $proximoCompromisso = $compromissosHoje
            ->filter(fn($c) => !$c->concluido && ($c->hora === null || substr($c->hora, 0, 5) >= now()->format('H:i')))
            ->sortBy(fn($c) => $c->hora ?? '99:99')
            ->first()
            ?? $compromissosHoje->firstWhere('concluido', false);

        $rotinasHoje = Routine::where('user_id', $uid)
            ->doDia($hoje)
            ->with(['checks' => fn($q) => $q->whereDate('data', $hoje)])
            ->get();
        $rotinasFeitasHoje = $rotinasHoje->filter(fn($r) => $r->checks->isNotEmpty())->count();

        return view('dashboard.index', compact(
            'saldoTotal','gastosHoje','gastosSemanais','entradasSemana','entradaMes','saidaMes',
            'podeGastarHoje','podeGastarSemana','podeGastarMes','semaforoPodeGastar',
            'contasPendentesMes','gastosRecorrentes',
            'metaDiaADia','gastoDiaADia',
            'temLancamento','temContaFixa',
            'avisos','lembretes','ultimasTransacoes',
            'compromissosHoje','proximoCompromisso','rotinasHoje','rotinasFeitasHoje'
        ));
    }

    private function gerarAvisos(int $uid, Carbon $hoje, Carbon $mesFim): array
    {
        $avisos = [];

        $vencidas = Bill::where('user_id',$uid)
            ->where(fn($q) => $q->where('status','atrasado')->orWhere(fn($q2) => $q2->where('status','pendente')->where('vencimento','<',$hoje)))
            ->count();

        if ($vencidas) {
            $avisos[] = ['tipo'=>'danger','mensagem'=>"{$vencidas} conta(s) vencida(s) aguardando pagamento.",'link'=>route('bills.index'),'icone'=>'alert-circle'];
        }

        $proximas = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$hoje->copy()->addDays(3)])->get();
        if ($proximas->count()) {
            $avisos[] = ['tipo'=>'warning','mensagem'=>"{$proximas->count()} conta(s) vence(m) nos próximos 3 dias — R$ ".number_format($proximas->sum('valor'),2,',','.'),'link'=>route('bills.index'),'icone'=>'clock'];
        }

        $alertas = Alert::where('user_id',$uid)->where('ativo',1)->with('categoria')->get();
        foreach ($alertas as $alerta) {
            $inicio = match($alerta->periodo) { 'dia'=>Carbon::today(),'semana'=>Carbon::now()->startOfWeek(),default=>Carbon::now()->startOfMonth() };
            $gasto  = Transaction::where('user_id',$uid)->where('tipo','saida')->where('categoria_id',$alerta->categoria_id)->whereDate('data','>=',$inicio)->sum('valor');
            $pct    = $alerta->limite_valor > 0 ? ($gasto / $alerta->limite_valor) * 100 : 0;
            if ($pct >= 100) $avisos[] = ['tipo'=>'danger','mensagem'=>"Limite {$alerta->categoria->nome} atingido.","link"=>route('alerts.index'),'icone'=>'triangle-alert'];
            elseif ($pct >= 80) $avisos[] = ['tipo'=>'warning','mensagem'=>"{$alerta->categoria->nome}: ".round($pct)."% do limite.",'link'=>route('alerts.index'),'icone'=>'trending-up'];
        }

        if (empty($avisos)) {
            $avisos[] = ['tipo'=>'success','mensagem'=>'Tudo em dia! Continue assim.','link'=>null,'icone'=>'check-circle'];
        }

        return $avisos;
    }

}
