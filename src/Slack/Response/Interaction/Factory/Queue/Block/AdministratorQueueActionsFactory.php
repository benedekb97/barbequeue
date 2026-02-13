<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\Queue;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Response\Interaction\Factory\Queue\BlockElement\EditQueueButtonFactory;
use App\Slack\Response\Interaction\Factory\Queue\BlockElement\PopQueueButtonFactory;

readonly class AdministratorQueueActionsFactory
{
    public function __construct(
        private EditQueueButtonFactory $editQueueButtonFactory,
        private PopQueueButtonFactory $popQueueButtonFactory,
    ) {
    }

    public function create(Queue $queue): ActionsBlock
    {
        return new ActionsBlock(
            $this->createButtons($queue),
            'queue_admin_action_'.$queue->getId(),
        );
    }

    /** @return ButtonBlockElement[] */
    private function createButtons(Queue $queue): array
    {
        return [
            $this->editQueueButtonFactory->create($queue),
            $this->popQueueButtonFactory->create($queue),
        ];
    }
}
