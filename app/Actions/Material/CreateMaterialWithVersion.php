<?php

namespace App\Actions\Material;

use App\Actions\MaterialVersion\CreateMaterialVersion;
use App\Enums\MaterialStatus;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreateMaterialWithVersion
{
    public function __construct(
        private CreateMaterial $createMaterial,
        private CreateMaterialVersion $createMaterialVersion,
        private PublishMaterial $publishMaterial,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(
        User $actor,
        array $attributes,
        UploadedFile $uploadedFile,
        ?string $changeNote,
        MaterialStatus $initialStatus,
    ): Material {
        $storedFilePath = null;

        try {
            return DB::transaction(function () use (
                $actor,
                $attributes,
                $uploadedFile,
                $changeNote,
                $initialStatus,
                &$storedFilePath,
            ): Material {
                $material = $this->createMaterial->handle($actor, $attributes);
                $version = $this->createMaterialVersion->handle(
                    $material,
                    $actor,
                    $uploadedFile,
                    $changeNote,
                );
                $storedFilePath = $version->file_path;

                if ($initialStatus === MaterialStatus::PUBLISHED) {
                    $material->refresh();
                    $this->publishMaterial->handle($actor, $material);
                }

                return $material->fresh(['currentVersion']) ?? $material;
            });
        } catch (Throwable $exception) {
            if (is_string($storedFilePath)) {
                Storage::disk('local')->delete($storedFilePath);
            }

            throw $exception;
        }
    }
}
