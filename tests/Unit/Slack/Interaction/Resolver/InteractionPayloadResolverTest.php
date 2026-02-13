<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Resolver;

use App\Slack\Interaction\Exception\InvalidPayloadException;
use App\Slack\Interaction\Resolver\InteractionPayloadResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(InteractionPayloadResolver::class)]
class InteractionPayloadResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowInvalidPayloadExceptionIfPayloadCannotBeDecoded(): void
    {
        $request = new Request();
        $request->request->set('payload', 'invalid-payload');

        $resolver = new InteractionPayloadResolver(
            $this->createStub(LoggerInterface::class),
        );

        $this->expectException(InvalidPayloadException::class);

        $resolver->resolve($request);
    }

    #[Test]
    public function itShouldPutEverythingInTheJsonEncodedPayloadFieldOfTheInitialRequestIntoAnotherRequest(): void
    {
        $request = new Request();
        $payload = [
            'someData' => [
                'someMoreData' => 'this is text',
                'someAdditionalData' => 3,
            ],
        ];
        $request->request->set('payload', json_encode($payload));

        $resolver = new InteractionPayloadResolver(
            $this->createStub(LoggerInterface::class),
        );

        $payloadRequest = $resolver->resolve($request);

        $this->assertEquals($payload, $payloadRequest->request->all());
    }
}
