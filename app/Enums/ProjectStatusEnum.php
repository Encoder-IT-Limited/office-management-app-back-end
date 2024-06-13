<?php

namespace App\Enums;

enum ProjectStatusEnum: int
{
    case UpComing = 0;
    case Lead = 1;
    case OnGoing = 2;
    case Pending = 3;
    case Accepted = 4;
    case Rejected = 5;
    case Completed = 6;

    public static function values(): array
    {
        // return array_column(self::cases(), 'name', 'value');
        return array_map(fn($status) => $status, self::cases());
    }
}
