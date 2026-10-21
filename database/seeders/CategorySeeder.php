<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Arduino' => 'Projetos e práticas com placas Arduino.',
            'Robótica' => 'Fundamentos e desafios de robótica educacional.',
            'Eletrônica' => 'Circuitos, sensores e componentes eletrônicos.',
            'Programação' => 'Lógica e programação para projetos de robótica.',
            'LEGO Education' => 'Atividades com soluções LEGO Education.',
            'Micro:bit' => 'Experimentos com a placa Micro:bit.',
            'Projetos' => 'Projetos integradores para a sala de aula.',
        ] as $name => $description) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description, 'is_active' => true],
            );
        }
    }
}
