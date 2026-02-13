<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Configuration;

use App\Entity\User;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\Configuration\UserNameDefaultValueResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UserNameDefaultValueResolver::class)]
class UserNameDefaultValueResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldSupportConfigurationUserNameArgument(): void
    {
        $resolver = new UserNameDefaultValueResolver();

        $this->assertEquals(ModalArgument::CONFIGURATION_USER_NAME, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveArrayNull(): void
    {
        $resolver = new UserNameDefaultValueResolver();

        $this->assertNull($resolver->resolveArray());
    }

    #[Test]
    public function itShouldResolveStringToUserName(): void
    {
        $resolver = new UserNameDefaultValueResolver();

        $this->assertNull($resolver->resolveString());

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getName')
            ->willReturn($userName = 'userName');

        $resolver->setUser($user);

        $this->assertSame($userName, $resolver->resolveString());
    }
}
