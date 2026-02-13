<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Option;

use App\Entity\Deployment;
use App\Entity\QueuedUser;

class QueuedUserOptionFactory
{
    public function create(QueuedUser $queuedUser, int $place): array
    {
        if ($queuedUser instanceof Deployment) {
            return [
                'text' => [
                    'type' => 'plain_text',
                    'text' => sprintf('#%d - %s', $place, $queuedUser->getDescription()),
                ],
                'value' => (string) $queuedUser->getId(),
            ];
        }

        return [
            'text' => [
                'type' => 'plain_text',
                'text' => sprintf('#%d', $place),
            ],
            'value' => (string) $queuedUser->getId(),
        ];
    }
}
