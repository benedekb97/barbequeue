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

class EditAddExpiryTest extends FeatureTestCase
{
    #[Test]
    public function itShouldNotChangeExistingQueuedUsers(): void
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
                    $this->getNumberArgument(ModalArgument::QUEUE_EXPIRY_MINUTES, $expiry = 20),
                ],
                [
                    'queue' => $this->getQueueId($queueName),
                ],
                userId: 'administrator',
            )
            ->assertInteractionResponseSentContainingMessage('Queue `test` edited successfully')
            ->assertQueueExists($queueName, expiryMinutes: $expiry)

            // Check currently queued users for expiry
            ->assertQueuedUserInformationAtPosition($queueName, 1)
            ->assertQueuedUserInformationAtPosition($queueName, 2)

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

            // Queued user left in queue should still not have expiry
            ->assertQueuedUserInformationAtPosition($queueName, 1)

            // Join the queue with a new entry
            ->sendUserCommand(SubCommand::JOIN, [$queueName])
            ->assertInteractionResponseSentContainingMessage('You are 1st and 2nd in the `test` queue.')
            ->assertQueuedUserInformationAtPosition($queueName, 1)

            // The new entry should have expiry minutes set
            ->assertQueuedUserInformationAtPosition($queueName, 2, expiryMinutes: $expiry)

            // Leave the queue with the first user
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

            // User in queue should have expiresAt set
            ->assertQueuedUserInformationAtPosition($queueName, 1, expiryMinutes: $expiry, hasExpiresAt: true)

            // Set the entry as expired
            ->setQueuedUserExpired($queueName, 1)

            // Send automatic expired user removal message
            ->sendAutomaticPopQueuesMessage()
            ->assertPrivateMessageSentContainingMessage('Your time at the front of the `test` queue is up.')

            // User should have been removed from queue, as expired
            ->assertUserNotInQueue($queueName);
    }
}
