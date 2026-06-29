<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categorias = Category::withCount('transactions')->orderBy('nome')->get();
        return view('categories.index', compact('categorias'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'  => ['required', 'string', 'max:60', 'unique:categories,nome'],
            'cor'   => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icone' => ['required', 'string', 'max:50'],
            'tipo'  => ['required', 'in:entrada,saida,ambos'],
        ], [
            'nome.required'  => 'Informe o nome da categoria.',
            'nome.unique'    => 'Já existe uma categoria com esse nome.',
            'nome.max'       => 'Nome muito longo (máx. 60 caracteres).',
            'cor.required'   => 'Escolha uma cor.',
            'cor.regex'      => 'Cor inválida.',
            'icone.required' => 'Escolha um ícone.',
            'tipo.required'  => 'Escolha o tipo.',
        ]);

        Category::create($data);

        return redirect()->route('categories.index')->with('sucesso', 'Categoria criada!');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'nome'  => ['required', 'string', 'max:60', 'unique:categories,nome,' . $category->id],
            'cor'   => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icone' => ['required', 'string', 'max:50'],
            'tipo'  => ['required', 'in:entrada,saida,ambos'],
        ], [
            'nome.required'  => 'Informe o nome da categoria.',
            'nome.unique'    => 'Já existe uma categoria com esse nome.',
            'nome.max'       => 'Nome muito longa (máx. 60 caracteres).',
            'cor.required'   => 'Escolha uma cor.',
            'cor.regex'      => 'Cor inválida.',
            'icone.required' => 'Escolha um ícone.',
            'tipo.required'  => 'Escolha o tipo.',
        ]);

        $category->update($data);

        return redirect()->route('categories.index')->with('sucesso', 'Categoria atualizada!');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('sucesso', 'Categoria excluída!');
    }
}
