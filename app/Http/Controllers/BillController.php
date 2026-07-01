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
        $bills = Bill::with('categoria')
            ->where('user_id', auth()->id())
            ->orderBy('vencimento')
            ->get();

        return view('bills.index', compact('bills'));
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
            'categoria_id'   => 'nullable|exists:categories,id',
            'recorrente'     => 'nullable|boolean',
            'recorrencia'    => 'nullable|in:semanal,mensal,anual',
            'parcelas_total' => 'nullable|integer|min:2|max:360',
        ], [
            'tipo.required'       => 'Escolha pagar ou receber.',
            'descricao.required'  => 'Informe a descrição.',
            'valor.required'      => 'Informe o valor.',
            'vencimento.required' => 'Informe a data de vencimento.',
        ]);

        $parcelasTotal = $data['parcelas_total'] ?? null;
        $recorrente    = (bool) ($data['recorrente'] ?? false);

        // Parcelado e recorrente são mutuamente exclusivos
        if ($parcelasTotal) $recorrente = false;

        Bill::create([
            'user_id'        => auth()->id(),
            'tipo'           => $data['tipo'],
            'descricao'      => $data['descricao'],
            'valor'          => $data['valor'],
            'vencimento'     => $data['vencimento'],
            'status'         => 'pendente',
            'categoria_id'   => $data['categoria_id'] ?? null,
            'recorrente'     => $recorrente,
            'recorrencia'    => $recorrente ? ($data['recorrencia'] ?? 'mensal') : null,
            'parcelas_total' => $parcelasTotal,
            'parcela_atual'  => 1,
        ]);

        return redirect()->route('bills.index')->with('sucesso', 'Conta salva!');
    }

    public function marcarPago(Bill $bill)
    {
        abort_unless($bill->user_id === auth()->id(), 403);

        $statusPago = $bill->tipo === 'pagar' ? 'pago' : 'recebido';
        $bill->update(['status' => $statusPago, 'pago_em' => Carbon::today()]);

        // Criar transação automaticamente
        Transaction::create([
            'user_id'      => auth()->id(),
            'tipo'         => $bill->tipo === 'pagar' ? 'saida' : 'entrada',
            'valor'        => $bill->valor,
            'descricao'    => $bill->descricao,
            'categoria_id' => $bill->categoria_id,
            'data'         => Carbon::today(),
        ]);

        // Próxima parcela
        if ($bill->isParcelado() && $bill->parcela_atual < $bill->parcelas_total) {
            Bill::create([
                'user_id'        => auth()->id(),
                'tipo'           => $bill->tipo,
                'descricao'      => $bill->descricao,
                'valor'          => $bill->valor,
                'vencimento'     => $bill->vencimento->copy()->addMonth(),
                'status'         => 'pendente',
                'categoria_id'   => $bill->categoria_id,
                'recorrente'     => false,
                'parcelas_total' => $bill->parcelas_total,
                'parcela_atual'  => $bill->parcela_atual + 1,
            ]);
        }

        // Próxima ocorrência recorrente
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
            : ($statusPago === 'pago' ? 'Conta marcada como paga!' : 'Conta marcada como recebida!');

        return redirect()->route('bills.index')->with('sucesso', $msg);
    }

    public function destroy(Bill $bill)
    {
        abort_unless($bill->user_id === auth()->id(), 403);
        $bill->delete();
        return redirect()->route('bills.index')->with('sucesso', 'Conta excluída!');
    }
}
