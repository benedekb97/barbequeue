<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler;

use App\Service\Notification\NotificationSettingsManager;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\ConfigurationSavedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Surface\Component\ModalArgument;
use Doctrine\ORM\EntityNotFoundException;

readonly class SaveConfigurationInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private NotificationSettingsManager $notificationConfigurationManager,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
        private ConfigurationSavedResponseFactory $configurationSavedResponseFactory,
    ) {
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::SAVE_CONFIGURATION === $interaction->getInteraction()
            && InteractionType::VIEW_SUBMISSION === $interaction->getType();
    }

    public function handle(SlackInteraction $interaction): void
    {
        if (!$interaction instanceof SlackViewSubmission) {
            return;
        }

        try {
            $this->notificationConfigurationManager->updatePreferences(
                $interaction->getUserId(),
                $interaction->getArgumentString(ModalArgument::CONFIGURATION_USER_NAME->value),
                $interaction->getTeamId(),
                array_merge(
                    $interaction->getArgumentStringArray(ModalArgument::CONFIGURATION_DEPLOYMENT_NOTIFICATIONS->value) ?? [],
                    $interaction->getArgumentStringArray(ModalArgument::CONFIGURATION_THIRD_PARTY_DEPLOYMENT_NOTIFICATIONS->value) ?? [],
                ),
                (string) $interaction->getArgumentString(ModalArgument::CONFIGURATION_NOTIFICATION_MODE->value),
            );

            $response = $this->configurationSavedResponseFactory->create();
        } catch (EntityNotFoundException) {
            $response = $this->genericFailureResponseFactory->create();
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $interaction->setResponse($response);
        }
    }
}
