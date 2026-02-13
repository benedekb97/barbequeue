<?php

declare(strict_types=1);

namespace App\Slack\Common\Component\Exception;

use App\Slack\Common\Component\AuthorisableInterface;

class UnauthorisedUserException extends \Exception
{
    public function __construct(
        private readonly \BackedEnum&AuthorisableInterface $enum,
    ) {
        parent::__construct();
    }

    public function getEnum(): \BackedEnum&AuthorisableInterface
    {
        return $this->enum;
    }
}
