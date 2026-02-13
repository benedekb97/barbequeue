<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener\Notifier;

use App\Entity\Deployment;
use App\Entity\NotificationSettings;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\NotificationSetting;
use App\Event\Deployment\DeploymentCompletedEvent;
use App\EventListener\Notifier\DeploymentCompletedNotifier;
use App\Slack\Response\PrivateMessage\Factory\Deployment\CompletedDeploymentMessageFactory;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentCompletedPrivateMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DeploymentCompletedNotifier::class)]
class DeploymentCompletedNotifierTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSendPrivateMessageToNotifyUsers(): void
    {
        $event = $this->createMock(DeploymentCompletedEvent::class);
        $event->expects($this->once())
            ->method('getDeployment')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $event->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $event->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createStub(Repository::class));

        $event->expects($this->once())
            ->method('shouldNotifyOwner')
            ->willReturn(false);

        $deployment->expects($this->once())
            ->method('getNotifyUsers')
            ->willReturn(new ArrayCollection([$user = $this->createMock(User::class)]));

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $settings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_COMPLETED)
            ->willReturn(true);

        $factory = $this->createMock(DeploymentCompletedPrivateMessageFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($deployment, $workspace, $repository, $user)
            ->willReturn($message = $this->createStub(SlackPrivateMessage::class));

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message);

        $this->expectsDebug('Deployment completed event received');

        $notifier = new DeploymentCompletedNotifier(
            $handler,
            $factory,
            $this->getLogger(),
            $this->createStub(CompletedDeploymentMessageFactory::class)
        );

        $notifier->handle($event);
    }

    #[Test]
    public function itShouldNotSendPrivateMessageToNotifyUsersIfDisabled(): void
    {
        $event = $this->createMock(DeploymentCompletedEvent::class);
        $event->expects($this->once())
            ->method('getDeployment')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $event->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $event->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createStub(Repository::class));

        $event->expects($this->once())
            ->method('shouldNotifyOwner')
            ->willReturn(false);

        $deployment->expects($this->once())
            ->method('getNotifyUsers')
            ->willReturn(new ArrayCollection([$user = $this->createMock(User::class)]));

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $settings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_COMPLETED)
            ->willReturn(false);

        $factory = $this->createMock(DeploymentCompletedPrivateMessageFactory::class);
        $factory->expects($this->never())
            ->method('create')
            ->with($deployment, $workspace, $repository, $user)
            ->willReturn($message = $this->createStub(SlackPrivateMessage::class));

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->never())
            ->method('handle')
            ->with($message);

        $this->expectsDebug('Deployment completed event received');

        $notifier = new DeploymentCompletedNotifier(
            $handler,
            $factory,
            $this->getLogger(),
            $this->createStub(CompletedDeploymentMessageFactory::class)
        );

        $notifier->handle($event);
    }

    #[Test]
    public function itShouldSendPrivateMessageToOwnerIfEnabledOnEvent(): void
    {
        $this->expectsDebug('Deployment completed event received');

        $event = $this->createMock(DeploymentCompletedEvent::class);
        $event->expects($this->once())
            ->method('getDeployment')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $event->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $event->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createStub(Repository::class));

        $event->expects($this->once())
            ->method('shouldNotifyOwner')
            ->willReturn(true);

        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($deploymentUser = $this->createMock(User::class));

        $deploymentUser->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $settings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::DEPLOYMENT_COMPLETED)
            ->willReturn(true);

        $deployment->expects($this->once())
            ->method('getNotifyUsers')
            ->willReturn(new ArrayCollection([$user = $this->createMock(User::class)]));

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($userSettings = $this->createMock(NotificationSettings::class));

        $userSettings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_COMPLETED)
            ->willReturn(true);

        $factory = $this->createMock(DeploymentCompletedPrivateMessageFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($deployment, $workspace, $repository, $user)
            ->willReturn($message = $this->createStub(SlackPrivateMessage::class));

        $ownerFactory = $this->createMock(CompletedDeploymentMessageFactory::class);
        $ownerFactory->expects($this->once())
            ->method('create')
            ->with($deployment, $workspace, $repository)
            ->willReturn($ownerMessage = $this->createStub(SlackPrivateMessage::class));

        $callCount = 0;
        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function ($argument) use ($message, $ownerMessage, &$callCount) {
                if (1 === ++$callCount) {
                    $this->assertSame($ownerMessage, $argument);
                } else {
                    $this->assertSame($message, $argument);
                }
            });

        $notifier = new DeploymentCompletedNotifier(
            $handler,
            $factory,
            $this->getLogger(),
            $ownerFactory,
        );

        $notifier->handle($event);
    }

    #[Test]
    public function itShouldNotSendPrivateMessageToOwnerIfEnabledOnEventAndDisabledForUser(): void
    {
        $event = $this->createMock(DeploymentCompletedEvent::class);
        $event->expects($this->once())
            ->method('getDeployment')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $event->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $event->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createStub(Repository::class));

        $event->expects($this->once())
            ->method('shouldNotifyOwner')
            ->willReturn(true);

        $deployment->expects($this->once())
            ->method('getNotifyUsers')
            ->willReturn(new ArrayCollection([$user = $this->createMock(User::class)]));

        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($deploymentUser = $this->createMock(User::class));

        $deploymentUser->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($deploymentSettings = $this->createMock(NotificationSettings::class));

        $deploymentSettings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::DEPLOYMENT_COMPLETED)
            ->willReturn(false);

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $settings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_COMPLETED)
            ->willReturn(true);

        $factory = $this->createMock(DeploymentCompletedPrivateMessageFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($deployment, $workspace, $repository, $user)
            ->willReturn($message = $this->createStub(SlackPrivateMessage::class));

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message);

        $this->expectsDebug('Deployment completed event received');

        $notifier = new DeploymentCompletedNotifier(
            $handler,
            $factory,
            $this->getLogger(),
            $this->createStub(CompletedDeploymentMessageFactory::class)
        );

        $notifier->handle($event);
    }
}
