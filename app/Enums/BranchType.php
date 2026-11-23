<?php

namespace App\Enums;

enum BranchType: string
{
    case KANTOR_PUSAT = 'kantor_pusat';
    case BRANCH = 'branch';
    case TERMINAL = 'terminal';

    public function label(): string
    {
        return match ($this) {
            self::KANTOR_PUSAT => 'Kantor Pusat',
            self::BRANCH => 'Branch',
            self::TERMINAL => 'Terminal',
        };
    }
}
