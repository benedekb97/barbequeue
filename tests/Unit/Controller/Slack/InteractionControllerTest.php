<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Slack;

use App\Controller\Slack\InteractionController;
use App\Message\Slack\SlackInteractionMessage;
use App\Slack\Interaction\Exception\UnhandledInteractionTypeException;
use App\Slack\Interaction\Factory\SlackInteractionFactory;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(InteractionController::class)]
class InteractionControllerTest extends LoggerAwareTestCase
{
    #[Test, DataProvider('provideForItShouldReturnEmptyResponseIfExceptionThrown')]
    public function itShouldReturnEmptyResponseIfExceptionThrown(
        \Throwable $exception,
        string $logMessage,
        array $logContext,
    ): void {
        $request = $this->createStub(Request::class);

        $factory = $this->createMock(SlackInteractionFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($request)
            ->willThrowException($exception);

        $this->expectsWarning($logMessage, $logContext);

        $controller = new InteractionController($factory, $this->createStub(MessageBusInterface::class));

        $controller->setLogger($this->getLogger());

        $response = $controller($request);

        $this->assertEmpty($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public static function provideForItShouldReturnEmptyResponseIfExceptionThrown(): iterable
    {
        return [
            [
                new UnhandledInteractionTypeException(InteractionType::BLOCK_ACTIONS),
                'Received unhandled interaction type {interaction}.',
                [
                    'interaction' => InteractionType::BLOCK_ACTIONS->value,
                ],
            ],
            [
                new \ValueError($message = 'message'),
                'Could not resolve requested interaction. {message}',
                [
                    'message' => $message,
                ],
            ],
        ];
    }

    #[Test]
    public function itShouldDispatchSlackInteractionMessageIfValidInteraction(): void
    {
        $slackInteraction = $this->createMock(SlackInteraction::class);
        $slackInteraction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interaction = Interaction::ADD_REPOSITORY);

        $slackInteraction->expects($this->once())
            ->method('getType')
            ->willReturn($type = InteractionType::BLOCK_ACTIONS);

        $slackInteraction->expects($this->once())
            ->method('getValue')
            ->willReturn($value = 'value');

        $request = $this->createStub(Request::class);

        $factory = $this->createMock(SlackInteractionFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($request)
            ->willReturn($slackInteraction);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($message) use ($slackInteraction) {
                $this->assertInstanceOf(SlackInteractionMessage::class, $message);
                $this->assertSame($slackInteraction, $message->getInteraction());

                return new Envelope($message);
            });

        $this->expectsInfo(
            'Dispatching interaction to asynchronous handler: {interaction} {type} {arguments}',
            [
                'interaction' => $interaction->value,
                'type' => $type->value,
                'arguments' => $value,
            ],
        );

        $controller = new InteractionController($factory, $messageBus);

        $controller->setLogger($this->getLogger());

        $response = $controller($request);

        $this->assertEmpty($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    public function itShouldLogArgumentsIfViewSubmission(): void
    {
        $slackInteraction = $this->createMock(SlackViewSubmission::class);
        $slackInteraction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interaction = Interaction::ADD_REPOSITORY);

        $slackInteraction->expects($this->once())
            ->method('getType')
            ->willReturn($type = InteractionType::VIEW_SUBMISSION);

        $slackInteraction->expects($this->once())
            ->method('getArguments')
            ->willReturn(['key' => $argument = 'argument', 'another_key' => ['argument', 'another_argument']]);

        $request = $this->createStub(Request::class);

        $factory = $this->createMock(SlackInteractionFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($request)
            ->willReturn($slackInteraction);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($message) use ($slackInteraction) {
                $this->assertInstanceOf(SlackInteractionMessage::class, $message);
                $this->assertSame($slackInteraction, $message->getInteraction());

                return new Envelope($message);
            });

        $this->expectsInfo(
            'Dispatching interaction to asynchronous handler: {interaction} {type} {arguments}',
            [
                'interaction' => $interaction->value,
                'type' => $type->value,
                'arguments' => $argument.', [argument, another_argument]',
            ],
        );

        $controller = new InteractionController($factory, $messageBus);

        $controller->setLogger($this->getLogger());

        $response = $controller($request);

        $this->assertEmpty($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
