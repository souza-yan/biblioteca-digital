<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;

class CaraguatatubaSchoolsSeeder extends Seeder
{
    private const SEED_PASSWORD = 'password';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Os dados iniciais só podem ser semeados em local ou testing.');
        }

        $schools = [];

        foreach ($this->schools() as [$name, $region]) {
            $schools[] = School::query()->updateOrCreate(
                ['name' => $name],
                [
                    'type' => SchoolType::Emef,
                    'region' => $region,
                    'address' => "Endereço de exemplo — {$name}, Caraguatatuba/SP",
                    'phone' => null,
                ],
            );

        }

        $this->seedUsers($schools);
    }

    /**
     * @return array<int, array{0: string, 1: SchoolRegion}>
     */
    private function schools(): array
    {
        return [
            ['EMEF Centro', SchoolRegion::Center],
            ['EMEF Indaiá', SchoolRegion::Center],
            ['EMEF Martim de Sá', SchoolRegion::Center],
            ['EMEF Jardim Primavera', SchoolRegion::Center],
            ['EMEF Sumaré', SchoolRegion::Center],
            ['EMEF Massaguaçu', SchoolRegion::North],
            ['EMEF Porto Novo', SchoolRegion::North],
            ['EMEF Tinga', SchoolRegion::North],
            ['EMEF Cocanha', SchoolRegion::North],
            ['EMEF Jaraguá', SchoolRegion::North],
            ['EMEF Pegorelli', SchoolRegion::South],
            ['EMEF Perequê-Mirim', SchoolRegion::South],
            ['EMEF Travessão', SchoolRegion::South],
            ['EMEF Casa Branca', SchoolRegion::South],
            ['EMEF Morro do Algodão', SchoolRegion::South],
            ['EMEF Getuba', SchoolRegion::South],
        ];
    }

    /**
     * @param  array<int, School>  $schools
     */
    private function seedUsers(array $schools): void
    {
        $definitions = [
            ['name' => 'Administrador', 'email' => 'admin@example.com', 'role' => Role::ADMIN, 'schoolIndex' => null],
            ['name' => 'Gestão 1', 'email' => 'gestao1@example.com', 'role' => Role::STAFF, 'schoolIndex' => 0],
            ['name' => 'Gestão 2', 'email' => 'gestao2@example.com', 'role' => Role::STAFF, 'schoolIndex' => 1],
            ['name' => 'Professor 1', 'email' => 'professor1@example.com', 'role' => Role::TEACHER, 'schoolIndex' => 0],
            ['name' => 'Professor 2', 'email' => 'professor2@example.com', 'role' => Role::TEACHER, 'schoolIndex' => 1],
            ['name' => 'Professor 3', 'email' => 'professor3@example.com', 'role' => Role::TEACHER, 'schoolIndex' => 2],
        ];

        foreach ($definitions as $definition) {
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => Hash::make(self::SEED_PASSWORD),
                    'role' => $definition['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            if ($definition['schoolIndex'] === null) {
                $user->schools()->sync([]);

                continue;
            }

            $user->schools()->sync([
                $schools[$definition['schoolIndex']]->getKey() => ['is_active' => true],
            ]);
        }
    }
}
