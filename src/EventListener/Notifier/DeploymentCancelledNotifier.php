<?php

declare(strict_types=1);

namespace App\EventListener\Notifier;

use App\Enum\NotificationSetting;
use App\Event\Deployment\DeploymentCancelledEvent;
use App\Slack\Response\PrivateMessage\Factory\Deployment\CancelledDeploymentMessageFactory;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentCancelledPrivateMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DeploymentCancelledEvent::class, method: 'handle')]
readonly class DeploymentCancelledNotifier
{
    public function __construct(
        private DeploymentCancelledPrivateMessageFactory $privateMessageFactory,
        private PrivateMessageHandler $privateMessageHandler,
        private CancelledDeploymentMessageFactory $cancelledDeploymentMessageFactory,
    ) {
    }

    public function handle(DeploymentCancelledEvent $event): void
    {
        $deployment = $event->getDeployment();
        $workspace = $event->getWorkspace();
        $repository = $event->getRepository();

        if (
            $event->shouldNotifyOwner()
            && $deployment->getUser()?->getNotificationSettings()?->isSettingEnabled(NotificationSetting::DEPLOYMENT_CANCELLED)
        ) {
            $this->privateMessageHandler->handle(
                $this->cancelledDeploymentMessageFactory->create($deployment, $workspace, $repository),
            );
        }

        foreach ($deployment->getNotifyUsers() as $user) {
            if (!$user->getNotificationSettings()?->isSettingEnabled(NotificationSetting::THIRD_PARTY_DEPLOYMENT_CANCELLED)) {
                continue;
            }

            $this->privateMessageHandler->handle(
                $this->privateMessageFactory->create($deployment, $workspace, $repository, $user)
            );
        }
    }
}
