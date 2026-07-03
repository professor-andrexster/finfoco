<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function create()
    {
        $categorias = Category::disponiveis()->orderBy('nome')->get();

        // Descrições recentes do usuário viram sugestões do campo (menos digitação)
        $sugestoes = Transaction::where('user_id', auth()->id())
            ->select('descricao')
            ->groupBy('descricao')
            ->orderByRaw('MAX(created_at) DESC')
            ->limit(10)
            ->pluck('descricao');

        return view('transactions.create', compact('categorias', 'sugestoes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'         => ['required', 'in:entrada,saida'],
            'valor'        => ['required', 'numeric', 'min:0.01'],
            'descricao'    => ['required', 'string', 'max:60'],
            'categoria_id' => ['nullable', $this->categoriaDisponivel()],
            'data'         => ['required', 'date'],
        ], [
            'tipo.required'      => 'Escolha entrada ou saída.',
            'valor.required'     => 'Informe o valor.',
            'valor.min'          => 'O valor deve ser maior que zero.',
            'descricao.required' => 'Informe uma descrição.',
            'data.required'      => 'Informe a data.',
        ]);

        Transaction::create($data + ['user_id' => auth()->id()]);

        // "Salvar e lançar outro": volta pro formulário pra registrar em sequência
        if ($request->boolean('continuar')) {
            return redirect()->route('transactions.create')->with('sucesso', 'Lançamento salvo! Pode lançar o próximo.');
        }

        return redirect()->route('dashboard')->with('sucesso', 'Lançamento salvo!');
    }

    public function edit(Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);
        $categorias = Category::disponiveis()->orderBy('nome')->get();
        return view('transactions.edit', compact('transaction', 'categorias'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        $data = $request->validate([
            'tipo'         => ['required', 'in:entrada,saida'],
            'valor'        => ['required', 'numeric', 'min:0.01'],
            'descricao'    => ['required', 'string', 'max:60'],
            'categoria_id' => ['nullable', $this->categoriaDisponivel()],
            'data'         => ['required', 'date'],
        ]);

        $transaction->update($data);
        return redirect()->route('history.index')->with('sucesso', 'Lançamento atualizado!');
    }

    public function destroy(Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);
        $transaction->delete();
        return redirect()->back()->with('sucesso', 'Lançamento excluído!');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ], [
            'ids.required' => 'Selecione ao menos um lançamento.',
        ]);

        $excluidos = Transaction::where('user_id', auth()->id())
            ->whereIn('id', $data['ids'])
            ->delete();

        return redirect()->route('history.index')
            ->with('sucesso', "{$excluidos} lançamento(s) excluído(s)!");
    }

    public function history(Request $request)
    {
        $busca       = $request->input('busca');
        $tipo        = $request->input('tipo');
        $categoriaId = $request->input('categoria_id');
        $dataInicio  = $request->input('data_inicio');
        $dataFim     = $request->input('data_fim');

        $query = Transaction::with('categoria')
            ->where('user_id', auth()->id())
            ->orderBy('data', 'desc')
            ->orderBy('created_at', 'desc');

        if ($busca) $query->where('descricao', 'like', "%{$busca}%");
        if ($tipo && in_array($tipo, ['entrada', 'saida'])) $query->where('tipo', $tipo);
        if ($categoriaId) $query->where('categoria_id', $categoriaId);
        if ($dataInicio) $query->whereDate('data', '>=', $dataInicio);
        if ($dataFim) $query->whereDate('data', '<=', $dataFim);

        $transactions = $query->paginate(20)->withQueryString();
        $categorias   = Category::disponiveis()->orderBy('nome')->get();

        return view('history.index', compact(
            'transactions', 'busca', 'tipo', 'categoriaId', 'dataInicio', 'dataFim', 'categorias'
        ));
    }
}
