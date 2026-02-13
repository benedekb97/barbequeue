<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Administrator;

use App\Slack\Response\Interaction\Factory\Administrator\AdministratorNotFoundResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorNotFoundResponseFactory::class)]
class AdministratorNotFoundResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $factory = new AdministratorNotFoundResponseFactory();

        $response = $factory->create($userId = 'userId')->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            '<@'.$userId.'> is not an administrator.',
            $blocks[0]
        );
    }
}
