<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Response\Interaction\Factory\Repository\RepositoryAlreadyExistsResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryAlreadyExistsResponseFactory::class)]
class RepositoryAlreadyExistsResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreatePrivateMessage(): void
    {
        $factory = new RepositoryAlreadyExistsResponseFactory();

        $name = 'name';

        $result = $factory->create($name);

        $result = $result->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'A repository called `name` already exists!',
            $blocks[0]
        );
    }
}
