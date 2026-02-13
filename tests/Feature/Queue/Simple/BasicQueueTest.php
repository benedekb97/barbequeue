<?php

declare(strict_types=1);

namespace App\Tests\Feature\Queue\Simple;

use App\Enum\Queue;
use App\Slack\BlockElement\BlockElement;
use App\Slack\Command\SubCommand;
use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalArgument;
use App\Tests\Feature\FeatureTestCase;
use PHPUnit\Framework\Attributes\Test;

class BasicQueueTest extends FeatureTestCase
{
    #[Test]
    public function itShouldBehaveLikeAQueue(): void
    {
        $this
            // Create queue command
            ->sendAdminCommand(SubCommand::ADD_QUEUE)
            ->assertQueueNotExists($queueName = 'test')
            ->assertModalOpened(Modal::ADD_QUEUE)

            // Select queue type
            ->sendInputSelectionInteraction(
                Interaction::QUEUE_TYPE,
                Queue::SIMPLE->value,
                $viewId = 'simple-queue-modal-view',
                'administrator'
            )
            ->assertModalUpdated(Modal::ADD_QUEUE_SIMPLE, $viewId)

            // Create queue
            ->sendViewSubmission(
                Interaction::ADD_SIMPLE_QUEUE,
                [
                    $this->getPlainTextArgument(ModalArgument::QUEUE_NAME, BlockElement::PLAIN_TEXT_INPUT, $queueName),
                    $this->getSingleSelectArgument(ModalArgument::QUEUE_TYPE, Queue::SIMPLE->value),
                ],
                userId: 'administrator',
            )
            ->assertInteractionResponseSentContainingMessage('A queue called `test` has been created')
            ->assertQueueExists($queueName)
            ->assertQueuedUserCount($queueName, 0)

            // Join queue with user 'test'
            ->sendUserCommand(SubCommand::JOIN, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You are 1st in the `test` queue.')
            ->assertQueuedUserCount($queueName, 1)
            ->assertQueuedUserPositionsInQueueCorrect($queueName, [1])

            // Join queue with user 'test2'
            ->sendUserCommand(SubCommand::JOIN, [$queueName], $secondUser = 'test2')
            ->assertInteractionResponseSentContainingMessage('You are 2nd in the `test` queue.')
            ->assertQueuedUserCount($queueName, 2)
            ->assertQueuedUserPositionsInQueueCorrect($queueName, [1])
            ->assertQueuedUserPositionsInQueueCorrect($queueName, [2], $secondUser)

            // Pop first user off queue
            ->sendAdminCommand(SubCommand::POP_QUEUE, [$queueName])
            ->assertModalOpened(Modal::POP_QUEUE)
            ->sendViewSubmission(
                Interaction::POP_QUEUE_ACTION,
                [
                    $this->getSingleSelectArgument(
                        ModalArgument::QUEUED_USER_ID,
                        (string) $this->getQueuedUserIdForPosition($queueName, 1),
                    ),
                ],
                [
                    'queue' => $queueName,
                ]
            )
            ->assertInteractionResponseSentContainingMessage('Queue `test` has been popped.')
            ->assertQueuedUserPositionsInQueueCorrect($queueName, [1], $secondUser)
            ->assertUserNotInQueue($queueName)

            // Leave the queue with the second user
            ->sendUserCommand(SubCommand::LEAVE, [$queueName], $secondUser)
            ->assertInteractionResponseSentContainingMessage('You have left the `test` queue.')
            ->assertUserNotInQueue($queueName, $secondUser);
    }
}
