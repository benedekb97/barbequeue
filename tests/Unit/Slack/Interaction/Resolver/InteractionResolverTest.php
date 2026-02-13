<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Resolver;

use App\Slack\Interaction\Exception\UnhandledInteractionTypeException;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\Resolver\InteractionResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(InteractionResolver::class)]
class InteractionResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowUnhandledInteractionTypeExceptionIfInteractionTypeIsNotImplemented(): void
    {
        $request = $this->createStub(Request::class);
        $resolver = new InteractionResolver();

        $this->expectException(UnhandledInteractionTypeException::class);

        try {
            $resolver->resolve(InteractionType::SHORTCUT, $request);
        } catch (UnhandledInteractionTypeException $exception) {
            $this->assertEquals(InteractionType::SHORTCUT, $exception->getInteractionType());

            throw $exception;
        }
    }

    #[Test, DataProvider('provideActionInteractionTypes')]
    public function itShouldThrowValueErrorIfInteractionUnknownForActionTypes(InteractionType $type): void
    {
        $request = new Request(request: [
            'actions' => [
                [
                    'action_id' => 'unexpected-action-id-1',
                ],
            ],
        ]);
        $resolver = new InteractionResolver();

        $this->expectException(\ValueError::class);

        $resolver->resolve($type, $request);
    }

    #[Test, DataProvider('provideActionInteractionTypes')]
    public function itShouldReturnCorrectInteractionForActionTypes(InteractionType $type): void
    {
        $request = new Request(request: [
            'actions' => [
                [
                    'action_id' => ($interaction = Interaction::EDIT_QUEUE_ACTION)->value.'-1',
                ],
            ],
        ]);
        $resolver = new InteractionResolver();

        $result = $resolver->resolve($type, $request);

        $this->assertEquals($interaction, $result);
    }

    public static function provideActionInteractionTypes(): iterable
    {
        return [
            [InteractionType::BLOCK_ACTIONS],
            [InteractionType::MESSAGE_ACTIONS],
        ];
    }

    #[Test, DataProvider('provideViewInteractionTypes')]
    public function itShouldThrowValueErrorIfActionUnknownForViewTypes(InteractionType $type): void
    {
        $request = new Request(request: [
            'view' => [
                'private_metadata' => json_encode([
                    'action' => 'unknown-action',
                ]),
            ],
        ]);
        $resolver = new InteractionResolver();

        $this->expectException(\ValueError::class);

        $resolver->resolve($type, $request);
    }

    #[Test, DataProvider('provideViewInteractionTypes')]
    public function itShouldReturnInteractionForViewTypes(InteractionType $type): void
    {
        $request = new Request(request: [
            'view' => [
                'private_metadata' => json_encode([
                    'action' => ($interaction = Interaction::EDIT_QUEUE)->value,
                ]),
            ],
        ]);
        $resolver = new InteractionResolver();

        $result = $resolver->resolve($type, $request);

        $this->assertEquals($interaction, $result);
    }

    public static function provideViewInteractionTypes(): iterable
    {
        return [
            [InteractionType::VIEW_CLOSED],
            [InteractionType::VIEW_SUBMISSION],
        ];
    }
}
