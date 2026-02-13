<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Configuration;

use App\Enum\NotificationSetting;
use App\Slack\Surface\Component\ModalArgument;

class NotificationOptionsResolver implements ConfigurationOptionsResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::CONFIGURATION_DEPLOYMENT_NOTIFICATIONS;
    }

    public function resolve(): array
    {
        return array_map(function (NotificationSetting $setting) {
            return [
                'text' => [
                    'type' => 'plain_text',
                    'text' => $setting->getName(),
                ],
                'value' => $setting->value,
            ];
        }, NotificationSetting::getUserSettings());
    }
}
