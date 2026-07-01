<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Bill;
use App\Models\Reminder;
use App\Models\Transaction;
use Carbon\Carbon;

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

        $gastosHoje     = Transaction::where('user_id', $uid)->where('tipo','saida')->whereDate('data',$hoje)->sum('valor');
        $gastosSemanais = Transaction::where('user_id', $uid)->where('tipo','saida')->whereDate('data','>=',$semanaInicio)->sum('valor');
        $entradasSemana = Transaction::where('user_id', $uid)->where('tipo','entrada')->whereDate('data','>=',$semanaInicio)->sum('valor');
        $entradaMes     = Transaction::where('user_id', $uid)->where('tipo','entrada')->whereDate('data','>=',$mesInicio)->sum('valor');
        $saidaMes       = Transaction::where('user_id', $uid)->where('tipo','saida')->whereDate('data','>=',$mesInicio)->sum('valor');

        // Safe-to-spend mensal
        $contasPendentesMes   = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$mesFim])->where('tipo','pagar')->sum('valor');
        $entradasEsperadasMes = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$mesFim])->where('tipo','receber')->sum('valor');
        $diasRestantesMes     = $hoje->diffInDays($mesFim) + 1;
        $podeGastarMes        = (float)$saldoTotal + (float)$entradasEsperadasMes - (float)$contasPendentesMes;
        $podeGastarHoje       = $diasRestantesMes > 0 ? $podeGastarMes / $diasRestantesMes : 0;

        // Safe-to-spend semanal
        $contasPendentesSemana   = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$semanaFim])->where('tipo','pagar')->sum('valor');
        $entradasEsperadasSemana = Bill::where('user_id',$uid)->where('status','pendente')->whereBetween('vencimento',[$hoje,$semanaFim])->where('tipo','receber')->sum('valor');
        $diasRestantesSemana     = max(1, $hoje->diffInDays($semanaFim) + 1);
        $podeGastarSemana        = ((float)$saldoTotal + (float)$entradasEsperadasSemana - (float)$contasPendentesSemana) / $diasRestantesSemana;

        $semaforoPodeGastar = $podeGastarHoje < 0 ? ($podeGastarHoje < -50 ? 'red' : 'yellow') : 'green';

        $avisos = $this->gerarAvisos($uid, $hoje, $mesFim);

        $lembretes = Reminder::where('user_id', $uid)
            ->where('data_lembrete', '>=', $hoje)
            ->orderBy('data_lembrete')
            ->get();

        $ultimasTransacoes = Transaction::with('categoria')
            ->where('user_id', $uid)
            ->orderBy('data','desc')->orderBy('created_at','desc')
            ->limit(5)->get();

        return view('dashboard.index', compact(
            'saldoTotal','gastosHoje','gastosSemanais','entradasSemana','entradaMes','saidaMes',
            'podeGastarHoje','podeGastarSemana','semaforoPodeGastar',
            'avisos','lembretes','ultimasTransacoes'
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
