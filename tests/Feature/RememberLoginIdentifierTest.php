<?php

use Symfony\Component\Process\Process;

test('remembering login stores only the identifier and respects opting out', function () {
    $response = $this->get('/login')->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $form = (new DOMXPath($document))->query('//form[@x-data]')->item(0);

    $process = new Process(['node', '--input-type=module', '-e', <<<'JS'
        import assert from 'node:assert/strict';
        import vm from 'node:vm';
        const stored = new Map();
        const storage = {
            getItem: key => stored.get(key) ?? null,
            setItem: (key, value) => stored.set(key, value),
            removeItem: key => stored.delete(key),
        };
        const state = vm.runInNewContext('(' + process.argv[1] + ')', { localStorage: storage });
        const fields = { login: { value: '' }, remember: { checked: false } };
        state.$el = { elements: { namedItem(name) { assert.ok(name in fields); return fields[name]; } } };
        state.restoreIdentifier();
        assert.equal(fields.login.value, '');
        assert.equal(fields.remember.checked, false);
        for (const identifier of ['01234567890', 'teacher@example.com']) {
            fields.login.value = ' ' + identifier + ' ';
            fields.remember.checked = true;
            state.saveIdentifier();
            assert.equal(stored.size, 1);
            const record = JSON.parse(stored.get('biblioteca.remembered-login'));
            assert.equal(record.identifier, identifier);
            assert.ok(record.expiresAt > Date.now());
            assert.deepEqual(Object.keys(record).sort(), ['expiresAt', 'identifier']);
            fields.login.value = '';
            fields.remember.checked = false;
            state.restoreIdentifier();
            assert.equal(fields.login.value, identifier);
            assert.equal(fields.remember.checked, true);
        }
        fields.login.value = 'old-input@example.com';
        state.restoreIdentifier();
        assert.equal(fields.login.value, 'old-input@example.com');
        fields.remember.checked = false;
        state.forgetIdentifier();
        assert.equal(stored.size, 0);
        for (const saved of [
            'legacy@example.com',
            'invalid JSON',
            JSON.stringify({ identifier: 'expired@example.com', expiresAt: Date.now() - 1 }),
            JSON.stringify({ identifier: 'invalid@example.com', expiresAt: 'tomorrow' }),
        ]) {
            stored.set('biblioteca.remembered-login', saved);
            fields.login.value = '';
            state.restoreIdentifier();
            assert.equal(fields.login.value, '');
            assert.equal(fields.remember.checked, false);
            assert.equal(stored.size, 0);
        }
        fields.login.value = 'shared@example.com';
        fields.remember.checked = true;
        state.saveIdentifier();
        state.clearIdentifier();
        assert.equal(fields.login.value, '');
        assert.equal(fields.remember.checked, false);
        assert.equal(stored.size, 0);
        state.saveIdentifier();
        assert.equal(stored.size, 0);
        storage.getItem = storage.setItem = storage.removeItem = () => { throw new Error('Storage blocked'); };
        assert.doesNotThrow(() => state.restoreIdentifier());
        assert.doesNotThrow(() => state.saveIdentifier());
        assert.doesNotThrow(() => state.forgetIdentifier());
        JS, $form->getAttribute('x-data')]);
    $process->run();

    expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
});
