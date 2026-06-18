<?php

declare(strict_types=1);

namespace App\Domains\Pets\Enums;

enum SpeciesType: string
{
    case DOG = 'dog';
    case CAT = 'cat';
    case BIRD = 'bird';
    case EXOTIC = 'exotic';
}
