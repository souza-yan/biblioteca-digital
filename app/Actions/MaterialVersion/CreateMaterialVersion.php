<?php

namespace App\Actions\MaterialVersion;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CreateMaterialVersion
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(
        Material $material,
        User $actor,
        UploadedFile $uploadedFile,
        ?string $changeNote,
    ): MaterialVersion {
        $storedFilePath = null;

        try {
            return DB::transaction(function () use (
                $material,
                $actor,
                $uploadedFile,
                $changeNote,
                &$storedFilePath,
            ): MaterialVersion {
                $lockedMaterial = Material::query()
                    ->lockForUpdate()
                    ->findOrFail($material->getKey());

                $versionNumber = ((int) $lockedMaterial->versions()->max('version_number')) + 1;
                $storedFilePath = $uploadedFile->store(
                    "materials/{$lockedMaterial->getKey()}/versions",
                    'local',
                );

                if (! is_string($storedFilePath)) {
                    throw new RuntimeException('Não foi possível salvar o arquivo da versão.');
                }

                $version = $lockedMaterial->versions()->create([
                    'version_number' => $versionNumber,
                    'file_path' => $storedFilePath,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getMimeType() ?? $uploadedFile->getClientMimeType(),
                    'size' => (int) $uploadedFile->getSize(),
                    'change_note' => $changeNote,
                    'published_by' => $actor->getKey(),
                ]);

                $lockedMaterial->current_version_id = $version->getKey();
                $lockedMaterial->save();

                $this->logActivity->handle(
                    $actor,
                    ActivityAction::VERSION_CREATED,
                    'Nova versão criada para o material.',
                    $lockedMaterial,
                );

                return $version;
            });
        } catch (Throwable $exception) {
            if (is_string($storedFilePath)) {
                Storage::disk('local')->delete($storedFilePath);
            }

            throw $exception;
        }
    }
}
