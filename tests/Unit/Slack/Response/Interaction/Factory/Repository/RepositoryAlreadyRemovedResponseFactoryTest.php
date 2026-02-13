<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Response\Interaction\Factory\Repository\RepositoryAlreadyRemovedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryAlreadyRemovedResponseFactory::class)]
class RepositoryAlreadyRemovedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $result = new RepositoryAlreadyRemovedResponseFactory()->create()->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'The repository you are trying to remove does not exist. It may have been removed by someone else.',
            $blocks[0],
        );

        $this->assertArrayHasKey('replace_original', $result);
        $this->assertTrue($result['replace_original']);
    }
}
