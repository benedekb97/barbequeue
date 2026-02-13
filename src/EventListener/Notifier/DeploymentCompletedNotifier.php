<?php

declare(strict_types=1);

namespace App\EventListener\Notifier;

use App\Enum\NotificationSetting;
use App\Event\Deployment\DeploymentCompletedEvent;
use App\Slack\Response\PrivateMessage\Factory\Deployment\CompletedDeploymentMessageFactory;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentCompletedPrivateMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DeploymentCompletedEvent::class, method: 'handle')]
readonly class DeploymentCompletedNotifier
{
    public function __construct(
        private PrivateMessageHandler $privateMessageHandler,
        private DeploymentCompletedPrivateMessageFactory $privateMessageFactory,
        private LoggerInterface $logger,
        private CompletedDeploymentMessageFactory $completedDeploymentMessageFactory,
    ) {
    }

    public function handle(DeploymentCompletedEvent $event): void
    {
        $deployment = $event->getDeployment();
        $workspace = $event->getWorkspace();
        $repository = $event->getRepository();

        $this->logger->debug('Deployment completed event received');

        if (
            $event->shouldNotifyOwner()
            && $deployment->getUser()?->getNotificationSettings()?->isSettingEnabled(NotificationSetting::DEPLOYMENT_COMPLETED)
        ) {
            $this->privateMessageHandler->handle(
                $this->completedDeploymentMessageFactory->create($deployment, $workspace, $repository),
            );
        }

        foreach ($deployment->getNotifyUsers() as $notifyUser) {
            if (!$notifyUser->getNotificationSettings()?->isSettingEnabled(NotificationSetting::THIRD_PARTY_DEPLOYMENT_COMPLETED)) {
                continue;
            }

            $this->privateMessageHandler->handle(
                $this->privateMessageFactory->create($deployment, $workspace, $repository, $notifyUser),
            );
        }
    }
}
