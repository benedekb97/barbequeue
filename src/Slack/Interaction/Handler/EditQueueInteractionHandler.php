<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler;

use App\Service\Queue\QueueManager;
use App\Slack\BlockElement\Component\NumberInputElement;
use App\Slack\BlockElement\Component\PlainTextInputElement;
use App\Slack\Interaction\Component\SlackInteraction;
use App\Slack\Interaction\Component\SlackViewSubmission;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionArgumentLocation;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;

readonly class EditQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public const string ARGUMENT_QUEUE = 'queue';
    public const string ARGUMENT_EXPIRY_MINUTES = 'expiry_minutes';
    public const string ARGUMENT_MAXIMUM_ENTRIES_PER_USER = 'maximum_entries_per_user';

    public const array REQUIRED_ARGUMENTS = [
        self::ARGUMENT_QUEUE => PlainTextInputElement::class,
        ...self::REQUIRED_FIELDS,
    ];

    public const array OPTIONAL_ARGUMENTS = [
        self::ARGUMENT_EXPIRY_MINUTES => NumberInputElement::class,
        self::ARGUMENT_MAXIMUM_ENTRIES_PER_USER => NumberInputElement::class,
    ];

    /** @var array|InteractionArgumentLocation[] */
    public const array ARGUMENT_LOCATION_MAP = [
        self::ARGUMENT_QUEUE => InteractionArgumentLocation::PRIVATE_METADATA,
        self::ARGUMENT_MAXIMUM_ENTRIES_PER_USER => InteractionArgumentLocation::STATE,
        self::ARGUMENT_EXPIRY_MINUTES => InteractionArgumentLocation::STATE,
    ];

    public const array REQUIRED_FIELDS = [];

    public const array FIELD_LABEL_MAP = [
        self::ARGUMENT_EXPIRY_MINUTES => 'How long before the first person in the queue gets removed',
        self::ARGUMENT_MAXIMUM_ENTRIES_PER_USER => 'How many times a person can join the queue',
    ];

    public const array FIELD_ENTITY_GETTER_MAP = [
        self::ARGUMENT_EXPIRY_MINUTES => 'getExpiryMinutes',
        self::ARGUMENT_MAXIMUM_ENTRIES_PER_USER => 'getMaximumEntriesPerUser',
    ];

    public const array FIELD_PLACEHOLDER_MAP = [
        self::ARGUMENT_EXPIRY_MINUTES => 'Expiry in minutes',
        self::ARGUMENT_MAXIMUM_ENTRIES_PER_USER => 'Maximum entries per user',
    ];

    public const array FIELD_HINT_MAP = [
        self::ARGUMENT_EXPIRY_MINUTES => 'Leave empty for no limit',
        self::ARGUMENT_MAXIMUM_ENTRIES_PER_USER => 'Leave empty for no limit',
    ];

    public function __construct(
        private QueueManager $queueManager,
        private LoggerInterface $logger,
    ) {}

    public function supports(SlackInteraction $interaction): bool
    {
        return $interaction->getInteraction() === Interaction::EDIT_QUEUE;
    }

    public function handle(SlackInteraction $interaction): void
    {
        if (!$interaction instanceof SlackViewSubmission) {
            return;
        }

        try {
            $this->queueManager->editQueue(
                (int) $interaction->getArgument(self::ARGUMENT_QUEUE),
                $interaction->getArgument(self::ARGUMENT_MAXIMUM_ENTRIES_PER_USER),
                $interaction->getArgument(self::ARGUMENT_EXPIRY_MINUTES),
            );
        } catch (EntityNotFoundException $e) {
            $this->logger->debug($e->getMessage());
        } finally {
            $interaction->setHandled();
        }
    }
}
