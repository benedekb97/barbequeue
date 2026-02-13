<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection;

use App\Entity\QueuedUser;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\SimpleQueuedUserSectionFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SimpleQueuedUserSectionFactory::class)]
class SimpleQueuedUserSectionFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSectionBlockWithExpiry(): void
    {
        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $queuedUser->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(10);

        $factory = new SimpleQueuedUserSectionFactory();

        $result = $factory->create($queuedUser, 1)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            '#1 - userLink Reserved for 10 minutes',
            $result
        );
    }

    #[Test]
    public function itShouldCreateSectionBlockWithoutExpiry(): void
    {
        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $queuedUser->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(null);

        $factory = new SimpleQueuedUserSectionFactory();

        $result = $factory->create($queuedUser, 1)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            '#1 - userLink ',
            $result
        );
    }
}
