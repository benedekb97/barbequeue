<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Leave;

use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Leave\LeaveQueueContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(LeaveQueueContext::class)]
class LeaveQueueContextTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $context = new LeaveQueueContext(
            $queueName = 'queueName',
            $teamId = 'teamId',
            $userId = 'userId',
            $queuedUserId = 1,
        );

        $this->assertSame($queueName, $context->getQueueIdentifier());
        $this->assertSame($teamId, $context->getTeamId());
        $this->assertSame($userId, $context->getUserId());
        $this->assertSame($queuedUserId, $context->getQueuedUserId());
        $this->assertEquals(ContextType::LEAVE, $context->getType());

        $context->setQueuedUser($queuedUser = $this->createStub(QueuedUser::class));
        $this->assertSame($queuedUser, $context->getQueuedUser());

        $context->setRepository($repository = $this->createStub(Repository::class));
        $this->assertSame($repository, $context->getRepository());
    }
}
