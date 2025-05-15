<?php

namespace App\Enums;

enum MediaFileType
{
    case IMAGE = 'image';
    case AUDIO = 'audio';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
