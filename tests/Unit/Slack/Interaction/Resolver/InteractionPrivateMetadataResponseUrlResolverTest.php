<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Resolver;

use App\Slack\Interaction\Resolver\InteractionPrivateMetadataResponseUrlResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(InteractionPrivateMetadataResponseUrlResolver::class)]
class InteractionPrivateMetadataResponseUrlResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnNullIfPrivateMetadataCouldNotBeDecoded(): void
    {
        $request = new Request(request: [
            'view' => [
                'private_metadata' => 'not-a-json{{{{{',
            ],
        ]);

        $resolver = new InteractionPrivateMetadataResponseUrlResolver();

        $this->assertNull($resolver->resolve($request));
    }

    #[Test]
    public function itShouldReturnNullIfResponseUrlDoesNotExistOnPrivateMetadata(): void
    {
        $request = new Request(request: [
            'view' => [
                'private_metadata' => json_encode(['id' => 'id']),
            ],
        ]);

        $resolver = new InteractionPrivateMetadataResponseUrlResolver();

        $this->assertNull($resolver->resolve($request));
    }

    #[Test]
    public function itShouldReturnNullIfResponseUrlIsNotString(): void
    {
        $request = new Request(request: [
            'view' => [
                'private_metadata' => json_encode(['response_url' => 1]),
            ],
        ]);

        $resolver = new InteractionPrivateMetadataResponseUrlResolver();

        $this->assertNull($resolver->resolve($request));
    }

    #[Test]
    public function itShouldResolveResponseUrl(): void
    {
        $request = new Request(request: [
            'view' => [
                'private_metadata' => json_encode(['response_url' => $responseUrl = 'responseUrl']),
            ],
        ]);

        $resolver = new InteractionPrivateMetadataResponseUrlResolver();

        $this->assertEquals($responseUrl, $resolver->resolve($request));
    }
}
