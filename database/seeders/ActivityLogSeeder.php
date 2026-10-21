<?php

namespace Database\Seeders;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Download;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $manager = User::query()->where('email', 'gestao1@example.com')->firstOrFail();
        $teacher = User::query()->where('email', 'professor1@example.com')->firstOrFail();
        $secondTeacher = User::query()->where('email', 'professor2@example.com')->firstOrFail();
        $materials = Material::query()
            ->with('currentVersion')
            ->whereIn('title', [
                'Introdução ao Arduino Uno',
                'Piscando o primeiro LED',
                'Fundamentos de Robótica Educacional',
            ])
            ->get()
            ->keyBy('title');

        foreach ([
            [$teacher, 'Introdução ao Arduino Uno'],
            [$teacher, 'Piscando o primeiro LED'],
            [$secondTeacher, 'Fundamentos de Robótica Educacional'],
        ] as [$downloader, $title]) {
            $material = $materials->get($title);

            if ($material === null || $material->currentVersion === null) {
                continue;
            }

            $downloader->favorites()->syncWithoutDetaching([$material->getKey()]);
            Download::query()->firstOrCreate(
                [
                    'user_id' => $downloader->getKey(),
                    'material_id' => $material->getKey(),
                    'material_version_id' => $material->currentVersion->getKey(),
                ],
                ['downloaded_at' => now()->subDay()],
            );
        }

        foreach ([
            [$admin, ActivityAction::USER_CREATED, 'Conta administrativa inicial criada.', null],
            [$manager, ActivityAction::CATEGORY_CREATED, 'Categoria de robótica criada.', null],
            [$manager, ActivityAction::MATERIAL_CREATED, 'Material de robótica cadastrado.', $materials->get('Introdução ao Arduino Uno')],
            [$teacher, ActivityAction::MATERIAL_DOWNLOADED, 'Material de robótica baixado.', $materials->get('Introdução ao Arduino Uno')],
        ] as [$user, $action, $description, $material]) {
            ActivityLog::query()->firstOrCreate([
                'user_id' => $user->getKey(),
                'material_id' => $material?->getKey(),
                'action' => $action,
                'description' => $description,
            ]);
        }
    }
}
