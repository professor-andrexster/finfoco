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
            ->orderByRaw("CASE
                WHEN status IN ('pendente','atrasado') AND vencimento < CURDATE() THEN 0
                ELSE 1
            END")
            ->orderBy('vencimento', 'asc')
            ->get();

        // Atualizar status para 'atrasado' onde venceu e ainda está pendente
        Bill::where('status', 'pendente')
            ->where('vencimento', '<', Carbon::today())
            ->update(['status' => 'atrasado']);

        // Recarregar após update
        $bills = Bill::with('categoria')
            ->orderByRaw("CASE
                WHEN status IN ('pendente','atrasado') AND vencimento < CURDATE() THEN 0
                ELSE 1
            END")
            ->orderBy('vencimento', 'asc')
            ->get();

        return view('bills.index', compact('bills'));
    }

    public function create()
    {
        $categorias = Category::orderBy('nome')->get();
        return view('bills.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'        => 'required|in:pagar,receber',
            'descricao'   => 'required|max:60',
            'valor'       => 'required|numeric|min:0.01',
            'vencimento'  => 'required|date',
            'categoria_id'=> 'nullable|exists:categories,id',
            'recorrente'  => 'boolean',
            'recorrencia' => 'nullable|in:mensal,semanal,anual|required_if:recorrente,1',
        ], [
            'tipo.required'         => 'O tipo é obrigatório.',
            'tipo.in'               => 'O tipo deve ser pagar ou receber.',
            'descricao.required'    => 'A descrição é obrigatória.',
            'descricao.max'         => 'Máximo 60 caracteres.',
            'valor.required'        => 'O valor é obrigatório.',
            'valor.numeric'         => 'O valor deve ser numérico.',
            'valor.min'             => 'O valor deve ser maior que zero.',
            'vencimento.required'   => 'A data de vencimento é obrigatória.',
            'vencimento.date'       => 'Data de vencimento inválida.',
            'categoria_id.exists'   => 'Categoria inválida.',
            'recorrencia.required_if' => 'Informe a frequência de recorrência.',
        ]);

        $data['recorrente'] = $request->boolean('recorrente');
        $data['status']     = 'pendente';

        Bill::create($data);

        return redirect()->route('bills.index')
            ->with('sucesso', 'Conta cadastrada com sucesso!');
    }

    public function marcarPago(Bill $bill)
    {
        $novoStatus = $bill->tipo === 'pagar' ? 'pago' : 'recebido';
        $tipoTransacao = $bill->tipo === 'pagar' ? 'saida' : 'entrada';

        $bill->update([
            'status'  => $novoStatus,
            'pago_em' => Carbon::today(),
        ]);

        // Criar transação correspondente
        Transaction::create([
            'tipo'         => $tipoTransacao,
            'valor'        => $bill->valor,
            'descricao'    => $bill->descricao,
            'categoria_id' => $bill->categoria_id,
            'data'         => Carbon::today(),
        ]);

        // Se recorrente, criar próxima ocorrência
        if ($bill->recorrente) {
            $proxima = $bill->calcularProximaOcorrencia();
            Bill::create([
                'tipo'        => $bill->tipo,
                'descricao'   => $bill->descricao,
                'valor'       => $bill->valor,
                'categoria_id'=> $bill->categoria_id,
                'vencimento'  => $proxima,
                'status'      => 'pendente',
                'recorrente'  => true,
                'recorrencia' => $bill->recorrencia,
            ]);
        }

        $label = $bill->tipo === 'pagar' ? 'Conta marcada como paga!' : 'Recebimento registrado!';
        return redirect()->route('bills.index')->with('sucesso', $label);
    }

    public function destroy(Bill $bill)
    {
        $bill->delete();
        return redirect()->route('bills.index')
            ->with('sucesso', 'Conta removida.');
    }
}
