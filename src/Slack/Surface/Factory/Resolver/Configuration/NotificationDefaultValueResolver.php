<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Configuration;

use App\Enum\NotificationSetting;
use App\Slack\Surface\Component\ModalArgument;

class NotificationDefaultValueResolver extends AbstractConfigurationDefaultValueResolver implements ConfigurationDefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::CONFIGURATION_DEPLOYMENT_NOTIFICATIONS;
    }

    public function resolveString(): ?string
    {
        return null;
    }

    public function resolveArray(): ?array
    {
        $options = [];

        if (null === $this->user) {
            return $options;
        }

        foreach (NotificationSetting::getUserSettings() as $setting) {
            if (
                $this->user
                    ->getNotificationSettings()
                    ?->isSettingEnabled($setting)
            ) {
                $options[] = [
                    'text' => [
                        'type' => 'plain_text',
                        'text' => $setting->getName(),
                    ],
                    'value' => $setting->value,
                ];
            }
        }

        return $options;
    }
}
