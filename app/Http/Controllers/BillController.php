<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index()
    {
        $uid = auth()->id();

        // Contas simples pendentes/atrasadas, separadas em fixas (recorrentes) e avulsas
        $pendentesSimples = Bill::with('categoria')
            ->where('user_id', $uid)
            ->whereNull('parcelas_total')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->orderBy('vencimento')
            ->get();

        $contasFixas   = $pendentesSimples->where('recorrente', true)->values();
        $contasAvulsas = $pendentesSimples->where('recorrente', false)->values();

        // Base do custo fixo mensal (todas as recorrentes, normalizadas, dedup por descrição)
        $custoFixo = Bill::custoFixoMensal($uid);

        // Totais gerais pendentes (inclui parcelas), descontando abatimentos parciais
        $pendentesTotais = Bill::where('user_id', $uid)
            ->whereIn('status', ['pendente', 'atrasado'])
            ->selectRaw("tipo, SUM(valor - valor_pago) as total")
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $totalAPagar   = (float) ($pendentesTotais['pagar'] ?? 0);
        $totalAReceber = (float) ($pendentesTotais['receber'] ?? 0);

        // Parcelamentos: agrupar por descrição+total e pegar próxima parcela pendente
        $parcelamentos = Bill::with('categoria')
            ->where('user_id', $uid)
            ->whereNotNull('parcelas_total')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->orderBy('parcela_atual')
            ->get()
            ->groupBy(fn($b) => $b->descricao . '||' . $b->parcelas_total . '||' . $b->valor)
            ->map(fn($grupo) => [
                'proxima'        => $grupo->first(),
                'pendentes'      => $grupo->count(),
                'total'          => $grupo->first()->parcelas_total,
                'pagas'          => $grupo->first()->parcela_atual - 1,
                'todas'          => $grupo,
                'restante_total' => $grupo->sum(fn($b) => $b->restante()),
            ]);

        // Contas pagas (todas)
        $contasPagas = Bill::with('categoria')
            ->where('user_id', $uid)
            ->whereIn('status', ['pago', 'recebido'])
            ->orderByDesc('pago_em')
            ->limit(30)
            ->get();

        return view('bills.index', compact(
            'contasFixas', 'contasAvulsas', 'custoFixo', 'parcelamentos', 'contasPagas',
            'totalAPagar', 'totalAReceber'
        ));
    }

    public function create()
    {
        $categorias = Category::disponiveis()->orderBy('nome')->get();
        return view('bills.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'           => 'required|in:pagar,receber',
            'descricao'      => 'required|max:60',
            'valor'          => 'required|numeric|min:0.01',
            'vencimento'     => 'required|date',
            'categoria_id'   => ['nullable', $this->categoriaDisponivel()],
            '_modo'          => 'required|in:avista,parcelado,recorrente',
            'recorrencia'    => 'nullable|in:semanal,mensal,anual',
            'parcelas_total' => 'nullable|integer|min:2|max:360',
        ], [
            'tipo.required'        => 'Escolha pagar ou receber.',
            'descricao.required'   => 'Informe a descrição.',
            'valor.required'       => 'Informe o valor.',
            'vencimento.required'  => 'Informe a data de vencimento.',
            '_modo.required'       => 'Escolha o tipo de cobrança.',
            'parcelas_total.max'   => 'Máximo de 360 parcelas.',
        ]);

        // O modo escolhido manda: campos dos outros modos que vierem no POST
        // (inputs escondidos por x-show ainda são enviados) são ignorados.
        $uid           = auth()->id();
        $parcelasTotal = $data['_modo'] === 'parcelado'  ? ($data['parcelas_total'] ?? null) : null;
        $recorrente    = $data['_modo'] === 'recorrente';

        if ($data['_modo'] === 'parcelado' && !$parcelasTotal) {
            return back()->withInput()->withErrors(['parcelas_total' => 'Informe o número de parcelas.']);
        }
        $vencimento    = Carbon::parse($data['vencimento']);
        $base          = [
            'user_id'      => $uid,
            'tipo'         => $data['tipo'],
            'descricao'    => $data['descricao'],
            'valor'        => $data['valor'],
            'categoria_id' => $data['categoria_id'] ?? null,
            'status'       => 'pendente',
        ];

        if ($parcelasTotal) {
            // Criar TODAS as parcelas de uma vez
            for ($i = 1; $i <= $parcelasTotal; $i++) {
                Bill::create(array_merge($base, [
                    'vencimento'     => $vencimento->copy()->addMonths($i - 1),
                    'parcelas_total' => $parcelasTotal,
                    'parcela_atual'  => $i,
                    'recorrente'     => false,
                ]));
            }
            $msg = "{$parcelasTotal} parcelas de \"{$data['descricao']}\" cadastradas!";
        } else {
            Bill::create(array_merge($base, [
                'vencimento'  => $vencimento,
                'recorrente'  => $recorrente,
                'recorrencia' => $recorrente ? ($data['recorrencia'] ?? 'mensal') : null,
            ]));
            $msg = 'Conta cadastrada!';
        }

        return redirect()->route('bills.index')->with('sucesso', $msg);
    }

    public function edit(Bill $bill)
    {
        abort_unless($bill->user_id === auth()->id(), 403);

        $categorias = Category::disponiveis()->orderBy('nome')->get();

        return view('bills.edit', compact('bill', 'categorias'));
    }

    public function update(Request $request, Bill $bill)
    {
        abort_unless($bill->user_id === auth()->id(), 403);

        $data = $request->validate([
            'descricao'    => 'required|max:60',
            'valor'        => 'required|numeric|min:0.01',
            'vencimento'   => 'required|date',
            'categoria_id' => ['nullable', $this->categoriaDisponivel()],
        ], [
            'descricao.required'  => 'Informe a descrição.',
            'valor.required'      => 'Informe o valor.',
            'vencimento.required' => 'Informe a data de vencimento.',
        ]);

        $bill->update($data);

        return redirect()->route('bills.index')->with('sucesso', 'Conta atualizada!');
    }

    public function marcarPago(Bill $bill)
    {
        abort_unless($bill->user_id === auth()->id(), 403);

        // Guarda contra duplo clique: pagar duas vezes duplicaria a Transaction
        // e, em recorrentes, geraria duas próximas ocorrências.
        if (!in_array($bill->status, ['pendente', 'atrasado'])) {
            return redirect()->route('bills.index');
        }

        // Se houve abatimentos parciais, a transação final é só do que falta
        $restante = $bill->restante();
        if ($restante > 0) {
            $this->registrarPagamento($bill, $restante);
        }
        $this->concluirConta($bill);

        $msg = $bill->isParcelado()
            ? "Parcela {$bill->parcela_atual}/{$bill->parcelas_total} paga!"
            : ($bill->tipo === 'pagar' ? 'Pago!' : 'Recebido!');

        return redirect()->route('bills.index')->with('sucesso', $msg);
    }

    public function pagarParcial(Request $request, Bill $bill)
    {
        abort_unless($bill->user_id === auth()->id(), 403);

        if (!in_array($bill->status, ['pendente', 'atrasado'])) {
            return redirect()->route('bills.index');
        }

        $data = $request->validate([
            'valor' => 'required|numeric|min:0.01',
        ], [
            'valor.required' => 'Informe o valor a abater.',
            'valor.min'      => 'O valor deve ser maior que zero.',
        ]);

        $restante = $bill->restante();
        $abatido  = (float) $data['valor'];

        // Abater o restante (ou mais) quita a conta de vez
        if ($abatido >= $restante - 0.009) {
            $this->registrarPagamento($bill, $restante);
            $this->concluirConta($bill);
            return redirect()->route('bills.index')
                ->with('sucesso', ($bill->tipo === 'pagar' ? 'Dívida quitada!' : 'Recebido por completo!'));
        }

        $this->registrarPagamento($bill, $abatido, parcial: true);
        $bill->update(['valor_pago' => (float) $bill->valor_pago + $abatido]);

        $novoRestante = number_format($bill->restante(), 2, ',', '.');
        return redirect()->route('bills.index')
            ->with('sucesso', 'R$ ' . number_format($abatido, 2, ',', '.') . " abatido — restam R$ {$novoRestante}.");
    }

    /** Cria a Transaction do pagamento (integral ou parcial) ligada à conta. */
    private function registrarPagamento(Bill $bill, float $valor, bool $parcial = false): void
    {
        $descricao = $bill->isParcelado()
            ? "{$bill->descricao} ({$bill->parcela_atual}/{$bill->parcelas_total})"
            : $bill->descricao;

        Transaction::create([
            'user_id'      => auth()->id(),
            'tipo'         => $bill->tipo === 'pagar' ? 'saida' : 'entrada',
            'valor'        => $valor,
            'descricao'    => $parcial ? "{$descricao} (parcial)" : $descricao,
            'categoria_id' => $bill->categoria_id,
            'bill_id'      => $bill->id,
            'data'         => Carbon::today(),
        ]);
    }

    /** Marca a conta como quitada e, se recorrente, agenda a próxima ocorrência. */
    private function concluirConta(Bill $bill): void
    {
        $bill->update([
            'status'     => $bill->tipo === 'pagar' ? 'pago' : 'recebido',
            'pago_em'    => Carbon::today(),
            'valor_pago' => $bill->valor,
        ]);

        if ($bill->recorrente && !$bill->isParcelado()) {
            Bill::create([
                'user_id'     => auth()->id(),
                'tipo'        => $bill->tipo,
                'descricao'   => $bill->descricao,
                'valor'       => $bill->valor,
                'vencimento'  => $bill->calcularProximaOcorrencia(),
                'status'      => 'pendente',
                'categoria_id'=> $bill->categoria_id,
                'recorrente'  => true,
                'recorrencia' => $bill->recorrencia,
            ]);
        }
    }

    public function destroy(Bill $bill)
    {
        abort_unless($bill->user_id === auth()->id(), 403);
        $bill->delete();
        return redirect()->route('bills.index')->with('sucesso', 'Conta excluída!');
    }

    public function destroyParcelamento(Request $request)
    {
        $uid = auth()->id();
        $request->validate([
            'descricao'      => 'required',
            'parcelas_total' => 'required|integer',
            'valor'          => 'required|numeric',
        ]);

        // valor participa do agrupamento no index — sem ele, dois parcelamentos
        // de mesma descrição/nº de parcelas e valores diferentes cairiam juntos
        Bill::where('user_id', $uid)
            ->where('descricao', $request->descricao)
            ->where('parcelas_total', $request->parcelas_total)
            ->where('valor', $request->valor)
            ->whereIn('status', ['pendente', 'atrasado'])
            ->delete();

        return redirect()->route('bills.index')->with('sucesso', 'Parcelamento excluído!');
    }
}
