<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Handler;

use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Handler\FlushEntitiesHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FlushEntitiesHandler::class)]
class FlushEntitiesHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportAllContexts(): void
    {
        $handler = new FlushEntitiesHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($this->createStub(QueueContextInterface::class)));
    }

    #[Test]
    public function itShouldFlushEntities(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('flush')
            ->with();

        $this->expectsDebug('Flushing entities for {contextId} {contextType}', [
            'contextId' => $contextId = 'contextId',
            'contextType' => $contextType = ContextType::EDIT,
        ]);

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId);

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType);

        $handler = new FlushEntitiesHandler($entityManager, $this->getLogger());

        $handler->handle($context);
    }
}
