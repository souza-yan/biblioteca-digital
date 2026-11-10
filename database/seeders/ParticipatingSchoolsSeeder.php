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

class ParticipatingSchoolsSeeder extends Seeder
{
    /** @var array<int, array{name: string, slug: string, legacy_name: string, legacy_slug: string}> */
    public const SCHOOLS = [
        ['name' => 'EMEF Profa Maria Aparecida de Carvalho', 'slug' => 'mariaaparecida', 'legacy_name' => 'Escola A', 'legacy_slug' => 'a'],
        ['name' => 'EMEF Luiz Silvar', 'slug' => 'luizsilvar', 'legacy_name' => 'Escola B', 'legacy_slug' => 'b'],
        ['name' => 'EMEF Profa Maria Thereza de Souza Castro', 'slug' => 'mariathereza', 'legacy_name' => 'Escola C', 'legacy_slug' => 'c'],
        ['name' => 'EMEF Luiz Ribeiro Muniz', 'slug' => 'luizribeiromuniz', 'legacy_name' => 'Escola D', 'legacy_slug' => 'd'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (self::SCHOOLS as $definition) {
                $school = School::query()->where('name', $definition['name'])->first()
                    ?? School::query()->where('name', $definition['legacy_name'])->first()
                    ?? new School;

                if (! $school->exists) {
                    $school->fill([
                        'type' => SchoolType::Emef, 'region' => SchoolRegion::Unspecified,
                        'address' => 'Endereço a informar — Caraguatatuba/SP', 'phone' => null,
                    ]);
                } elseif ($school->name === $definition['legacy_name']) {
                    if ($school->address === 'Endereço a definir — '.$definition['legacy_name'].', Caraguatatuba/SP') {
                        $school->address = 'Endereço a informar — Caraguatatuba/SP';
                        $school->region = SchoolRegion::Unspecified;
                    }
                }

                $school->name = $definition['name'];
                $school->save();
                $this->seedManager($school, $definition);
            }
        });
    }

    /** @param array{name: string, slug: string, legacy_name: string, legacy_slug: string} $definition */
    private function seedManager(School $school, array $definition): void
    {
        $email = $definition['slug'].'@educacaocaraguatatuba.com.br';
        $manager = User::query()->where('email', $email)->first()
            ?? $school->users()->where('email', $definition['legacy_slug'].'@educacaocaraguatatuba.com.br')->where('role', Role::STAFF)->first()
            ?? new User;

        if ($manager->exists && ! $manager->isStaff()) {
            throw new LogicException('O e-mail do gestor já pertence a uma conta de outro cargo.');
        }

        if ($manager->exists && $manager->schools()->wherePivot('is_active', true)->where('schools.id', '!=', $school->getKey())->exists()) {
            throw new LogicException('O gestor já está vinculado a outra escola ativa.');
        }

        if (! $manager->exists) {
            $manager->forceFill([
                'name' => 'Gestão - '.$school->name, 'role' => Role::STAFF, 'is_active' => true,
                'password' => Hash::make((string) config('accounts.default_password')),
                'must_change_password' => true, 'email_verified_at' => now(),
            ]);
        } elseif ($manager->name === 'Gestão - '.$definition['legacy_name']) {
            $manager->name = 'Gestão - '.$school->name;
        }

        if (Hash::check((string) config('accounts.default_password'), $manager->password)) {
            $manager->must_change_password = true;
        }

        $manager->email = $email;
        $manager->save();
        if (! $manager->schools()->whereKey($school->getKey())->exists()) {
            $manager->schools()->attach($school->getKey(), ['is_active' => true]);
        }
    }
}
