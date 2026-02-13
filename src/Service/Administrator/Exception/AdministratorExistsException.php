<?php

declare(strict_types=1);

namespace App\Service\Administrator\Exception;

use App\Entity\Administrator;

class AdministratorExistsException extends \Exception
{
    public function __construct(
        private readonly Administrator $administrator,
    ) {
        parent::__construct();
    }

    public function getAdministrator(): Administrator
    {
        return $this->administrator;
    }
}
