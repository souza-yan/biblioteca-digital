<?php

namespace Database\Seeders;

use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Enums\Role;
use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LogicException;

class DatabaseSeeder extends Seeder
{
    /** Senha conhecida: por isso o seeder só roda em local/testing. */
    private const SEED_PASSWORD = 'password';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Os dados de exemplo só podem ser semeados em local ou testing.');
        }

        $schools = $this->seedSchools();
        $users = $this->seedUsers();

        $this->assignMemberships($users, $schools);
        $this->seedLibrary($users['gestao@example.com']);
        $this->seedLibraryActivity($users);
    }

    /**
     * @return array<string, School> indexadas pela chave da escola (norte, centro, sul, vale)
     */
    private function seedSchools(): array
    {
        $definitions = [
            'norte' => ['name' => 'EMEF Norte', 'region' => SchoolRegion::North],
            'centro' => ['name' => 'EMEF Centro', 'region' => SchoolRegion::Center],
            'sul' => ['name' => 'EMEF Sul', 'region' => SchoolRegion::South],
            'vale' => ['name' => 'EMEF do Vale', 'region' => SchoolRegion::Center],
        ];

        $schools = [];

        foreach ($definitions as $key => $definition) {
            $schools[$key] = School::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'type' => SchoolType::Emef,
                    'region' => $definition['region'],
                    'address' => "Endereço de exemplo — {$definition['name']}",
                    'phone' => null,
                    'is_active' => true,
                ],
            );
        }

        return $schools;
    }

    /**
     * @return array<string, User> indexados pelo e-mail
     */
    private function seedUsers(): array
    {
        $definitions = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => Role::ADMIN],
            ['name' => 'Gestão Norte', 'email' => 'gestao@example.com', 'role' => Role::STAFF],
            ['name' => 'Gestão Centro', 'email' => 'gestao2@example.com', 'role' => Role::STAFF],
            ['name' => 'Gestão Sul', 'email' => 'gestao3@example.com', 'role' => Role::STAFF],
            ['name' => 'Gestão Vale', 'email' => 'gestao4@example.com', 'role' => Role::STAFF],
        ];

        for ($n = 1; $n <= 10; $n++) {
            $definitions[] = [
                'name' => $n === 1 ? 'Professor' : "Professor {$n}",
                'email' => $n === 1 ? 'professor@example.com' : "professor{$n}@example.com",
                'role' => Role::TEACHER,
            ];
        }

        $users = [];

        foreach ($definitions as $definition) {
            $users[$definition['email']] = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => Hash::make(self::SEED_PASSWORD),
                    'role' => $definition['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }

        return $users;
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, School>  $schools
     */
    private function assignMemberships(array $users, array $schools): void
    {
        // Admin não pertence a nenhuma escola.

        // Um gestor por escola.
        $staffSchools = [
            'gestao@example.com' => 'norte',
            'gestao2@example.com' => 'centro',
            'gestao3@example.com' => 'sul',
            'gestao4@example.com' => 'vale',
        ];

        foreach ($staffSchools as $email => $schoolKey) {
            $users[$email]->schools()->sync([
                $schools[$schoolKey]->getKey() => ['is_active' => true],
            ]);
        }

        // Professores: o primeiro é multiescola (norte + centro);
        // os demais se distribuem entre as 4 escolas.
        $schoolKeys = array_keys($schools);
        $index = 0;

        foreach ($users as $user) {
            if (! $user->isTeacher()) {
                continue;
            }

            $keys = $index === 0
                ? ['norte', 'centro']
                : [$schoolKeys[$index % count($schoolKeys)]];

            $user->schools()->sync(
                collect($keys)
                    ->mapWithKeys(fn (string $key): array => [$schools[$key]->getKey() => ['is_active' => true]])
                    ->all(),
            );

            $index++;
        }
    }

    private function seedLibrary(User $publisher): void
    {
        foreach ($this->library() as $categoryName => $materials) {
            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => "Materiais de {$categoryName} para a biblioteca de robótica.",
                    'is_active' => true,
                ],
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
                    'created_by' => $publisher->id,
                ]);

                // Material publicado precisa de uma versão, senão o professor não tem o que baixar.
                if ($status === 'published') {
                    $this->attachPlaceholderVersion($material, $publisher);
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

    /**
     * @param  array<string, User>  $users
     */
    private function seedLibraryActivity(array $users): void
    {
        $materials = Material::query()
            ->where('status', MaterialStatus::PUBLISHED)
            ->whereIn('title', [
                'Introdução ao Arduino Uno',
                'Piscando o primeiro LED',
                'Fundamentos de Robótica Educacional',
            ])
            ->with('currentVersion')
            ->get()
            ->keyBy('title');

        $teacher = $users['professor@example.com'];
        $secondTeacher = $users['professor2@example.com'];
        $staff = $users['gestao@example.com'];
        $admin = $users['admin@example.com'];

        foreach ([
            [$teacher, 'Introdução ao Arduino Uno'],
            [$teacher, 'Piscando o primeiro LED'],
            [$secondTeacher, 'Fundamentos de Robótica Educacional'],
        ] as [$downloader, $title]) {
            $material = $materials->get($title);

            if ($material === null || $material->currentVersion === null) {
                throw new LogicException("O material de exemplo \"{$title}\" precisa de uma versão atual.");
            }

            $downloader->favorites()->syncWithoutDetaching([$material->getKey()]);

            Download::query()->firstOrCreate(
                [
                    'user_id' => $downloader->getKey(),
                    'material_id' => $material->getKey(),
                    'material_version_id' => $material->currentVersion->getKey(),
                ],
                ['downloaded_at' => now()->subDays(2)],
            );
        }

        foreach ([
            [$admin, ActivityAction::USER_CREATED, 'Usuário de exemplo criado.', null],
            [$staff, ActivityAction::CATEGORY_CREATED, 'Categoria de exemplo criada.', null],
            [$staff, ActivityAction::MATERIAL_CREATED, 'Material de exemplo criado.', $materials->get('Introdução ao Arduino Uno')],
            [$teacher, ActivityAction::MATERIAL_DOWNLOADED, 'Material de exemplo baixado.', $materials->get('Introdução ao Arduino Uno')],
        ] as [$actor, $action, $description, $material]) {
            $exists = ActivityLog::query()
                ->where('user_id', $actor->getKey())
                ->where('action', $action)
                ->where('description', $description)
                ->where('material_id', $material?->getKey())
                ->exists();

            if (! $exists) {
                ActivityLog::query()->create([
                    'user_id' => $actor->getKey(),
                    'material_id' => $material?->getKey(),
                    'action' => $action,
                    'description' => $description,
                ]);
            }
        }
    }
}
