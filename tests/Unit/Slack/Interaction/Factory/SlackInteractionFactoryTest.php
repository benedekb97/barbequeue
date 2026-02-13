<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Factory;

use App\Slack\Interaction\Exception\InvalidPayloadException;
use App\Slack\Interaction\Exception\UnhandledInteractionTypeException;
use App\Slack\Interaction\Factory\SlackInteractionFactory;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\Resolver\InteractionArgumentsResolver;
use App\Slack\Interaction\Resolver\InteractionPayloadResolver;
use App\Slack\Interaction\Resolver\InteractionPrivateMetadataResponseUrlResolver;
use App\Slack\Interaction\Resolver\InteractionResolver;
use App\Slack\Interaction\Resolver\InteractionTypeResolver;
use App\Slack\Interaction\SlackViewSubmission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SlackInteractionFactory::class)]
class SlackInteractionFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowInvalidPayloadExceptionIfPayloadCouldNotBeResolved(): void
    {
        $request = $this->createStub(Request::class);
        $exception = $this->createStub(InvalidPayloadException::class);

        $payloadResolver = $this->createMock(InteractionPayloadResolver::class);
        $payloadResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $factory = new SlackInteractionFactory(
            $payloadResolver,
            $this->createStub(InteractionTypeResolver::class),
            $this->createStub(InteractionResolver::class),
            $this->createStub(InteractionArgumentsResolver::class),
            $this->createStub(InteractionPrivateMetadataResponseUrlResolver::class),
        );

        $factory->create($request);
    }

    #[Test]
    public function itShouldThrowValueErrorIfInteractionTypeCouldNotBeResolved(): void
    {
        $request = $this->createStub(Request::class);
        $payload = new Request();
        $exception = $this->createStub(\ValueError::class);

        $payloadResolver = $this->createMock(InteractionPayloadResolver::class);
        $payloadResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($payload);

        $interactionTypeResolver = $this->createMock(InteractionTypeResolver::class);
        $interactionTypeResolver->expects($this->once())
            ->method('resolve')
            ->with($payload)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $factory = new SlackInteractionFactory(
            $payloadResolver,
            $interactionTypeResolver,
            $this->createStub(InteractionResolver::class),
            $this->createStub(InteractionArgumentsResolver::class),
            $this->createStub(InteractionPrivateMetadataResponseUrlResolver::class),
        );

        $factory->create($request);
    }

    #[Test]
    public function itShouldThrowUnhandledInteractionTypeExceptionIfInteractionTypeIsNotImplemented(): void
    {
        $request = $this->createStub(Request::class);
        $payload = new Request();
        $exception = $this->createStub(UnhandledInteractionTypeException::class);

        $payloadResolver = $this->createMock(InteractionPayloadResolver::class);
        $payloadResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($payload);

        $interactionTypeResolver = $this->createMock(InteractionTypeResolver::class);
        $interactionTypeResolver->expects($this->once())
            ->method('resolve')
            ->with($payload)
            ->willReturn($type = InteractionType::SHORTCUT);

        $interactionResolver = $this->createMock(InteractionResolver::class);
        $interactionResolver->expects($this->once())
            ->method('resolve')
            ->with($type, $payload)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $factory = new SlackInteractionFactory(
            $payloadResolver,
            $interactionTypeResolver,
            $interactionResolver,
            $this->createStub(InteractionArgumentsResolver::class),
            $this->createStub(InteractionPrivateMetadataResponseUrlResolver::class),
        );

        $factory->create($request);
    }

    #[Test]
    public function itShouldThrowValueErrorIfInteractionCouldNotBeResolved(): void
    {
        $request = $this->createStub(Request::class);
        $payload = new Request();
        $exception = $this->createStub(\ValueError::class);

        $payloadResolver = $this->createMock(InteractionPayloadResolver::class);
        $payloadResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($payload);

        $interactionTypeResolver = $this->createMock(InteractionTypeResolver::class);
        $interactionTypeResolver->expects($this->once())
            ->method('resolve')
            ->with($payload)
            ->willReturn($type = InteractionType::BLOCK_ACTIONS);

        $interactionResolver = $this->createMock(InteractionResolver::class);
        $interactionResolver->expects($this->once())
            ->method('resolve')
            ->with($type, $payload)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $factory = new SlackInteractionFactory(
            $payloadResolver,
            $interactionTypeResolver,
            $interactionResolver,
            $this->createStub(InteractionArgumentsResolver::class),
            $this->createStub(InteractionPrivateMetadataResponseUrlResolver::class),
        );

        $factory->create($request);
    }

    #[Test, DataProvider('provideSlackInteractionTypes')]
    public function itShouldReturnSlackInteraction(InteractionType $type): void
    {
        $request = $this->createStub(Request::class);
        $payload = new Request(request: [
            'team' => [
                'id' => $teamId = 'teamId',
            ],
            'user' => [
                'id' => $userId = 'userId',
                'username' => $username = 'username',
            ],
            'view' => [
                'something' => 'something',
            ],
            'response_url' => $responseUrl = 'responseUrl',
            'trigger_id' => $triggerId = 'triggerId',
            'actions' => [
                [
                    'value' => $value = 'value',
                ],
            ],
        ]);

        $payloadResolver = $this->createMock(InteractionPayloadResolver::class);
        $payloadResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($payload);

        $interactionTypeResolver = $this->createMock(InteractionTypeResolver::class);
        $interactionTypeResolver->expects($this->once())
            ->method('resolve')
            ->with($payload)
            ->willReturn($type);

        $interactionResolver = $this->createMock(InteractionResolver::class);
        $interactionResolver->expects($this->once())
            ->method('resolve')
            ->with($type, $payload)
            ->willReturn($interaction = Interaction::EDIT_QUEUE);

        $factory = new SlackInteractionFactory(
            $payloadResolver,
            $interactionTypeResolver,
            $interactionResolver,
            $this->createStub(InteractionArgumentsResolver::class),
            $this->createStub(InteractionPrivateMetadataResponseUrlResolver::class),
        );

        $slackInteraction = $factory->create($request);

        $this->assertNotInstanceOf(SlackViewSubmission::class, $slackInteraction);
        $this->assertSame($type, $slackInteraction->getType());
        $this->assertSame($interaction, $slackInteraction->getInteraction());
        $this->assertSame($teamId, $slackInteraction->getTeamId());
        $this->assertSame($username, $slackInteraction->getUserName());
        $this->assertSame($responseUrl, $slackInteraction->getResponseUrl());
        $this->assertSame($triggerId, $slackInteraction->getTriggerId());
        $this->assertSame($userId, $slackInteraction->getUserId());
        $this->assertSame($value, $slackInteraction->getValue());
        $this->assertNull($slackInteraction->getViewId());
    }

    #[Test]
    public function itShouldReturnSlackInteractionIfValueIsSelectedOptions(): void
    {
        $request = $this->createStub(Request::class);
        $payload = new Request(request: [
            'team' => [
                'id' => $teamId = 'teamId',
            ],
            'user' => [
                'id' => $userId = 'userId',
                'name' => $userName = 'userName',
            ],
            'response_url' => $responseUrl = 'responseUrl',
            'trigger_id' => $triggerId = 'triggerId',
            'actions' => [
                [
                    'selected_option' => [
                        'value' => $value = 'value',
                    ],
                ],
            ],
            'view' => [
                'id' => $viewId = 'viewId',
            ],
        ]);

        $payloadResolver = $this->createMock(InteractionPayloadResolver::class);
        $payloadResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($payload);

        $interactionTypeResolver = $this->createMock(InteractionTypeResolver::class);
        $interactionTypeResolver->expects($this->once())
            ->method('resolve')
            ->with($payload)
            ->willReturn($type = InteractionType::BLOCK_ACTIONS);

        $interactionResolver = $this->createMock(InteractionResolver::class);
        $interactionResolver->expects($this->once())
            ->method('resolve')
            ->with($type, $payload)
            ->willReturn($interaction = Interaction::EDIT_QUEUE);

        $factory = new SlackInteractionFactory(
            $payloadResolver,
            $interactionTypeResolver,
            $interactionResolver,
            $this->createStub(InteractionArgumentsResolver::class),
            $this->createStub(InteractionPrivateMetadataResponseUrlResolver::class),
        );

        $slackInteraction = $factory->create($request);

        $this->assertNotInstanceOf(SlackViewSubmission::class, $slackInteraction);
        $this->assertSame($type, $slackInteraction->getType());
        $this->assertSame($interaction, $slackInteraction->getInteraction());
        $this->assertSame($teamId, $slackInteraction->getTeamId());
        $this->assertSame($userName, $slackInteraction->getUserName());
        $this->assertSame($responseUrl, $slackInteraction->getResponseUrl());
        $this->assertSame($triggerId, $slackInteraction->getTriggerId());
        $this->assertSame($userId, $slackInteraction->getUserId());
        $this->assertSame($value, $slackInteraction->getValue());
        $this->assertSame($viewId, $slackInteraction->getViewId());
    }

    public static function provideSlackInteractionTypes(): array
    {
        return [
            [InteractionType::BLOCK_ACTIONS],
            [InteractionType::MESSAGE_ACTIONS],
        ];
    }

    #[Test, DataProvider('provideSlackViewSubmissionTypes')]
    public function itShouldReturnSlackViewSubmission(InteractionType $type): void
    {
        $request = $this->createStub(Request::class);
        $payload = new Request(request: [
            'team' => [
                'id' => $teamId = 'teamId',
            ],
            'user' => [
                'id' => $userId = 'userId',
                'name' => $userName = 'userName',
            ],
            'trigger_id' => $triggerId = 'triggerId',
            'view' => [
                'id' => $viewId = 'viewId',
                'private_metadata' => json_encode(['response_url' => $responseUrl = 'responseUrl']),
            ],
        ]);

        $payloadResolver = $this->createMock(InteractionPayloadResolver::class);
        $payloadResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($payload);

        $interactionTypeResolver = $this->createMock(InteractionTypeResolver::class);
        $interactionTypeResolver->expects($this->once())
            ->method('resolve')
            ->with($payload)
            ->willReturn($type);

        $interactionResolver = $this->createMock(InteractionResolver::class);
        $interactionResolver->expects($this->once())
            ->method('resolve')
            ->with($type, $payload)
            ->willReturn($interaction = Interaction::EDIT_QUEUE);

        $argumentsResolver = $this->createMock(InteractionArgumentsResolver::class);
        $argumentsResolver->expects($this->once())
            ->method('resolve')
            ->with($interaction, $payload)
            ->willReturn($arguments = ['argument']);

        $responseUrlResolver = $this->createMock(InteractionPrivateMetadataResponseUrlResolver::class);
        $responseUrlResolver->expects($this->once())
            ->method('resolve')
            ->with($payload)
            ->willReturn($responseUrl);

        $factory = new SlackInteractionFactory(
            $payloadResolver,
            $interactionTypeResolver,
            $interactionResolver,
            $argumentsResolver,
            $responseUrlResolver,
        );

        $viewSubmission = $factory->create($request);

        $this->assertInstanceOf(SlackViewSubmission::class, $viewSubmission);

        $this->assertSame($interaction, $viewSubmission->getInteraction());
        $this->assertSame($teamId, $viewSubmission->getTeamId());
        $this->assertSame($userId, $viewSubmission->getUserId());
        $this->assertSame($userName, $viewSubmission->getUserName());
        $this->assertSame($triggerId, $viewSubmission->getTriggerId());
        $this->assertSame($arguments, $viewSubmission->getArguments());
        $this->assertNull($viewSubmission->getViewId());
        $this->assertEquals($responseUrl, $viewSubmission->getResponseUrl());
    }

    public static function provideSlackViewSubmissionTypes(): array
    {
        return [
            [InteractionType::VIEW_SUBMISSION],
            [InteractionType::VIEW_CLOSED],
        ];
    }
}
