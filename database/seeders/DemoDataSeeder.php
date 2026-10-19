<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LogicException;

class DemoDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'DemoRobotics2026!';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Os dados de demonstração só podem ser semeados em local ou testing.');
        }

        $schools = $this->seedSchools();

        $admin = $this->upsertUser(
            'demo.admin@example.com',
            'Administrador de Demonstração',
            Role::ADMIN,
        );
        $this->setActiveSchoolMemberships($admin, []);

        foreach ($schools as $key => $school) {
            $staff = $this->upsertUser(
                "demo.gestao.{$key}@example.com",
                "Gestor fictício — {$school->name}",
                Role::STAFF,
            );
            $this->setActiveSchoolMemberships($staff, [$school]);
        }

        $teacherSchools = $this->teacherSchoolAssignments();

        foreach ($teacherSchools as $email => $schoolKeys) {
            $isMultiSchoolTeacher = $email === 'demo.professor.multiescola@example.com';
            $teacherName = $isMultiSchoolTeacher
                ? 'Professor fictício multiescola'
                : 'Professor fictício '.str_replace(
                    ['demo.professor.', '@example.com'],
                    ['', ''],
                    $email,
                );
            $teacher = $this->upsertUser($email, $teacherName, Role::TEACHER);

            $this->setActiveSchoolMemberships(
                $teacher,
                array_map(fn (string $schoolKey): School => $schools[$schoolKey], $schoolKeys),
            );
        }
    }

    /**
     * @return array<string, School>
     */
    private function seedSchools(): array
    {
        $definitions = [
            'norte' => [
                'name' => 'EMEF Demonstração Norte',
                'region' => SchoolRegion::North,
            ],
            'centro' => [
                'name' => 'EMEF Demonstração Centro',
                'region' => SchoolRegion::Center,
            ],
            'sul' => [
                'name' => 'EMEF Demonstração Sul',
                'region' => SchoolRegion::South,
            ],
            'vale' => [
                'name' => 'EMEF Demonstração do Vale',
                'region' => SchoolRegion::Center,
            ],
        ];

        $schools = [];

        foreach ($definitions as $key => $definition) {
            $schools[$key] = School::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'type' => SchoolType::Emef,
                    'region' => $definition['region'],
                    'address' => "Endereço fictício para demonstração — {$definition['name']}",
                    'phone' => null,
                    'is_active' => true,
                ],
            );
        }

        return $schools;
    }

    private function upsertUser(string $email, string $name, Role $role): User
    {
        $attributes = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(self::DEMO_PASSWORD),
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ];

        $user = User::query()->where('email', $email)->first();

        if ($user !== null) {
            $user->forceFill($attributes)->save();

            return $user;
        }

        $factory = match ($role) {
            Role::ADMIN => User::factory()->admin(),
            Role::STAFF => User::factory()->staff(),
            Role::TEACHER => User::factory()->teacher(),
        };

        return $factory->create($attributes);
    }

    /**
     * @param  array<int, School>  $schools
     */
    private function setActiveSchoolMemberships(User $user, array $schools): void
    {
        DB::table('school_user')
            ->where('user_id', $user->getKey())
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        if ($schools === []) {
            return;
        }

        $user->schools()->syncWithoutDetaching(
            collect($schools)
                ->mapWithKeys(fn (School $school): array => [
                    $school->getKey() => ['is_active' => true],
                ])
                ->all(),
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function teacherSchoolAssignments(): array
    {
        $assignments = [
            'norte' => [
                'demo.professor.multiescola@example.com',
                'demo.professor.norte.02@example.com',
                'demo.professor.norte.03@example.com',
                'demo.professor.norte.04@example.com',
                'demo.professor.norte.05@example.com',
            ],
            'centro' => [
                'demo.professor.multiescola@example.com',
                'demo.professor.centro.02@example.com',
                'demo.professor.centro.03@example.com',
                'demo.professor.centro.04@example.com',
                'demo.professor.centro.05@example.com',
            ],
            'sul' => [
                'demo.professor.sul.01@example.com',
                'demo.professor.sul.02@example.com',
                'demo.professor.sul.03@example.com',
                'demo.professor.sul.04@example.com',
                'demo.professor.sul.05@example.com',
            ],
            'vale' => [
                'demo.professor.vale.01@example.com',
                'demo.professor.vale.02@example.com',
                'demo.professor.vale.03@example.com',
                'demo.professor.vale.04@example.com',
                'demo.professor.vale.05@example.com',
            ],
        ];

        $teacherSchools = [];

        foreach ($assignments as $schoolKey => $emails) {
            foreach ($emails as $email) {
                $teacherSchools[$email][] = $schoolKey;
            }
        }

        return $teacherSchools;
    }
}
