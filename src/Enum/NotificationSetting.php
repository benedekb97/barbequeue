<?php

declare(strict_types=1);

namespace App\Enum;

enum NotificationSetting: string
{
    case THIRD_PARTY_DEPLOYMENT_ADDED = 'third-party-deployment-added';
    case THIRD_PARTY_DEPLOYMENT_STARTED = 'third-party-deployment-started';
    case THIRD_PARTY_DEPLOYMENT_COMPLETED = 'third-party-deployment-completed';
    case THIRD_PARTY_DEPLOYMENT_CANCELLED = 'third-party-deployment-cancelled';
    case DEPLOYMENT_STARTED = 'deployment-started';
    case DEPLOYMENT_COMPLETED = 'deployment-completed';
    case DEPLOYMENT_CANCELLED = 'deployment-cancelled';

    /** @return self[] */
    public static function getThirdPartySettings(): array
    {
        return [
            self::THIRD_PARTY_DEPLOYMENT_STARTED,
            self::THIRD_PARTY_DEPLOYMENT_ADDED,
            self::THIRD_PARTY_DEPLOYMENT_COMPLETED,
            self::THIRD_PARTY_DEPLOYMENT_CANCELLED,
        ];
    }

    /** @return self[] */
    public static function getUserSettings(): array
    {
        return [
            self::DEPLOYMENT_STARTED,
            self::DEPLOYMENT_COMPLETED,
            self::DEPLOYMENT_CANCELLED,
        ];
    }

    public function getName(): string
    {
        return match ($this) {
            self::DEPLOYMENT_STARTED, self::THIRD_PARTY_DEPLOYMENT_STARTED => 'When a deployment is started',
            self::DEPLOYMENT_COMPLETED, self::THIRD_PARTY_DEPLOYMENT_COMPLETED => 'When a deployment is completed',
            self::DEPLOYMENT_CANCELLED, self::THIRD_PARTY_DEPLOYMENT_CANCELLED => 'When a deployment is cancelled',
            self::THIRD_PARTY_DEPLOYMENT_ADDED => 'When a deployment is added to a queue',
        };
    }
}
