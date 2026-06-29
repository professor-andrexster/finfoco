<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function create()
    {
        $categorias = Category::orderBy('nome')->get();
        return view('transactions.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'         => ['required', 'in:entrada,saida'],
            'valor'        => ['required', 'numeric', 'min:0.01'],
            'descricao'    => ['required', 'string', 'max:60'],
            'categoria_id' => ['nullable', 'exists:categories,id'],
            'data'         => ['required', 'date'],
        ], [
            'tipo.required'      => 'Escolha entrada ou saída.',
            'valor.required'     => 'Informe o valor.',
            'valor.min'          => 'O valor deve ser maior que zero.',
            'descricao.required' => 'Informe uma descrição.',
            'descricao.max'      => 'Descrição muito longa (máx. 60 caracteres).',
            'data.required'      => 'Informe a data.',
        ]);

        Transaction::create($data);

        return redirect()->route('dashboard')->with('sucesso', 'Lançamento salvo!');
    }

    public function edit(Transaction $transaction)
    {
        $categorias = Category::orderBy('nome')->get();
        return view('transactions.edit', compact('transaction', 'categorias'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'tipo'         => ['required', 'in:entrada,saida'],
            'valor'        => ['required', 'numeric', 'min:0.01'],
            'descricao'    => ['required', 'string', 'max:60'],
            'categoria_id' => ['nullable', 'exists:categories,id'],
            'data'         => ['required', 'date'],
        ], [
            'tipo.required'      => 'Escolha entrada ou saída.',
            'valor.required'     => 'Informe o valor.',
            'valor.min'          => 'O valor deve ser maior que zero.',
            'descricao.required' => 'Informe uma descrição.',
            'descricao.max'      => 'Descrição muito longa (máx. 60 caracteres).',
            'data.required'      => 'Informe a data.',
        ]);

        $transaction->update($data);

        return redirect()->route('history.index')->with('sucesso', 'Lançamento atualizado!');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->back()->with('sucesso', 'Lançamento excluído!');
    }

    public function history(Request $request)
    {
        $busca = $request->input('busca');
        $tipo  = $request->input('tipo');

        $query = Transaction::with('categoria')->orderBy('data', 'desc')->orderBy('created_at', 'desc');

        if ($busca) {
            $query->where('descricao', 'like', "%{$busca}%");
        }

        if ($tipo && in_array($tipo, ['entrada', 'saida'])) {
            $query->where('tipo', $tipo);
        }

        $transactions = $query->paginate(20)->withQueryString();

        return view('history.index', compact('transactions', 'busca', 'tipo'));
    }
}
