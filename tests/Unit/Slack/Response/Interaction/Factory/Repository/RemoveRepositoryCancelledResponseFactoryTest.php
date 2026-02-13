<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Response\Interaction\Factory\Repository\RemoveRepositoryCancelledResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RemoveRepositoryCancelledResponseFactory::class)]
class RemoveRepositoryCancelledResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $factory = new RemoveRepositoryCancelledResponseFactory();

        $result = $factory->create()->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'Operation cancelled.',
            $blocks[0],
        );

        $this->assertArrayHasKey('replace_original', $result);
        $this->assertTrue($result['replace_original']);
    }
}
