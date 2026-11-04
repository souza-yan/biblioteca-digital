<?php

namespace App\Actions\MaterialVersion;

use App\Models\MaterialVersion;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GeneratePdfCover
{
    public function handle(MaterialVersion $version): void
    {
        if ($version->mime_type !== 'application/pdf') {
            return;
        }

        $disk = Storage::disk((string) config('materials.pdf_cover.disk'));
        $lock = Cache::lock(
            "material-version-cover:{$version->getKey()}",
            (int) config('materials.pdf_cover.lock_seconds'),
        );

        if (! $lock->get()) {
            return;
        }

        $temporaryDirectory = null;

        try {
            $version = MaterialVersion::query()->findOrFail($version->getKey());

            if ($version->mime_type !== 'application/pdf' || $this->hasCover($version)) {
                return;
            }

            if (! $disk->exists($version->file_path)) {
                throw new RuntimeException('O PDF original não está disponível para gerar a capa.');
            }

            $temporaryDirectory = storage_path('app/private/pdf-cover-generation/'.Str::uuid());
            File::ensureDirectoryExists($temporaryDirectory);

            $outputPrefix = $temporaryDirectory.'/cover';
            $outputPath = $outputPrefix.'.jpg';
            $result = Process::timeout((int) config('materials.pdf_cover.timeout_seconds'))
                ->idleTimeout((int) config('materials.pdf_cover.idle_timeout_seconds'))
                ->run([
                    (string) config('materials.pdf_cover.binary'),
                    '-f', '1',
                    '-l', '1',
                    '-singlefile',
                    '-scale-to', (string) config('materials.pdf_cover.max_dimension'),
                    '-jpeg',
                    '-jpegopt', 'quality='.(string) config('materials.pdf_cover.jpeg_quality').',optimize=y',
                    $disk->path($version->file_path),
                    $outputPrefix,
                ]);

            if (! $result->successful() || ! File::isFile($outputPath)) {
                throw new RuntimeException('Não foi possível converter a primeira página do PDF em capa.');
            }

            if (File::size($outputPath) > ((int) config('materials.pdf_cover.max_size_kilobytes') * 1024)) {
                throw new RuntimeException('A capa gerada excede o tamanho máximo permitido.');
            }

            $coverPath = "materials/{$version->material_id}/covers/version-{$version->getKey()}.jpg";
            $stream = fopen($outputPath, 'rb');

            if ($stream === false) {
                throw new RuntimeException('Não foi possível ler a capa gerada.');
            }

            try {
                if (! $disk->put($coverPath, $stream)) {
                    throw new RuntimeException('Não foi possível armazenar a capa gerada.');
                }
            } finally {
                fclose($stream);
            }

            DB::transaction(function () use ($version, $disk, $coverPath): void {
                $lockedVersion = MaterialVersion::query()
                    ->lockForUpdate()
                    ->findOrFail($version->getKey());

                if (! $this->hasCover($lockedVersion, $disk)) {
                    $lockedVersion->update(['cover_path' => $coverPath]);
                }
            });
        } catch (Throwable $exception) {
            throw new RuntimeException('Não foi possível gerar a capa do PDF.', previous: $exception);
        } finally {
            if (is_string($temporaryDirectory)) {
                File::deleteDirectory($temporaryDirectory);
            }

            $lock->release();
        }
    }

    private function hasCover(MaterialVersion $version, ?FilesystemAdapter $disk = null): bool
    {
        if (! is_string($version->cover_path) || $version->cover_path === '') {
            return false;
        }

        $disk ??= Storage::disk((string) config('materials.pdf_cover.disk'));

        return $disk->exists($version->cover_path);
    }
}
