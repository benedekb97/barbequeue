<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Resolver;

use App\Slack\Interaction\Exception\ValueUnchangedException;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\Resolver\InteractionArgumentsResolver;
use App\Slack\Interaction\Resolver\InteractionPrivateMetadataArgumentResolver;
use App\Slack\Interaction\Resolver\InteractionStateArgumentResolver;
use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(InteractionArgumentsResolver::class)]
class InteractionArgumentsResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldLogUnrecognisedInputElementExceptionOnInvalidPayload(): void
    {
        $request = $this->createStub(Request::class);
        $exception = $this->createMock(UnrecognisedInputElementException::class);
        $exception->expects($this->once())
            ->method('getInputElementType')
            ->willReturn($inputElementType = 'inputElementType');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('Received unrecognised input element '.$inputElementType);

        $stateArgumentResolver = $this->createMock(InteractionStateArgumentResolver::class);
        $stateArgumentResolver->expects($this->exactly(2))
            ->method('resolve')
            ->withAnyParameters()
            ->willReturnCallback(function ($actualRequest, $argumentKey) use ($request, $exception) {
                $this->assertSame($request, $actualRequest);
                $this->assertIsString($argumentKey);

                if ('expiry_minutes' === $argumentKey) {
                    throw $exception;
                }

                throw new ValueUnchangedException();
            });

        $privateMetadataArgumentResolver = $this->createMock(InteractionPrivateMetadataArgumentResolver::class);
        $privateMetadataArgumentResolver->expects($this->once())
            ->method('resolve')
            ->with($request, $queueArgumentKey = 'queue')
            ->willReturn($queueName = 'queueName');

        $resolver = new InteractionArgumentsResolver(
            $logger,
            $stateArgumentResolver,
            $privateMetadataArgumentResolver,
        );

        $result = $resolver->resolve(Interaction::EDIT_QUEUE, $request);

        $this->assertArrayHasKey($queueArgumentKey, $result);
        $this->assertEquals($queueName, $result[$queueArgumentKey]);
    }
}
