<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case TEACHER = 'teacher';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::STAFF => 'Funcionário da Gestão',
            self::TEACHER => 'Professor',
        };
    }
}
