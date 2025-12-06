<?php

declare(strict_types=1);

namespace App\DataFixtures\Queue;

enum QueueDomains: string
{
    case FIRST = 'first';
    case SECOND = 'second';
}
