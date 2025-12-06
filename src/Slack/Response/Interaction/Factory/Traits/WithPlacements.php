<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Traits;

use App\Entity\Queue;

trait WithPlacements
{
    /** @return array|string[] */
    private function getPlacements(Queue $queue, string $userId): array
    {
        $allUsers = $queue->getQueuedUsers()->toArray();
        $queuedPlaces = $queue->getQueuedUsersByUserId($userId);

        $places = [];

        foreach ($allUsers as $key => $user) {
            if ($queuedPlaces->contains($user)) {
                $places[] = $key + 1;
            }
        }

        return array_map(function (int $number) {
            return $number.$this->getOrdinalSuffix($number);
        }, $places);
    }

    private function getOrdinalSuffix(int $number): string
    {
        return match ($number % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }

    private function getPlacementString(Queue $queue, string $userId): string
    {
        $placements = $this->getPlacements($queue, $userId);

        if (count($placements) === 1) {
            return reset($placements);
        }

        return implode(', ', array_slice($placements, 0, -1)).' and '.end($placements);
    }
}
