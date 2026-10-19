<?php

namespace App\Enums;

enum SchoolType: string
{
    case Emef = 'emef';

    public function label(): string
    {
        return 'EMEF';
    }
}
