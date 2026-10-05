<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seededUsers = $this->seedUsers();
        $staff = $seededUsers['gestao@example.com'];

        foreach ($this->library() as $categoryName => $materials) {
            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => "Materiais de {$categoryName} para a biblioteca de robótica.",
                    'is_active' => true,
                ]
            );

            foreach ($materials as [$title, $type, $author, $status]) {
                if (Material::query()->where('title', $title)->exists()) {
                    continue; // rodar o seeder de novo não duplica
                }

                // status: 'draft' | 'published' | 'archived' (estados do MaterialFactory)
                $material = Material::factory()->{$status}()->create([
                    'title' => $title,
                    'description' => "{$type} sobre {$category->name}.",
                    'category_id' => $category->id,
                    'type' => $type,
                    'author' => $author,
                    'created_by' => $staff->id,
                ]);

                // Material publicado precisa de uma versão, senão o professor não tem o que baixar
                if ($status === 'published') {
                    $this->attachPlaceholderVersion($material, $staff);
                }
            }
        }
    }

    /** Categoria => [título, tipo, autor, status] */
    private function library(): array
    {
        return [
            'Arduino' => [
                ['Introdução ao Arduino Uno', 'Apostila', 'Equipe de Robótica', 'published'],
                ['Piscando o primeiro LED', 'Atividade', 'Equipe de Robótica', 'published'],
                ['Sensor ultrassônico com Arduino', 'Projeto', 'Equipe de Robótica', 'draft'],
            ],
            'Robótica' => [
                ['Fundamentos de Robótica Educacional', 'Apostila', 'Equipe de Robótica', 'published'],
                ['Tipos de motores e servos', 'Material de apoio', 'Equipe de Robótica', 'published'],
                ['Robô seguidor de linha', 'Projeto', 'Equipe de Robótica', 'published'],
            ],
            'Eletrônica' => [
                ['Circuitos básicos: tensão, corrente e resistência', 'Apostila', 'Equipe de Robótica', 'published'],
                ['Como usar o protoboard', 'Atividade', 'Equipe de Robótica', 'published'],
                ['Guia de componentes eletrônicos', 'Material de apoio', 'Equipe de Robótica', 'draft'],
            ],
            'Programação' => [
                ['Lógica de programação para iniciantes', 'Apostila', 'Equipe de Robótica', 'published'],
                ['Programação em blocos', 'Atividade', 'Equipe de Robótica', 'published'],
                ['Introdução à linguagem C++ para Arduino', 'Apostila', 'Equipe de Robótica', 'draft'],
            ],
            'LEGO Education' => [
                ['Primeiros passos com LEGO SPIKE', 'Apostila', 'Equipe de Robótica', 'published'],
                ['Desafio: construindo um veículo', 'Atividade', 'Equipe de Robótica', 'published'],
                ['Guia de montagem WeDo 2.0', 'Material de apoio', 'Equipe de Robótica', 'archived'],
            ],
            'Micro:bit' => [
                ['Conhecendo a placa Micro:bit', 'Apostila', 'Equipe de Robótica', 'published'],
                ['Jogo de pedra, papel e tesoura', 'Projeto', 'Equipe de Robótica', 'published'],
            ],
            'Projetos' => [
                ['Braço robótico hidráulico', 'Projeto', 'Equipe de Robótica', 'published'],
                ['Estação meteorológica escolar', 'Projeto', 'Equipe de Robótica', 'draft'],
            ],
            'Atividades' => [
                ['Roteiro de aula: primeiro contato com robôs', 'Plano de aula', 'Equipe de Robótica', 'published'],
                ['Gincana de robótica', 'Atividade', 'Equipe de Robótica', 'published'],
            ],
            'Formação de Professores' => [
                ['Como planejar uma aula de robótica', 'Guia', 'Equipe de Formação', 'published'],
                ['Avaliação em robótica educacional', 'Guia', 'Equipe de Formação', 'published'],
            ],
        ];
    }

    private function seedUsers(): array
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => Role::ADMIN],
            ['name' => 'Gestão', 'email' => 'gestao@example.com', 'role' => Role::STAFF],
            ['name' => 'Gestão2', 'email' => 'gestao2@example.com', 'role' => Role::STAFF],
        ];

        for ($n = 1; $n <= 10; $n++) {
            $users[] = [
                'name' => $n === 1 ? 'Professor' : "Professor {$n}",
                'email' => $n === 1 ? 'professor@example.com' : "professor{$n}@example.com",
                'role' => Role::TEACHER,
            ];
        }

        $password = env('SEED_PASSWORD', 'password');
        $seeded = [];

        foreach ($users as $data) {
            $seeded[$data['email']] = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($password),
                    'role' => $data['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        return $seeded;
    }

    private function attachPlaceholderVersion(Material $material, User $publisher): void
    {
        $name = Str::slug($material->title).'.txt';
        $path = "materials/{$material->id}/".Str::uuid().'.txt';
        $content = "Arquivo de exemplo para: {$material->title}\n";

        Storage::disk('local')->put($path, $content); // disco privado

        $version = MaterialVersion::query()->forceCreate([
            'material_id' => $material->id,
            'version_number' => 1,
            'file_path' => $path,
            'original_name' => $name,
            'mime_type' => 'text/plain',
            'size' => strlen($content),
            'change_note' => 'Versão inicial (dados de exemplo).',
            'published_by' => $publisher->id,
        ]);

        $material->forceFill(['current_version_id' => $version->id])->save();
    }
}
