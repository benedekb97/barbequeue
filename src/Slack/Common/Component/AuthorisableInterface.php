<?php

declare(strict_types=1);

namespace App\Slack\Common\Component;

interface AuthorisableInterface
{
    public function isAuthorisationRequired(): bool;
}
