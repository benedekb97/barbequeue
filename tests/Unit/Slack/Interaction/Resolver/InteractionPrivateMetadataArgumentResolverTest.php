<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Resolver;

use App\Slack\Interaction\Resolver\InteractionPrivateMetadataArgumentResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(InteractionPrivateMetadataArgumentResolver::class)]
class InteractionPrivateMetadataArgumentResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnNullIfArgumentDoesNotExistOnPrivateMetadata(): void
    {
        $privateMetadata = ['key' => 'value'];
        $request = new Request(request: [
            'view' => [
                'private_metadata' => json_encode($privateMetadata),
            ],
        ]);

        $resolver = new InteractionPrivateMetadataArgumentResolver();

        $result = $resolver->resolve($request, 'non-existent-key');

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnIntegerIfArgumentExistsOnPrivateMetadataAndIsInteger(): void
    {
        $privateMetadata = [$key = 'key' => $value = 1];
        $request = new Request(request: [
            'view' => [
                'private_metadata' => json_encode($privateMetadata),
            ],
        ]);

        $resolver = new InteractionPrivateMetadataArgumentResolver();

        $result = $resolver->resolve($request, $key);

        $this->assertIsInt($result);
        $this->assertEquals($value, $result);
    }

    #[Test]
    public function itShouldReturnStringIfArgumentExistsOnPrivateMetadataAndIsString(): void
    {
        $privateMetadata = [$key = 'key' => $value = 'string'];
        $request = new Request(request: [
            'view' => [
                'private_metadata' => json_encode($privateMetadata),
            ],
        ]);

        $resolver = new InteractionPrivateMetadataArgumentResolver();

        $result = $resolver->resolve($request, $key);

        $this->assertIsString($result);
        $this->assertEquals($value, $result);
    }
}
