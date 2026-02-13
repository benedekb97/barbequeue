<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Response\Interaction\Factory\Repository\RepositoryRemovedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryRemovedResponseFactory::class)]
class RepositoryRemovedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $result = new RepositoryRemovedResponseFactory()->create($name = 'name')->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            '`name` has been removed from your repositories.',
            $blocks[0],
        );

        $this->assertArrayHasKey('replace_original', $result);
        $this->assertTrue($result['replace_original']);
    }
}
