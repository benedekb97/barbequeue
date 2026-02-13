<?php

declare(strict_types=1);

namespace App\Slack\Common\Validator;

use App\Entity\Administrator;
use App\Repository\AdministratorRepositoryInterface;
use App\Slack\Common\Component\AuthorisableInterface;
use App\Slack\Common\Component\Exception\UnauthorisedUserException;

readonly class AuthorisationValidator
{
    public function __construct(
        private AdministratorRepositoryInterface $administratorRepository,
    ) {
    }

    /** @throws UnauthorisedUserException */
    public function validate(\BackedEnum&AuthorisableInterface $authorisable, string $userId, string $teamId): ?Administrator
    {
        if (!$authorisable->isAuthorisationRequired()) {
            return null;
        }

        $administrator = $this->administratorRepository->findOneByUserIdAndTeamId($userId, $teamId);

        if ($administrator instanceof Administrator) {
            return $administrator;
        }

        throw new UnauthorisedUserException($authorisable);
    }
}
