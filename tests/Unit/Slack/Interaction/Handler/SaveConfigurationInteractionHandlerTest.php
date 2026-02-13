<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler;

use App\Service\Notification\NotificationSettingsManager;
use App\Slack\Interaction\Handler\SaveConfigurationInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\ConfigurationSavedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Surface\Component\ModalArgument;
use Doctrine\ORM\EntityNotFoundException;
use PHPUnit\Framework\Attributes\Test;

class SaveConfigurationInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldReturnEarlyIfInteractionNotViewSubmission(): void
    {
        $this->expectNotToPerformAssertions();

        $interaction = $this->createStub(SlackInteraction::class);

        /** @var SaveConfigurationInteractionHandler $handler */
        $handler = $this->getHandler();

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldCreateGenericFailureResponseIfEntityNotFoundExceptionThrown(): void
    {
        $manager = $this->createMock(NotificationSettingsManager::class);
        $manager->expects($this->once())
            ->method('updatePreferences')
            ->with($userId = 'userId', $userName = 'userName', $teamId = 'teamId', [], $mode = 'mode')
            ->willThrowException(new EntityNotFoundException());

        $callCount = 0;
        $viewSubmission = $this->createMock(SlackViewSubmission::class);
        $viewSubmission->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function (string $argument) use (&$callCount, $userName, $mode) {
                if (1 === ++$callCount) {
                    $this->assertEquals(ModalArgument::CONFIGURATION_USER_NAME->value, $argument);

                    return $userName;
                }

                $this->assertEquals(ModalArgument::CONFIGURATION_NOTIFICATION_MODE->value, $argument);

                return $mode;
            });

        $viewSubmission->expects($this->exactly(2))
            ->method('getArgumentStringArray')
            ->withAnyParameters()
            ->willReturn([]);

        $viewSubmission->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $viewSubmission->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $configurationSavedResponseFactory = $this->createMock(ConfigurationSavedResponseFactory::class);
        $configurationSavedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $viewSubmission->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new SaveConfigurationInteractionHandler(
            $manager,
            $genericFailureResponseFactory,
            $configurationSavedResponseFactory,
        );

        $handler->handle($viewSubmission);
    }

    #[Test]
    public function itShouldCreateConfigurationSavedResponse(): void
    {
        $manager = $this->createMock(NotificationSettingsManager::class);
        $manager->expects($this->once())
            ->method('updatePreferences')
            ->with($userId = 'userId', $userName = 'userName', $teamId = 'teamId', [], $mode = 'mode');

        $callCount = 0;
        $viewSubmission = $this->createMock(SlackViewSubmission::class);
        $viewSubmission->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function (string $argument) use (&$callCount, $userName, $mode) {
                if (1 === ++$callCount) {
                    $this->assertEquals(ModalArgument::CONFIGURATION_USER_NAME->value, $argument);

                    return $userName;
                }

                $this->assertEquals(ModalArgument::CONFIGURATION_NOTIFICATION_MODE->value, $argument);

                return $mode;
            });

        $viewSubmission->expects($this->exactly(2))
            ->method('getArgumentStringArray')
            ->withAnyParameters()
            ->willReturn([]);

        $viewSubmission->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $viewSubmission->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $configurationSavedResponseFactory = $this->createMock(ConfigurationSavedResponseFactory::class);
        $configurationSavedResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $viewSubmission->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new SaveConfigurationInteractionHandler(
            $manager,
            $genericFailureResponseFactory,
            $configurationSavedResponseFactory,
        );

        $handler->handle($viewSubmission);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::SAVE_CONFIGURATION;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::ADD_DEPLOYMENT_QUEUE;
    }

    protected function getSupportedInteractionType(): InteractionType
    {
        return InteractionType::VIEW_SUBMISSION;
    }

    protected function getUnsupportedInteractionType(): InteractionType
    {
        return InteractionType::BLOCK_ACTIONS;
    }

    protected function getHandler(): SlackInteractionHandlerInterface
    {
        return new SaveConfigurationInteractionHandler(
            $this->createStub(NotificationSettingsManager::class),
            $this->createStub(GenericFailureResponseFactory::class),
            $this->createStub(ConfigurationSavedResponseFactory::class),
        );
    }
}
