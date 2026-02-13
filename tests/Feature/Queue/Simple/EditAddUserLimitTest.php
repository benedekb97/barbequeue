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

class EditAddUserLimitTest extends FeatureTestCase
{
    #[Test]
    public function itShouldNotRemoveUsersIfUserLimitModified(): void
    {
        $this
            // Create a queue
            ->sendAdminCommand(SubCommand::ADD_QUEUE)
            ->assertQueueNotExists($queueName = 'test')
            ->assertModalOpened(Modal::ADD_QUEUE)

            // Select queue type
            ->sendInputSelectionInteraction(
                Interaction::QUEUE_TYPE,
                Queue::SIMPLE->value,
                $viewId = 'simple-queue-modal-view',
                'administrator',
            )
            ->assertModalUpdated(Modal::ADD_QUEUE_SIMPLE, $viewId)

            // Submit the modal
            ->sendViewSubmission(Interaction::ADD_SIMPLE_QUEUE, [
                $this->getPlainTextArgument(ModalArgument::QUEUE_NAME, BlockElement::PLAIN_TEXT_INPUT, $queueName),
                $this->getSingleSelectArgument(ModalArgument::QUEUE_TYPE, Queue::SIMPLE->value),
            ], userId: 'administrator')
            ->assertInteractionResponseSentContainingMessage('A queue called `test` has been created')
            ->assertQueueExists($queueName)
            ->assertQueuedUserCount($queueName, 0)

            // Join the queue
            ->sendUserCommand(SubCommand::JOIN, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You are 1st in the `test` queue.')
            ->assertQueuedUserCount($queueName, 1)
            ->assertQueuedUserPositionsInQueueCorrect($queueName, [1])

            // Join it again
            ->sendUserCommand(SubCommand::JOIN, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You are 1st and 2nd in the `test` queue.')
            ->assertQueuedUserCount($queueName, 2)
            ->assertQueuedUserPositionsInQueueCorrect($queueName, [1, 2])

            // Edit the queue
            ->sendAdminCommand(SubCommand::EDIT_QUEUE, [$queueName])
            ->assertModalOpened(Modal::EDIT_QUEUE)

            // Save queue as having expiry of 20 minutes
            ->sendViewSubmission(
                Interaction::EDIT_QUEUE,
                [
                    $this->getNumberArgument(ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER, $maxEntries = 1),
                ],
                [
                    'queue' => $this->getQueueId($queueName),
                ],
                userId: 'administrator',
            )
            ->assertInteractionResponseSentContainingMessage('Queue `test` edited successfully')
            ->assertQueueExists($queueName, maxEntries: $maxEntries)
            ->assertQueuedUserPositionsInQueueCorrect($queueName, [1, 2])

            ->sendUserCommand(SubCommand::JOIN, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You are already in the `test` queue.')

            // Leave the first place in the queue
            ->sendUserCommand(SubCommand::LEAVE, [$queueName])
            ->assertModalOpened(Modal::LEAVE_QUEUE)
            ->sendViewSubmission(
                Interaction::LEAVE_QUEUE,
                [
                    $this->getSingleSelectArgument(
                        ModalArgument::QUEUED_USER_ID,
                        (string) $this->getQueuedUserIdForPosition($queueName, 1)
                    ),
                ],
                [
                    'queue' => $queueName,
                ]
            )
            ->assertInteractionResponseSentContainingMessage('You have been removed from the `test` queue.', true)
            ->assertInteractionResponseSentContainingMessage('You are now 1st in the `test` queue.')

            // Attempt to join the queue
            ->sendUserCommand(SubCommand::JOIN, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You are already in the `test` queue.')

            // Leave the first place in the queue
            ->sendUserCommand(SubCommand::LEAVE, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You have left the `test` queue.')

            // Join the queue
            ->sendUserCommand(SubCommand::JOIN, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You are 1st in the `test` queue.')

            // Join the queue with a different user
            ->sendUserCommand(SubCommand::JOIN, [$queueName], $secondUser = 'test2')
            ->assertInteractionResponseSentContainingMessage('You are 2nd in the `test` queue.')

            // Attempt to join the queue
            ->sendUserCommand(SubCommand::JOIN, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You are already in the `test` queue.')

            // Attempt to join the queue with a different user
            ->sendUserCommand(SubCommand::JOIN, [$queueName], $secondUser)
            ->assertInteractionResponseSentContainingMessage('You are already in the `test` queue.');
    }
}
