<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nome' => 'Alimentação',   'cor' => '#F59E0B', 'icone' => 'utensils',      'tipo' => 'saida'],
            ['nome' => 'Transporte',    'cor' => '#3B82F6', 'icone' => 'car',            'tipo' => 'saida'],
            ['nome' => 'Saúde',         'cor' => '#EF4444', 'icone' => 'heart-pulse',    'tipo' => 'saida'],
            ['nome' => 'Lazer',         'cor' => '#8B5CF6', 'icone' => 'gamepad-2',      'tipo' => 'saida'],
            ['nome' => 'Salário',       'cor' => '#22C55E', 'icone' => 'briefcase',      'tipo' => 'entrada'],
            ['nome' => 'Outros',        'cor' => '#64748B', 'icone' => 'tag',            'tipo' => 'ambos'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['nome' => $cat['nome']], $cat);
        }
    }
}
