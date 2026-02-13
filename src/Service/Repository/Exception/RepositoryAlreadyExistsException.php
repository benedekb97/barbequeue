<?php

declare(strict_types=1);

namespace App\Service\Repository\Exception;

class RepositoryAlreadyExistsException extends \Exception
{
    public function __construct(private readonly string $name)
    {
        parent::__construct();
    }

    public function getName(): string
    {
        return $this->name;
    }
}
