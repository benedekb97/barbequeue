<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Administrator;

use App\Entity\Administrator;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorAlreadyExistsResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorAlreadyExistsResponseFactory::class)]
class AdministratorAlreadyExistsResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnSlackInteractionResponse(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $factory = new AdministratorAlreadyExistsResponseFactory();

        $result = $factory->create($administrator)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'userLink is already an administrator.',
            $blocks[0],
        );
    }
}
