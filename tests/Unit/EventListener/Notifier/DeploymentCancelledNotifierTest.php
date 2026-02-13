<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener\Notifier;

use App\Entity\Deployment;
use App\Entity\NotificationSettings;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\NotificationSetting;
use App\Event\Deployment\DeploymentCancelledEvent;
use App\EventListener\Notifier\DeploymentCancelledNotifier;
use App\Slack\Response\PrivateMessage\Factory\Deployment\CancelledDeploymentMessageFactory;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentCancelledPrivateMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentCancelledNotifier::class)]
class DeploymentCancelledNotifierTest extends KernelTestCase
{
    #[Test]
    public function itShouldSendPrivateMessageToNotifyUsers(): void
    {
        $event = $this->createMock(DeploymentCancelledEvent::class);
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
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_CANCELLED)
            ->willReturn(true);

        $factory = $this->createMock(DeploymentCancelledPrivateMessageFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($deployment, $workspace, $repository, $user)
            ->willReturn($message = $this->createStub(SlackPrivateMessage::class));

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message);

        $notifier = new DeploymentCancelledNotifier(
            $factory,
            $handler,
            $this->createStub(CancelledDeploymentMessageFactory::class)
        );

        $notifier->handle($event);
    }

    #[Test]
    public function itShouldSendPrivateMessageToOwnerIfEnabledOnEvent(): void
    {
        $event = $this->createMock(DeploymentCancelledEvent::class);
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
            ->willReturn($userSettings = $this->createMock(NotificationSettings::class));

        $userSettings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::DEPLOYMENT_CANCELLED)
            ->willReturn(true);

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $settings->expects($this->once())
            ->method('isSettingEnabled')
            ->with(NotificationSetting::THIRD_PARTY_DEPLOYMENT_CANCELLED)
            ->willReturn(false);

        $factory = $this->createMock(DeploymentCancelledPrivateMessageFactory::class);
        $factory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $ownerFactory = $this->createMock(CancelledDeploymentMessageFactory::class);
        $ownerFactory->expects($this->once())
            ->method('create')
            ->with($deployment, $workspace, $repository)
            ->willReturn($ownerMessage = $this->createStub(SlackPrivateMessage::class));

        $handler = $this->createMock(PrivateMessageHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($ownerMessage);

        $notifier = new DeploymentCancelledNotifier($factory, $handler, $ownerFactory);

        $notifier->handle($event);
    }
}
