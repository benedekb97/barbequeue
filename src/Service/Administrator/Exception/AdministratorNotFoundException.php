<?php

declare(strict_types=1);

namespace App\Service\Administrator\Exception;

class AdministratorNotFoundException extends \Exception
{
    public function __construct(
        private readonly string $userId,
    ) {
        parent::__construct();
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
