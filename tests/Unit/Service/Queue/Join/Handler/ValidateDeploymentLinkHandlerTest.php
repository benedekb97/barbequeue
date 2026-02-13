<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\InvalidDeploymentUrlException;
use App\Service\Queue\Join\Handler\ValidateDeploymentLinkHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use App\Validator\UrlValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ValidateDeploymentLinkHandler::class)]
class ValidateDeploymentLinkHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinQueueContextWithDeploymentQueue(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $handler = new ValidateDeploymentLinkHandler($this->getLogger(), $this->createStub(UrlValidator::class));

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportJoinQueueContextWithSimpleQueue(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new ValidateDeploymentLinkHandler($this->getLogger(), $this->createStub(UrlValidator::class));

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportGenericQueueContext(): void
    {
        $handler = new ValidateDeploymentLinkHandler($this->getLogger(), $this->createStub(UrlValidator::class));

        $this->assertFalse($handler->supports($this->createStub(QueueContextInterface::class)));
    }

    #[Test]
    public function itShouldReturnEarlyIfContextNotJoinQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new ValidateDeploymentLinkHandler($this->getLogger(), $this->createStub(UrlValidator::class));

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowInvalidDeploymentUrlExceptionIfValidatorReturnsNull(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturN($contextType = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getDeploymentLink')
            ->willReturN($deploymentLink = 'deploymentLink');

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $validator = $this->createMock(UrlValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($deploymentLink)
            ->willReturn(null);

        $this->expectException(InvalidDeploymentUrlException::class);

        $this->expectsDebug('Validating deployment link for {contextId} {contextType}', [
            'contextId' => $contextId,
            'contextType' => $contextType,
        ]);

        $handler = new ValidateDeploymentLinkHandler($this->getLogger(), $validator);

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetValidatedDeploymentLinkOnContext(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturN($contextType = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getDeploymentLink')
            ->willReturN($deploymentLink = 'deploymentLink');

        $context->expects($this->once())
            ->method('setDeploymentLink')
            ->with($deploymentLink);

        $validator = $this->createMock(UrlValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($deploymentLink)
            ->willReturn($deploymentLink);

        $this->expectsDebug('Validating deployment link for {contextId} {contextType}', [
            'contextId' => $contextId,
            'contextType' => $contextType,
        ]);

        $handler = new ValidateDeploymentLinkHandler($this->getLogger(), $validator);

        $handler->handle($context);
    }
}
