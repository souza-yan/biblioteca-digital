<?php

namespace App\Console\Commands;

use App\Jobs\GenerateMaterialVersionCover;
use App\Models\MaterialVersion;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('materials:generate-pdf-covers {--chunk=100 : Quantidade de versões processadas por lote}')]
#[Description('Enfileira a geração de capas para versões PDF sem capa.')]
class GenerateMaterialVersionCovers extends Command
{
    public function handle(): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dispatched = 0;

        MaterialVersion::query()
            ->where('mime_type', 'application/pdf')
            ->whereNull('cover_path')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($versions) use (&$dispatched): void {
                foreach ($versions as $version) {
                    GenerateMaterialVersionCover::dispatch($version->getKey());
                    $dispatched++;
                }
            });

        $this->info("{$dispatched} versão(ões) PDF foram enfileiradas para gerar capa.");

        return self::SUCCESS;
    }
}
