<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit;

use App\Entity\Repository;
use App\Enum\QueueBehaviour;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Edit\EditQueueContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(EditQueueContext::class)]
class EditQueueContextTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $context = new EditQueueContext(
            $queueId = 1,
            $teamId = 'teamId',
            $userId = 'userId',
            $maxEntries = 1,
            $expiryMinutes = 1,
            $repositoryIds = [1],
            $queueBehaviour = 'queueBehaviour',
        );

        $this->assertSame($queueId, $context->getQueueIdentifier());
        $this->assertSame($teamId, $context->getTeamId());
        $this->assertSame($userId, $context->getUserId());
        $this->assertSame($maxEntries, $context->getMaximumEntriesPerUser());
        $this->assertSame($expiryMinutes, $context->getExpiryMinutes());
        $this->assertSame($repositoryIds, $context->getRepositoryIds());
        $this->assertSame($queueBehaviour, $context->getQueueBehaviour());

        $context->setExpiryMinutes($expiryMinutes = 0);

        $this->assertSame($expiryMinutes, $context->getExpiryMinutes());

        $this->assertEquals(ContextType::EDIT, $context->getType());

        $context->setBehaviour($behaviour = QueueBehaviour::ALLOW_SIMULTANEOUS);
        $this->assertSame($behaviour, $context->getBehaviour());

        $context->addRepository($repository = $this->createStub(Repository::class));
        $this->assertCount(1, $context->getRepositories());
        $this->assertSame($repository, $context->getRepositories()->first());
    }
}
