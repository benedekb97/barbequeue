<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener\Notifier;

use App\Entity\Deployment;
use App\Entity\NotificationSettings;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\NotificationSetting;
use App\Event\Deployment\DeploymentAddedEvent;
use App\EventListener\Notifier\DeploymentAddedNotifier;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentAddedPrivateMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentAddedNotifier::class)]
class DeploymentAddedNotifierTest extends KernelTestCase
{
    #[Test]
    public function itShouldSendPrivateMessageToDeploymentNotifyUsers(): void
    {
        $event = $this->createMock(DeploymentAddedEvent::class);
        $event->expects($this->once())
            ->method('getDeployment')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $event->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $deployment->expects($this->once())
            ->method('getNotifyUsers')
            ->willReturn(new ArrayCollection([$user = $this->createMock(User::class)]));

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $settings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_ADDED)
            ->willReturn(true);

        $factory = $this->createMock(DeploymentAddedPrivateMessageFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($deployment, $workspace, $user)
            ->willReturn($message = $this->createStub(SlackPrivateMessage::class));

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message);

        $notifier = new DeploymentAddedNotifier($handler, $factory);

        $notifier->handle($event);
    }

    #[Test]
    public function itShouldNotSendPrivateMessageToDeploymentNotifyUserIfDisabledInSettings(): void
    {
        $event = $this->createMock(DeploymentAddedEvent::class);
        $event->expects($this->once())
            ->method('getDeployment')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $event->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($this->createStub(Workspace::class));

        $deployment->expects($this->once())
            ->method('getNotifyUsers')
            ->willReturn(new ArrayCollection([$user = $this->createMock(User::class)]));

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $settings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_ADDED)
            ->willReturn(false);

        $factory = $this->createMock(DeploymentAddedPrivateMessageFactory::class);
        $factory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $notifier = new DeploymentAddedNotifier($handler, $factory);

        $notifier->handle($event);
    }
}
