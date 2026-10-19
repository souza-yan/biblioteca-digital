<?php

namespace App\Enums;

enum SchoolRegion: string
{
    case North = 'north';
    case Center = 'center';
    case South = 'south';

    public function label(): string
    {
        return match ($this) {
            self::North => 'Norte',
            self::Center => 'Centro',
            self::South => 'Sul',
        };
    }
}
