<?php

namespace App\Livewire\Profile;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Jetstream\Http\Livewire\UpdatePasswordForm as JetstreamUpdatePasswordForm;
use TallStackUi\Traits\Interactions;

class UpdatePasswordForm extends JetstreamUpdatePasswordForm
{
    use Interactions;

    public function updatePassword(UpdatesUserPasswords $updater): void
    {
        try {
            parent::updatePassword($updater);
        } catch (ValidationException $exception) {
            $this->toast()
                ->error('Senha não atualizada!', collect($exception->errors())->flatten()->first())
                ->send();

            throw $exception;
        }

        $this->toast()
            ->success('Senha atualizada!', 'Sua nova senha foi salva com sucesso.')
            ->send();
    }
}
