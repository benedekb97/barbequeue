<?php

declare(strict_types=1);

namespace App\EventListener\Notifier;

use App\Enum\NotificationSetting;
use App\Event\Deployment\DeploymentStartedEvent;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentStartedPrivateMessageFactory;
use App\Slack\Response\PrivateMessage\Factory\Deployment\StartDeploymentMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DeploymentStartedEvent::class, method: 'handle')]
readonly class DeploymentStartedNotifier
{
    public function __construct(
        private PrivateMessageHandler $privateMessageHandler,
        private DeploymentStartedPrivateMessageFactory $deploymentStartedPrivateMessageFactory,
        private StartDeploymentMessageFactory $startDeploymentMessageFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(DeploymentStartedEvent $event): void
    {
        $this->logger->debug('Deployment started event received');

        $deployment = $event->getDeployment();
        $workspace = $event->getWorkspace();

        if (
            $event->shouldNotifyOwner()
            && $deployment->getUser()?->getNotificationSettings()?->isSettingEnabled(NotificationSetting::DEPLOYMENT_STARTED)
        ) {
            $this->privateMessageHandler->handle($this->startDeploymentMessageFactory->create($deployment));
        }

        foreach ($deployment->getNotifyUsers() as $notifyUser) {
            if (!$notifyUser->getNotificationSettings()?->isSettingEnabled(NotificationSetting::THIRD_PARTY_DEPLOYMENT_STARTED)) {
                continue;
            }

            $this->privateMessageHandler->handle(
                $this->deploymentStartedPrivateMessageFactory->create($deployment, $workspace, $notifyUser)
            );
        }
    }
}
