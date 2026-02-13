<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory;

use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(GenericFailureResponseFactory::class)]
class GenericFailureResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnSlackInteractionResponse(): void
    {
        $factory = new GenericFailureResponseFactory();

        $result = $factory->create()->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted('Something went wrong.', $blocks[0]);
    }
}
