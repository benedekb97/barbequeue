<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Repository;

use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\Repository\UrlDefaultValueResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UrlDefaultValueResolver::class)]
class UrlDefaultValueResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldSupportRepositoryNameArgument(): void
    {
        $resolver = new UrlDefaultValueResolver();

        $this->assertEquals(ModalArgument::REPOSITORY_URL, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveNullArray(): void
    {
        $resolver = new UrlDefaultValueResolver();

        $this->assertNull($resolver->resolveArray());
    }

    #[Test]
    public function itShouldResolveNullIfRepositoryNotSet(): void
    {
        $resolver = new UrlDefaultValueResolver();

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveRepositoryName(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getUrl')
            ->willReturn('repositoryUrl');

        $resolver = new UrlDefaultValueResolver()
            ->setRepository($repository);

        $this->assertEquals('repositoryUrl', $resolver->resolveString());
    }
}
