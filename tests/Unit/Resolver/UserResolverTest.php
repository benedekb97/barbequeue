<?php

declare(strict_types=1);

namespace App\Tests\Unit\Resolver;

use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\UserRepositoryInterface;
use App\Resolver\UserResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UserResolver::class)]
class UserResolverTest extends KernelTestCase
{
    #[Test, DataProvider('provideNames')]
    public function itShouldReturnNewUserIfNotFoundOnRepository(?string $userName): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'slackId' => $userId = 'userId',
                'workspace' => $workspace = $this->createStub(Workspace::class),
            ])
            ->willReturn(null);

        $resolver = new UserResolver($repository);

        $result = $resolver->resolve($userId, $workspace, $userName);

        $this->assertSame($userId, $result->getSlackId());
        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertNotNull($result->getNotificationSettings());
        $this->assertEquals($userName, $result->getName());
    }

    public static function provideNames(): array
    {
        return [
            ['name'],
            [null],
        ];
    }

    #[Test]
    public function itShouldNotSetNameIfUserHasOneSet(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getName')
            ->willReturn('userName');

        $user->expects($this->never())
            ->method('setName');

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'slackId' => $userId = 'userId',
                'workspace' => $workspace = $this->createStub(Workspace::class),
            ])
            ->willReturn($user);

        $resolver = new UserResolver($repository);

        $result = $resolver->resolve($userId, $workspace, 'newName');

        $this->assertSame($user, $result);
    }
}
