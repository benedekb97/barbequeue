<?php

declare(strict_types=1);

namespace App\Slack\Surface\Component;

use App\Slack\BlockElement\Component\CheckboxesElement;
use App\Slack\BlockElement\Component\MultiStaticSelectElement;
use App\Slack\BlockElement\Component\MultiUsersSelectElement;
use App\Slack\BlockElement\Component\NumberInputElement;
use App\Slack\BlockElement\Component\PlainTextInputElement;
use App\Slack\BlockElement\Component\SlackBlockElement;
use App\Slack\BlockElement\Component\StaticSelectElement;
use App\Slack\BlockElement\Component\UrlInputElement;
use App\Slack\Interaction\InteractionArgumentLocation;

enum ModalArgument: string
{
    case QUEUE = 'queue';
    case QUEUE_EXPIRY_MINUTES = 'expiry_minutes';
    case QUEUE_MAXIMUM_ENTRIES_PER_USER = 'maximum_entries_per_user';

    case REPOSITORY_ID = 'repository_id';
    case REPOSITORY_NAME = 'repository_name';
    case REPOSITORY_URL = 'repository_url';
    case REPOSITORY_BLOCKS = 'repository_blockers';

    case QUEUE_TYPE = 'queue_type';
    case QUEUE_REPOSITORIES = 'queue_repositories';
    case QUEUE_BEHAVIOUR = 'queue_behaviour';
    case QUEUE_NAME = 'queue_name';

    case JOIN_QUEUE_NAME = 'join_queue_name';
    case DEPLOYMENT_DESCRIPTION = 'deployment_description';
    case DEPLOYMENT_LINK = 'deployment_link';
    case DEPLOYMENT_REPOSITORY = 'deployment_repository';
    case DEPLOYMENT_NOTIFY_USERS = 'deployment_notify_users';
    case JOIN_QUEUE_REQUIRED_MINUTES = 'join_queue_required_minutes';

    case QUEUED_USER_ID = 'queued_user_id';

    case CONFIGURATION_NOTIFICATION_MODE = 'configuration_notification_mode';
    case CONFIGURATION_DEPLOYMENT_NOTIFICATIONS = 'configuration_deployment_notifications';
    case CONFIGURATION_THIRD_PARTY_DEPLOYMENT_NOTIFICATIONS = 'configuration_third_party_deployment_notifications';
    case CONFIGURATION_USER_NAME = 'configuration_user_name';

    public function isRequired(): bool
    {
        return match ($this) {
            self::QUEUE,
            self::REPOSITORY_ID,
            self::REPOSITORY_NAME,
            self::QUEUE_TYPE,
            self::QUEUE_NAME,
            self::QUEUE_REPOSITORIES,
            self::JOIN_QUEUE_NAME,
            self::DEPLOYMENT_DESCRIPTION,
            self::DEPLOYMENT_LINK,
            self::DEPLOYMENT_REPOSITORY,
            self::QUEUE_BEHAVIOUR,
            self::CONFIGURATION_NOTIFICATION_MODE => true,

            default => false,
        };
    }

    public function getLocation(): InteractionArgumentLocation
    {
        return match ($this) {
            self::QUEUE,
            self::REPOSITORY_ID,
            self::JOIN_QUEUE_NAME, => InteractionArgumentLocation::PRIVATE_METADATA,

            default => InteractionArgumentLocation::STATE,
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::QUEUE_EXPIRY_MINUTES => 'How long before the first person in the queue gets removed',
            self::QUEUE_MAXIMUM_ENTRIES_PER_USER => 'How many times a person can join the queue',

            self::REPOSITORY_NAME => 'What is the repository called?',
            self::REPOSITORY_URL => 'Where can the repository be found?',

            self::REPOSITORY_BLOCKS => 'Which repositories will be blocked by a deployment on this repository?',

            self::QUEUE_NAME => 'What should the queue be called?',
            self::QUEUE_REPOSITORIES => 'Which repositories can users queueing in this queue deploy to?',
            self::QUEUE_TYPE => 'What type of queue would you like to create?',
            self::QUEUE_BEHAVIOUR => 'How should the queue handle blocking deployments?',

            self::DEPLOYMENT_DESCRIPTION => 'What are you deploying?',
            self::DEPLOYMENT_LINK => 'A link to where it can be found',
            self::DEPLOYMENT_REPOSITORY => 'Which repository is it in?',
            self::DEPLOYMENT_NOTIFY_USERS => 'Who to notify when it\'s time to deploy',

            self::JOIN_QUEUE_REQUIRED_MINUTES => 'How long will you need at the front of the queue?',

            self::QUEUED_USER_ID => 'Select which queued user you would like to remove',

            self::CONFIGURATION_DEPLOYMENT_NOTIFICATIONS => 'When would you like to be notified about *your own* deployments?',
            self::CONFIGURATION_THIRD_PARTY_DEPLOYMENT_NOTIFICATIONS => 'When would you like to be notified about *others* deployments?',
            self::CONFIGURATION_NOTIFICATION_MODE => 'How would you like to receive notifications?',
            self::CONFIGURATION_USER_NAME => 'How would you like us to call you?',

            default => null,
        };
    }

    public function getPlaceholder(): ?string
    {
        return match ($this) {
            self::QUEUE_EXPIRY_MINUTES => 'Expiry in minutes',
            self::QUEUE_MAXIMUM_ENTRIES_PER_USER => 'Maximum entries per user',

            self::REPOSITORY_NAME => 'Repository name',
            self::REPOSITORY_URL => 'Repository URL',

            self::REPOSITORY_BLOCKS => 'Select blockers',

            self::QUEUE_NAME => 'Queue name',
            self::QUEUE_REPOSITORIES => 'Select repositories',
            self::QUEUE_BEHAVIOUR => 'Select behaviour',
            self::QUEUE_TYPE => 'Select a type',

            self::DEPLOYMENT_DESCRIPTION => 'Issue title',
            self::DEPLOYMENT_LINK => 'Issue link',
            self::DEPLOYMENT_REPOSITORY => 'Select a repository',
            self::DEPLOYMENT_NOTIFY_USERS => 'Select some users',

            self::JOIN_QUEUE_REQUIRED_MINUTES => 'Number of minutes',

            self::QUEUED_USER_ID => 'Select an entry',

            self::CONFIGURATION_NOTIFICATION_MODE => 'Select mode',
            self::CONFIGURATION_USER_NAME => 'Your name',

            default => null,
        };
    }

    public function getHint(): ?string
    {
        return match ($this) {
            self::QUEUE_EXPIRY_MINUTES => 'Leave empty for no limit. Will be rounded up to closest 5.',
            self::QUEUE_MAXIMUM_ENTRIES_PER_USER => 'Leave empty for no limit.',

            self::REPOSITORY_URL => 'This will be displayed on development or environment queue entries',

            self::QUEUE_NAME => 'Keep it simple, use kebab-case if you must.',
            self::QUEUE_REPOSITORIES => 'When joining this queue, a user will be able to choose from deploying to one of these repositories.',

            self::DEPLOYMENT_LINK => 'A link to a pull request or a ticket. This will be highlighted when your deployment starts.',
            self::DEPLOYMENT_NOTIFY_USERS => 'The selected users will also receive a notification when you reach the front of the queue.',

            self::JOIN_QUEUE_REQUIRED_MINUTES => 'You will be removed from the front of the queue after this many minutes. Leave empty for no limit.',

            self::CONFIGURATION_DEPLOYMENT_NOTIFICATIONS => 'You cannot disable the initial notification of your deployment being created.',

            default => null,
        };
    }

    public function getExplanation(): ?string
    {
        return match ($this) {
            self::QUEUE_BEHAVIOUR => '*Enforce queue*: FIFO.
*Allow jump*: If the first deployment in line is blocked by a deployment in another queue, the next deployment can jump the queue.
*Allow simultaneous*: Allows all deployments where the repository is free to happen simultaneously.',
            default => null,
        };
    }

    /** @return class-string<SlackBlockElement>|null */
    public function getFieldType(): ?string
    {
        return match ($this) {
            self::QUEUE_EXPIRY_MINUTES,
            self::QUEUE_MAXIMUM_ENTRIES_PER_USER,
            self::JOIN_QUEUE_REQUIRED_MINUTES => NumberInputElement::class,

            self::REPOSITORY_NAME,
            self::REPOSITORY_URL,
            self::QUEUE_NAME,
            self::DEPLOYMENT_DESCRIPTION,
            self::CONFIGURATION_USER_NAME => PlainTextInputElement::class,

            self::DEPLOYMENT_LINK => UrlInputElement::class,

            self::REPOSITORY_BLOCKS,
            self::QUEUE_REPOSITORIES => MultiStaticSelectElement::class,

            self::QUEUE_TYPE,
            self::DEPLOYMENT_REPOSITORY,
            self::QUEUE_BEHAVIOUR,
            self::QUEUED_USER_ID,
            self::CONFIGURATION_NOTIFICATION_MODE, => StaticSelectElement::class,

            self::CONFIGURATION_THIRD_PARTY_DEPLOYMENT_NOTIFICATIONS,
            self::CONFIGURATION_DEPLOYMENT_NOTIFICATIONS => CheckboxesElement::class,

            self::DEPLOYMENT_NOTIFY_USERS => MultiUsersSelectElement::class,

            default => null,
        };
    }

    public function hasDispatchedAction(): bool
    {
        return match ($this) {
            self::QUEUE_TYPE => true,
            default => false,
        };
    }
}
