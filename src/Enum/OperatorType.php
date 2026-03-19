<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Enum;

enum OperatorType: string
{
    case REPLACE = 'replace';
    case REDACT = 'redact';
    case HASH = 'hash';
    case MASK = 'mask';
    case ENCRYPT = 'encrypt';
    case DECRYPT = 'decrypt';
    case CUSTOM = 'custom';
    case KEEP = 'keep';
}
