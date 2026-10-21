<?php

namespace Database\Seeders;

use App\Enums\MaterialStatus;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $publisher = User::query()->where('email', 'admin@example.com')->firstOrFail();

        foreach ($this->materials() as [$categoryName, $title, $type, $status]) {
            $category = Category::query()->where('name', $categoryName)->firstOrFail();
            $requiresVersion = in_array($status, [MaterialStatus::PUBLISHED, MaterialStatus::ARCHIVED], true);

            $material = Material::query()->updateOrCreate(
                ['title' => $title],
                [
                    'description' => "Material fictício de {$categoryName} para testes da biblioteca.",
                    'category_id' => $category->getKey(),
                    'type' => $type,
                    'author' => 'Equipe de Robótica',
                    'status' => $status,
                    'published_at' => $status === MaterialStatus::PUBLISHED ? now()->subDays(3) : null,
                    'archived_at' => $status === MaterialStatus::ARCHIVED ? now()->subDay() : null,
                    'created_by' => $publisher->getKey(),
                ],
            );

            if ($requiresVersion) {
                $this->attachVersion($material, $publisher);
            }
        }
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: MaterialStatus}>
     */
    private function materials(): array
    {
        return [
            ['Arduino', 'Introdução ao Arduino Uno', 'pdf', MaterialStatus::PUBLISHED],
            ['Arduino', 'Piscando o primeiro LED', 'pdf', MaterialStatus::PUBLISHED],
            ['Arduino', 'Sensor ultrassônico com Arduino', 'pdf', MaterialStatus::DRAFT],
            ['Robótica', 'Fundamentos de Robótica Educacional', 'pdf', MaterialStatus::PUBLISHED],
            ['Robótica', 'Tipos de motores e servos', 'pdf', MaterialStatus::PUBLISHED],
            ['Robótica', 'Robô seguidor de linha', 'pdf', MaterialStatus::PUBLISHED],
            ['Eletrônica', 'Circuitos básicos', 'pdf', MaterialStatus::PUBLISHED],
            ['Eletrônica', 'Como usar o protoboard', 'pdf', MaterialStatus::PUBLISHED],
            ['Eletrônica', 'Guia de componentes eletrônicos', 'pdf', MaterialStatus::DRAFT],
            ['Programação', 'Lógica de programação para iniciantes', 'pdf', MaterialStatus::PUBLISHED],
            ['Programação', 'Programação em blocos', 'pdf', MaterialStatus::PUBLISHED],
            ['Programação', 'Introdução ao C++ para Arduino', 'pdf', MaterialStatus::DRAFT],
            ['LEGO Education', 'Primeiros passos com LEGO SPIKE', 'pdf', MaterialStatus::PUBLISHED],
            ['LEGO Education', 'Guia de montagem WeDo 2.0', 'pdf', MaterialStatus::ARCHIVED],
            ['Micro:bit', 'Conhecendo a placa Micro:bit', 'pdf', MaterialStatus::PUBLISHED],
            ['Projetos', 'Braço robótico hidráulico', 'pdf', MaterialStatus::PUBLISHED],
        ];
    }

    private function attachVersion(Material $material, User $publisher): void
    {
        $path = "materials/{$material->getKey()}/".Str::slug($material->title).'.txt';
        $version = MaterialVersion::query()->firstOrCreate(
            ['material_id' => $material->getKey(), 'version_number' => 1],
            [
                'file_path' => $path,
                'original_name' => Str::slug($material->title).'.txt',
                'mime_type' => 'text/plain',
                'size' => strlen($material->title),
                'change_note' => 'Versão inicial de demonstração.',
                'published_by' => $publisher->getKey(),
            ],
        );

        if ($version->wasRecentlyCreated) {
            Storage::disk('local')->put($path, "Arquivo fictício: {$material->title}\n");
        }

        $material->update(['current_version_id' => $version->getKey()]);
    }
}
