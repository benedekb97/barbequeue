<?php

declare(strict_types=1);

namespace App\Service\Queue\Context;

enum ContextType: string
{
    case JOIN = 'join';
    case LEAVE = 'leave';
    case EDIT = 'edit';
    case POP = 'pop';
}
