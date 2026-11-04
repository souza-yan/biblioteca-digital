<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LogicException;

class CaraguatatubaSchoolsSeeder extends Seeder
{
    private const EMAIL_DOMAIN = 'educacaocaraguatatuba.com.br';

    /**
     * @var array<int, array{name: string, type: SchoolType, region: SchoolRegion, address: string, email_slug: string}>
     */
    private const SCHOOLS = [
        [
            'name' => 'Escola A',
            'type' => SchoolType::Emef,
            'region' => SchoolRegion::Center,
            'address' => 'Endereço a definir — Escola A, Caraguatatuba/SP',
            'email_slug' => 'a',
        ],
        [
            'name' => 'Escola B',
            'type' => SchoolType::Emef,
            'region' => SchoolRegion::Center,
            'address' => 'Endereço a definir — Escola B, Caraguatatuba/SP',
            'email_slug' => 'b',
        ],
        [
            'name' => 'Escola C',
            'type' => SchoolType::Emef,
            'region' => SchoolRegion::Center,
            'address' => 'Endereço a definir — Escola C, Caraguatatuba/SP',
            'email_slug' => 'c',
        ],
        [
            'name' => 'Escola D',
            'type' => SchoolType::Emef,
            'region' => SchoolRegion::Center,
            'address' => 'Endereço a definir — Escola D, Caraguatatuba/SP',
            'email_slug' => 'd',
        ],
    ];

    private const LEGACY_SCHOOL_NAMES = [
        'EMEF Centro',
        'EMEF Indaiá',
        'EMEF Martim de Sá',
        'EMEF Jardim Primavera',
        'EMEF Sumaré',
        'EMEF Massaguaçu',
        'EMEF Porto Novo',
        'EMEF Tinga',
        'EMEF Cocanha',
        'EMEF Jaraguá',
        'EMEF Pegorelli',
        'EMEF Perequê-Mirim',
        'EMEF Travessão',
        'EMEF Casa Branca',
        'EMEF Morro do Algodão',
        'EMEF Getuba',
    ];

    private const LEGACY_USER_EMAILS = [
        'gestao1@example.com',
        'gestao2@example.com',
        'professor1@example.com',
        'professor2@example.com',
        'professor3@example.com',
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Os dados iniciais só podem ser semeados em local ou testing.');
        }

        $this->removeLegacyDemoData();

        $this->seedAdministrator();

        foreach (self::SCHOOLS as $definition) {
            $school = School::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'type' => $definition['type'],
                    'region' => $definition['region'],
                    'address' => $definition['address'],
                    'phone' => null,
                ],
            );

            $this->seedSchoolUsers($school, $definition);
        }
    }

    /**
     * @param  array{name: string, type: SchoolType, region: SchoolRegion, address: string, email_slug: string}  $definition
     */
    private function seedSchoolUsers(School $school, array $definition): void
    {
        $this->upsertSchoolUser(
            $school,
            "Gestão - {$definition['name']}",
            "{$definition['email_slug']}@".self::EMAIL_DOMAIN,
            Role::STAFF,
        );

        $this->upsertSchoolUser(
            $school,
            "Professor - {$definition['name']}",
            "professor.{$definition['email_slug']}@".self::EMAIL_DOMAIN,
            Role::TEACHER,
        );
    }

    private function seedAdministrator(): void
    {
        $administrator = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            $this->userAttributes('Administrador', Role::ADMIN),
        );

        $administrator->schools()->sync([]);
    }

    private function upsertSchoolUser(School $school, string $name, string $email, Role $role): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            $this->userAttributes($name, $role),
        );

        $user->schools()->sync([
            $school->getKey() => ['is_active' => true],
        ]);
    }

    /**
     * @return array{name: string, password: string, role: Role, is_active: bool, email_verified_at: Carbon}
     */
    private function userAttributes(string $name, Role $role): array
    {
        return [
            'name' => $name,
            'password' => Hash::make((string) config('accounts.default_password')),
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ];
    }

    private function removeLegacyDemoData(): void
    {
        $legacyUsers = User::query()
            ->whereIn('email', self::LEGACY_USER_EMAILS)
            ->get();

        foreach ($legacyUsers as $legacyUser) {
            DB::table('favorites')->where('user_id', $legacyUser->getKey())->delete();
            DB::table('downloads')->where('user_id', $legacyUser->getKey())->delete();
            DB::table('activity_logs')->where('user_id', $legacyUser->getKey())->delete();
            $legacyUser->schools()->detach();
            $legacyUser->delete();
        }

        School::query()->whereIn('name', self::LEGACY_SCHOOL_NAMES)->delete();
    }
}
