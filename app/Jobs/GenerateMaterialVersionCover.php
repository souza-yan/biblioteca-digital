<?php

namespace App\Jobs;

use App\Actions\MaterialVersion\GeneratePdfCover;
use App\Models\MaterialVersion;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateMaterialVersionCover implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 30;

    public int $tries = 1;

    public int $uniqueFor = 90;

    public function __construct(public int $materialVersionId) {}

    public function uniqueId(): string
    {
        return (string) $this->materialVersionId;
    }

    public function handle(GeneratePdfCover $generatePdfCover): void
    {
        $version = MaterialVersion::query()->find($this->materialVersionId);

        if ($version === null) {
            return;
        }

        try {
            $generatePdfCover->handle($version);
        } catch (Throwable $exception) {
            Log::warning('Não foi possível gerar a capa da versão de material.', [
                'material_version_id' => $this->materialVersionId,
                'exception' => $exception,
            ]);
        }
    }
}
