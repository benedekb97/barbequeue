<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Entity\Queue;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use Psr\Log\LoggerInterface;

class QueueLeftResponseFactory
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function create(Queue $queue, string $userId): SlackInteractionResponse
    {
        if (!$queue->canLeave($userId)) {
            return new SlackInteractionResponse([
                new SectionBlock('You have left the '.$queue->getName().' queue.')
            ]);
        }

        $allUsers = $queue->getQueuedUsers()->toArray();
        $queuedPlaces = $queue->getQueuedUsersByUserId($userId);

        $this->logger->debug(implode(' ', array_keys($allUsers)));

        $places = [];

        foreach ($allUsers as $key => $user) {
            if ($queuedPlaces->contains($user)) {
                $places[] = $key + 1;
            }
        }

        $this->logger->debug(implode(' ', $places));

        $places = array_map(function (int $number){
            return $number.$this->getOrdinalSuffix($number);
        }, $places);

        return new SlackInteractionResponse([
            new HeaderBlock('You have been removed from your last place in the '.$queue->getName().' queue.'),
            new DividerBlock(),
            new SectionBlock(
                'You are now '.$this->getPlacementString($places).' in the queue.'
            )
        ]);
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

    private function getPlacementString(array $placements): ?string
    {
        if (count($placements) === 1) {
            return reset($placements);
        }

        return implode(', ', array_slice($placements, -1)).' and '.end($placements);
    }
}
