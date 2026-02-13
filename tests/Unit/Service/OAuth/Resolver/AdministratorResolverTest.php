<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\OAuth\Resolver;

use App\Entity\Administrator;
use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\AdministratorRepositoryInterface;
use App\Resolver\UserResolver;
use App\Service\OAuth\OAuthAccessResponse;
use App\Service\OAuth\Resolver\AdministratorResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorResolver::class)]
class AdministratorResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnAdministratorFromRepository(): void
    {
        $workspace = $this->createStub(Workspace::class);

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('setWorkspace')
            ->with($workspace)
            ->willReturnSelf();

        $administrator->expects($this->once())
            ->method('setAddedBy')
            ->with(null)
            ->willReturnSelf();

        $administrator->expects($this->once())
            ->method('setUser')
            ->with($user = $this->createStub(User::class))
            ->willReturnSelf();

        $response = $this->createMock(OAuthAccessResponse::class);
        $response->expects($this->exactly(2))
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndWorkspace')
            ->with($userId, $workspace)
            ->willReturn($administrator);

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId, $workspace)
            ->willReturn($user);

        $resolver = new AdministratorResolver($repository, $userResolver);

        $result = $resolver->resolve($response, $workspace);

        $this->assertSame($administrator, $result);
    }

    #[Test]
    public function itShouldReturnNewAdministratorIfRepositoryReturnsNull(): void
    {
        $response = $this->createMock(OAuthAccessResponse::class);
        $response->expects($this->exactly(2))
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $workspace = $this->createStub(Workspace::class);

        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndWorkspace')
            ->with($userId, $workspace)
            ->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId, $workspace)
            ->willReturn($user = $this->createStub(User::class));

        $resolver = new AdministratorResolver($repository, $userResolver);

        $result = $resolver->resolve($response, $workspace);

        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertSame($user, $result->getUser());
        $this->assertNull($result->getAddedBy());
    }
}
