<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('accounts:cpf-status')]
#[Description('Verifica se os CPFs de professores e gestores estão prontos para o login obrigatório.')]
class CheckCpfReadiness extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $missing = 0;
        $invalid = 0;
        User::query()->whereIn('role', [Role::STAFF, Role::TEACHER])->select(['id', 'cpf'])
            ->chunkById(500, function ($users) use (&$missing, &$invalid): void {
                foreach ($users as $user) {
                    if (blank($user->cpf)) {
                        $missing++;
                    } elseif (! Cpf::isValid($user->cpf)) {
                        $invalid++;
                    }
                }
            });

        $this->line('Contas sem CPF: '.$missing);
        $this->line('Contas com CPF inválido: '.$invalid);

        if ($missing > 0 || $invalid > 0) {
            $this->warn('Regularize os cadastros antes de ativar CPF_LOGIN_ENABLED.');

            return self::FAILURE;
        }

        $this->info('Cadastros prontos para ativar CPF_LOGIN_ENABLED=true.');

        return self::SUCCESS;
    }
}
