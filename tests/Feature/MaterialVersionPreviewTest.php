<?php

use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\Process\Process;

it('opens historical previews with modal buttons on mobile and desktop', function (string $role) {
    $actor = $role === 'staff' ? User::factory()->staffInSchool()->create() : User::factory()->admin()->create();
    $material = Material::factory()->create(['created_by' => $actor->getKey()]);
    $historicalVersion = MaterialVersion::factory()->for($material)->create(['version_number' => 1]);
    $currentVersion = MaterialVersion::factory()->for($material)->create(['version_number' => 2]);
    $material->update(['current_version_id' => $currentVersion->getKey()]);

    $route = $role === 'staff' ? 'painel.library.show' : 'painel.materials.show';
    $response = $this->actingAs($actor)->get(route($route, $material));

    $response->assertOk()->assertSee('aria-label="Fechar prévia da versão"', false);
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $xpath = new DOMXPath($document);
    $buttons = $xpath->query('//*[@aria-label="Ver prévia da versão 1"]');
    expect($buttons->length)->toBe(2);
    foreach ($buttons as $button) {
        expect($button->tagName)->toBe('button');
        expect($button->hasAttribute('href'))->toBeFalse();
        expect($button->getAttribute('aria-controls'))->toBe('version-preview-modal-'.$material->getKey());
        expect($button->getAttribute('data-preview-url'))->toBe(route('previews.version', [$material, $historicalVersion]));
        expect($button->getAttribute('data-preview-name'))->toBe($historicalVersion->original_name);
        expect($button->getAttribute('x-on:click'))->toContain('versionPreview =');
    }
    expect($xpath->query('//*[@id="version-preview-modal-'.$material->getKey().'"][@role="dialog"]')->length)->toBe(1);
    expect($xpath->query('//*[@aria-label="Ver prévia da versão 2"]')->length)->toBe(0);
})->with(['staff', 'admin']);

it('selects a proportional image or the appropriate document and media viewer', function () {
    $html = Blade::render('<x-materials.preview-content />');
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
    $templates = [];
    foreach ($document->getElementsByTagName('template') as $template) {
        $templates[] = ['condition' => $template->getAttribute('x-if'), 'html' => $document->saveHTML($template)];
    }

    $process = new Process(['node', '--input-type=module', '-e', <<<'JS'
        import assert from 'node:assert/strict';
        import vm from 'node:vm';
        const templates = JSON.parse(process.argv[1]);
        const cases = {
            'image/png': 'img', 'image/jpeg': 'img', 'image/webp': 'img', 'image/gif': 'img',
            'application/pdf': 'iframe', 'text/plain': 'iframe',
            'video/mp4': 'video', 'video/webm': 'video', 'audio/mpeg': 'audio',
        };
        for (const [type, element] of Object.entries(cases)) {
            const active = templates.filter(template => vm.runInNewContext(template.condition, {
                versionPreview: { type, url: '/preview', name: 'Arquivo' },
            }));
            assert.equal(active.length, 1, type);
            assert.match(active[0].html, new RegExp('<' + element + '\\b'), type);
            if (element === 'img') {
                assert.match(active[0].html, /h-full w-full object-contain/);
                assert.doesNotMatch(active[0].html, /<iframe\b/);
            }
        }
        const unsupported = templates.filter(template => vm.runInNewContext(template.condition, {
            versionPreview: { type: 'application/msword' },
        }));
        assert.equal(unsupported.length, 1);
        assert.match(unsupported[0].html, /A visualização não está disponível/);
        assert.doesNotMatch(unsupported[0].html, /<(iframe|img|video|audio)\b/);
        JS, json_encode($templates, JSON_THROW_ON_ERROR)]);
    $process->run();

    expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
});
