<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Resolver;

use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\Resolver\InteractionTypeResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(InteractionTypeResolver::class)]
class InteractionTypeResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowValueErrorIfUnrecognisedInteractionType(): void
    {
        $request = new Request(request: [
            'type' => 'non-existent-type',
        ]);
        $resolver = new InteractionTypeResolver();

        $this->expectException(\ValueError::class);

        $resolver->resolve($request);
    }

    #[Test, DataProvider('provideInteractionTypes')]
    public function itShouldReturnInteractionType(InteractionType $type): void
    {
        $request = new Request(request: [
            'type' => $type->value,
        ]);
        $resolver = new InteractionTypeResolver();

        $result = $resolver->resolve($request);

        $this->assertEquals($type, $result);
    }

    public static function provideInteractionTypes(): iterable
    {
        foreach (InteractionType::cases() as $case) {
            yield [$case];
        }
    }
}
