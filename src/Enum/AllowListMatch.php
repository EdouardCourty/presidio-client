<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Enum;

enum AllowListMatch: string
{
    case EXACT = 'exact';
    case REGEX = 'regex';
}
