<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Repository;

use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\Repository\NameDefaultValueResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(NameDefaultValueResolver::class)]
class NameDefaultValueResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldSupportRepositoryNameArgument(): void
    {
        $resolver = new NameDefaultValueResolver();

        $this->assertEquals(ModalArgument::REPOSITORY_NAME, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveNullArray(): void
    {
        $resolver = new NameDefaultValueResolver();

        $this->assertNull($resolver->resolveArray());
    }

    #[Test]
    public function itShouldResolveNullIfRepositoryNotSet(): void
    {
        $resolver = new NameDefaultValueResolver();

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveRepositoryName(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $resolver = new NameDefaultValueResolver()
            ->setRepository($repository);

        $this->assertEquals('repositoryName', $resolver->resolveString());
    }
}
