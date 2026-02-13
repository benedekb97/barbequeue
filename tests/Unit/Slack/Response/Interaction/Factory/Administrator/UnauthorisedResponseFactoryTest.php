<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Administrator;

use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnauthorisedResponseFactory::class)]
class UnauthorisedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnSlackInteractionResponse(): void
    {
        $response = new UnauthorisedResponseFactory()->create()->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'You are not allowed to do that!',
            $blocks[0],
        );
    }
}
