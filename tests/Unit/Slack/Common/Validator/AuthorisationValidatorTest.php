<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Common\Validator;

use App\Entity\Administrator;
use App\Repository\AdministratorRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Common\Component\Exception\UnauthorisedUserException;
use App\Slack\Common\Validator\AuthorisationValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AuthorisationValidator::class)]
class AuthorisationValidatorTest extends KernelTestCase
{
    #[Test]
    public function itShouldNotCheckRepositoryIfAuthorisationNotRequired(): void
    {
        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->never())
            ->method('findOneByUserIdAndTeamId')
            ->withAnyParameters();

        $validator = new AuthorisationValidator($repository);

        $validator->validate(Command::BBQ, '', '');
    }

    #[Test]
    public function itShouldThrowUnauthorisedUserExceptionIfAuthorisationRequiredAndUserCouldNotBeFound(): void
    {
        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndTeamId')
            ->with($userId = 'userId', $teamId = 'teamId')
            ->willReturn(null);

        $this->expectException(UnauthorisedUserException::class);

        $validator = new AuthorisationValidator($repository);

        $validator->validate(Command::BBQ_ADMIN, $userId, $teamId);
    }

    #[Test]
    public function itShouldReturnAdministratorIfAuthorisationRequired(): void
    {
        $administrator = $this->createStub(Administrator::class);

        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndTeamId')
            ->with($userId = 'userId', $teamId = 'teamId')
            ->willReturn($administrator);

        $validator = new AuthorisationValidator($repository);

        $result = $validator->validate(Command::BBQ_ADMIN, $userId, $teamId);

        $this->assertSame($result, $administrator);
    }
}
