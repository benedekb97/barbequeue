<?php

declare(strict_types=1);

namespace App\EventListener\Notifier;

use App\Enum\NotificationSetting;
use App\Event\Deployment\DeploymentAddedEvent;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentAddedPrivateMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DeploymentAddedEvent::class, method: 'handle')]
readonly class DeploymentAddedNotifier
{
    public function __construct(
        private PrivateMessageHandler $privateMessageHandler,
        private DeploymentAddedPrivateMessageFactory $deploymentAddedPrivateMessageFactory,
    ) {
    }

    public function handle(DeploymentAddedEvent $event): void
    {
        $deployment = $event->getDeployment();
        $workspace = $event->getWorkspace();

        foreach ($deployment->getNotifyUsers() as $notifyUser) {
            if (!$notifyUser->getNotificationSettings()?->isSettingEnabled(NotificationSetting::THIRD_PARTY_DEPLOYMENT_ADDED)) {
                continue;
            }

            $this->privateMessageHandler->handle(
                $this->deploymentAddedPrivateMessageFactory->create($deployment, $workspace, $notifyUser)
            );
        }
    }
}
