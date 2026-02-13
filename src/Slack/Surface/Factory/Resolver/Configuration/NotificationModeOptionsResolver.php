<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Configuration;

use App\Enum\NotificationMode;
use App\Slack\Surface\Component\ModalArgument;

class NotificationModeOptionsResolver implements ConfigurationOptionsResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::CONFIGURATION_NOTIFICATION_MODE;
    }

    public function resolve(): array
    {
        return array_map(function (NotificationMode $mode) {
            return [
                'text' => [
                    'type' => 'plain_text',
                    'text' => $mode->getName(),
                ],
                'value' => $mode->value,
            ];
        }, NotificationMode::cases());
    }
}
