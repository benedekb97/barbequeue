<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Administrator;

use App\Slack\Response\Interaction\Factory\Administrator\AdministratorRemovedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorRemovedResponseFactory::class)]
class AdministratorRemovedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $factory = new AdministratorRemovedResponseFactory();

        $response = $factory->create($userId = 'userId')->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            '<@'.$userId.'> has been removed as an administrator.',
            $blocks[0]
        );
    }
}
