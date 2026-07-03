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

        // Contas simples e recorrentes (pendentes/atrasadas)
        $contasSimples = Bill::with('categoria')
            ->where('user_id', $uid)
            ->whereNull('parcelas_total')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->orderBy('vencimento')
            ->get();

        // Parcelamentos: agrupar por descrição+total e pegar próxima parcela pendente
        $parcelamentos = Bill::with('categoria')
            ->where('user_id', $uid)
            ->whereNotNull('parcelas_total')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->orderBy('parcela_atual')
            ->get()
            ->groupBy(fn($b) => $b->descricao . '||' . $b->parcelas_total . '||' . $b->valor)
            ->map(fn($grupo) => [
                'proxima'   => $grupo->first(),
                'pendentes' => $grupo->count(),
                'total'     => $grupo->first()->parcelas_total,
                'pagas'     => $grupo->first()->parcela_atual - 1,
                'todas'     => $grupo,
            ]);

        // Contas pagas (todas)
        $contasPagas = Bill::with('categoria')
            ->where('user_id', $uid)
            ->whereIn('status', ['pago', 'recebido'])
            ->orderByDesc('pago_em')
            ->limit(30)
            ->get();

        return view('bills.index', compact('contasSimples', 'parcelamentos', 'contasPagas'));
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

        $statusPago = $bill->tipo === 'pagar' ? 'pago' : 'recebido';
        $bill->update(['status' => $statusPago, 'pago_em' => Carbon::today()]);

        Transaction::create([
            'user_id'      => auth()->id(),
            'tipo'         => $bill->tipo === 'pagar' ? 'saida' : 'entrada',
            'valor'        => $bill->valor,
            'descricao'    => $bill->isParcelado()
                ? "{$bill->descricao} ({$bill->parcela_atual}/{$bill->parcelas_total})"
                : $bill->descricao,
            'categoria_id' => $bill->categoria_id,
            'data'         => Carbon::today(),
        ]);

        // Recorrente sem parcelas: criar próxima ocorrência
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

        $msg = $bill->isParcelado()
            ? "Parcela {$bill->parcela_atual}/{$bill->parcelas_total} paga!"
            : ($statusPago === 'pago' ? 'Pago!' : 'Recebido!');

        return redirect()->route('bills.index')->with('sucesso', $msg);
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
