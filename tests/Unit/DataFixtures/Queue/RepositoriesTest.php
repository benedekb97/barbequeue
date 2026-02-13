<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataFixtures\Queue;

use App\DataFixtures\Queue\Repositories;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Repositories::class)]
class RepositoriesTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveTwoCases(): void
    {
        $this->assertCount(2, Repositories::cases());
    }

    #[Test, DataProvider('provideUrls')]
    public function itShouldReturnCorrectUrl(Repositories $repository, ?string $url): void
    {
        $this->assertEquals($url, $repository->getUrl());
    }

    public static function provideUrls(): array
    {
        return [
            [Repositories::REPOSITORY_A, 'url'],
            [Repositories::REPOSITORY_B, null],
        ];
    }
}
