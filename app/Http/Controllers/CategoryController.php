<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categorias = Category::disponiveis()
            ->withCount('transactions')
            ->orderByRaw('user_id IS NOT NULL, nome')
            ->get();
        return view('categories.index', compact('categorias'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'  => 'required|max:60',
            'cor'   => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone' => 'required|max:50',
            'tipo'  => 'required|in:entrada,saida,ambos',
        ], [
            'nome.required'  => 'Informe o nome.',
            'cor.required'   => 'Escolha uma cor.',
            'icone.required' => 'Escolha um ícone.',
        ]);

        Category::create($data + ['user_id' => auth()->id()]);
        return redirect()->route('categories.index')->with('sucesso', 'Categoria criada!');
    }

    public function edit(Category $category)
    {
        abort_unless($category->user_id === auth()->id(), 403);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($category->user_id === auth()->id(), 403);

        $data = $request->validate([
            'nome'  => 'required|max:60',
            'cor'   => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone' => 'required|max:50',
            'tipo'  => 'required|in:entrada,saida,ambos',
        ]);

        $category->update($data);
        return redirect()->route('categories.index')->with('sucesso', 'Categoria atualizada!');
    }

    public function destroy(Category $category)
    {
        abort_unless($category->user_id === auth()->id(), 403);
        $category->delete();
        return redirect()->route('categories.index')->with('sucesso', 'Categoria excluída!');
    }
}
