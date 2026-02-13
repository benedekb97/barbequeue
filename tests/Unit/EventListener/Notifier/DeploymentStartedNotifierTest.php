<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener\Notifier;

use App\Entity\Deployment;
use App\Entity\NotificationSettings;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\NotificationSetting;
use App\Event\Deployment\DeploymentStartedEvent;
use App\EventListener\Notifier\DeploymentStartedNotifier;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentStartedPrivateMessageFactory;
use App\Slack\Response\PrivateMessage\Factory\Deployment\StartDeploymentMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DeploymentStartedNotifier::class)]
class DeploymentStartedNotifierTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSendPrivateMessageToNotifyUsersIfEnabled(): void
    {
        $event = $this->createMock(DeploymentStartedEvent::class);
        $event->expects($this->once())
            ->method('getDeployment')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $event->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

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
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_STARTED)
            ->willReturn(true);

        $factory = $this->createMock(DeploymentStartedPrivateMessageFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($deployment, $workspace, $user)
            ->willReturn($message = $this->createStub(SlackPrivateMessage::class));

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message);

        $this->expectsDebug('Deployment started event received');

        $notifier = new DeploymentStartedNotifier(
            $handler,
            $factory,
            $this->createStub(StartDeploymentMessageFactory::class),
            $this->getLogger(),
        );

        $notifier->handle($event);
    }

    #[Test]
    public function itShouldSendPrivateMessageToOwnerIfEnabledOnEvent(): void
    {
        $this->expectsDebug('Deployment started event received');

        $event = $this->createMock(DeploymentStartedEvent::class);
        $event->expects($this->once())
            ->method('getDeployment')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $event->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $event->expects($this->once())
            ->method('shouldNotifyOwner')
            ->willReturn(true);

        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($deploymentUser = $this->createMock(User::class));

        $deploymentUser->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($userSettings = $this->createMock(NotificationSettings::class));

        $userSettings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::DEPLOYMENT_STARTED)
            ->willReturn(true);

        $deployment->expects($this->once())
            ->method('getNotifyUsers')
            ->willReturn(new ArrayCollection([$user = $this->createMock(User::class)]));

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $settings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_STARTED)
            ->willReturn(false);

        $ownerFactory = $this->createMock(StartDeploymentMessageFactory::class);
        $ownerFactory->expects($this->once())
            ->method('create')
            ->with($deployment)
            ->willReturn($message = $this->createStub(SlackPrivateMessage::class));

        $factory = $this->createMock(DeploymentStartedPrivateMessageFactory::class);
        $factory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message);

        $notifier = new DeploymentStartedNotifier(
            $handler,
            $factory,
            $ownerFactory,
            $this->getLogger(),
        );

        $notifier->handle($event);
    }
}
