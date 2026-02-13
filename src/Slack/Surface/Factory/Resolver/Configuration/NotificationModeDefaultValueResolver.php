<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Configuration;

use App\Slack\Surface\Component\ModalArgument;

class NotificationModeDefaultValueResolver extends AbstractConfigurationDefaultValueResolver implements ConfigurationDefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::CONFIGURATION_NOTIFICATION_MODE;
    }

    public function resolveString(): ?string
    {
        return null;
    }

    public function resolveArray(): ?array
    {
        if (($settings = $this->user?->getNotificationSettings()) === null) {
            return null;
        }

        return [
            'text' => [
                'type' => 'plain_text',
                'text' => ($mode = $settings->getMode())->getName(),
            ],
            'value' => $mode->value,
        ];
    }
}
