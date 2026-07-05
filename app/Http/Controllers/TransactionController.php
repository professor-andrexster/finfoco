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

        // Descrições recentes do usuário viram sugestões do campo (menos digitação).
        // Só lançamentos manuais: pagamentos de contas geram sufixos "(3/12)"/"(parcial)"
        // que não fazem sentido como sugestão.
        $sugestoes = Transaction::where('user_id', auth()->id())
            ->whereNull('bill_id')
            ->select('descricao')
            ->groupBy('descricao')
            ->orderByRaw('MAX(created_at) DESC')
            ->limit(10)
            ->pluck('descricao');

        $limiteImpulso = (float) \App\Models\Setting::get('limite_impulso', '150.00');
        $valorHora     = (float) \App\Models\Setting::get('valor_hora', '0');

        return view('transactions.create', compact('categorias', 'sugestoes', 'limiteImpulso', 'valorHora'));
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

    public function repeat(Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        Transaction::create([
            'user_id'      => auth()->id(),
            'tipo'         => $transaction->tipo,
            'valor'        => $transaction->valor,
            'descricao'    => $transaction->descricao,
            'categoria_id' => $transaction->categoria_id,
            'data'         => today(),
        ]);

        return redirect()->route('history.index')
            ->with('sucesso', "\"{$transaction->descricao}\" repetido com a data de hoje!");
    }

    public function destroy(Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        $reverteu = $this->reverterPagamentoDeConta($transaction);
        $transaction->delete();

        return redirect()->back()->with('sucesso', $reverteu
            ? 'Lançamento excluído — a conta correspondente voltou a pendente.'
            : 'Lançamento excluído!');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ], [
            'ids.required' => 'Selecione ao menos um lançamento.',
        ]);

        $transacoes = Transaction::where('user_id', auth()->id())
            ->whereIn('id', $data['ids'])
            ->get();

        $revertidas = 0;
        foreach ($transacoes as $t) {
            if ($this->reverterPagamentoDeConta($t)) $revertidas++;
            $t->delete();
        }

        $msg = $transacoes->count() . ' lançamento(s) excluído(s)!';
        if ($revertidas) $msg .= " {$revertidas} conta(s) voltou/voltaram a pendente.";

        return redirect()->route('history.index')->with('sucesso', $msg);
    }

    /**
     * Excluir um lançamento que veio do pagamento de uma conta desfaz o pagamento:
     * devolve o valor abatido e, se a conta estava quitada, volta pra pendente.
     * (A próxima ocorrência de recorrente já criada não é removida — o usuário
     * pode excluí-la manualmente se for o caso.)
     */
    private function reverterPagamentoDeConta(Transaction $transaction): bool
    {
        if (!$transaction->bill_id || !$transaction->bill) {
            return false;
        }

        $bill = $transaction->bill;
        $bill->valor_pago = max(0, (float) $bill->valor_pago - (float) $transaction->valor);
        if (in_array($bill->status, ['pago', 'recebido'])) {
            $bill->status  = 'pendente';
            $bill->pago_em = null;
        }
        $bill->save();

        return true;
    }

    public function history(Request $request)
    {
        $busca       = $request->input('busca');
        $tipo        = $request->input('tipo');
        $categoriaId = $request->input('categoria_id');
        $dataInicio  = $request->input('data_inicio');
        $dataFim     = $request->input('data_fim');

        $transactions = $this->queryHistorico($request)->paginate(20)->withQueryString();
        $categorias   = Category::disponiveis()->orderBy('nome')->get();

        return view('history.index', compact(
            'transactions', 'busca', 'tipo', 'categoriaId', 'dataInicio', 'dataFim', 'categorias'
        ));
    }

    public function exportCsv(Request $request)
    {
        $transactions = $this->queryHistorico($request)->get();
        $nomeArquivo  = 'finfoco-historico-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $saida = fopen('php://output', 'w');
            // BOM UTF-8 + separador ";" — abre certo no Excel pt-BR
            fwrite($saida, "\xEF\xBB\xBF");
            fputcsv($saida, ['Data', 'Tipo', 'Descrição', 'Categoria', 'Valor (R$)'], ';');
            foreach ($transactions as $t) {
                fputcsv($saida, [
                    $t->data->format('d/m/Y'),
                    $t->tipo === 'entrada' ? 'Entrada' : 'Saída',
                    $t->descricao,
                    $t->categoria?->nome ?? '',
                    number_format((float) $t->valor, 2, ',', ''),
                ], ';');
            }
            fclose($saida);
        }, $nomeArquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Query do histórico com os filtros da tela (compartilhada com a exportação CSV). */
    private function queryHistorico(Request $request)
    {
        $query = Transaction::with('categoria')
            ->where('user_id', auth()->id())
            ->orderBy('data', 'desc')
            ->orderBy('created_at', 'desc');

        if ($busca = $request->input('busca')) $query->where('descricao', 'like', "%{$busca}%");
        if (in_array($request->input('tipo'), ['entrada', 'saida'])) $query->where('tipo', $request->input('tipo'));
        if ($categoriaId = $request->input('categoria_id')) $query->where('categoria_id', $categoriaId);
        if ($dataInicio = $request->input('data_inicio')) $query->whereDate('data', '>=', $dataInicio);
        if ($dataFim = $request->input('data_fim')) $query->whereDate('data', '<=', $dataFim);

        return $query;
    }
}
