<?php

declare(strict_types=1);

namespace App\Slack\Surface\Component;

use App\Slack\Interaction\InteractionArgumentLocation;

enum Modal: string
{
    case JOIN_QUEUE_DEPLOYMENT = 'join-queue-deployment';
    case LEAVE_QUEUE = 'leave-queue';
    case CONFIGURATION = 'configuration';

    case ADD_REPOSITORY = 'add-repository';
    case EDIT_REPOSITORY = 'edit-repository';

    case ADD_QUEUE = 'add-queue';
    case EDIT_QUEUE = 'edit-queue';
    case EDIT_QUEUE_DEPLOYMENT = 'edit-queue-deployment';
    case ADD_QUEUE_DEPLOYMENT = 'add-queue-deployment';
    case ADD_QUEUE_SIMPLE = 'add-queue-simple';
    case POP_QUEUE = 'pop-queue';

    /** @return ModalArgument[] */
    public function getRequiredFields(): array
    {
        return match ($this) {
            self::ADD_REPOSITORY, self::EDIT_REPOSITORY => [ModalArgument::REPOSITORY_NAME],
            self::ADD_QUEUE => [ModalArgument::QUEUE_TYPE],
            self::ADD_QUEUE_SIMPLE => [
                ModalArgument::QUEUE_TYPE,
                ModalArgument::QUEUE_NAME,
            ],
            self::ADD_QUEUE_DEPLOYMENT => [
                ModalArgument::QUEUE_TYPE,
                ModalArgument::QUEUE_NAME,
                ModalArgument::QUEUE_REPOSITORIES,
                ModalArgument::QUEUE_BEHAVIOUR,
            ],
            self::JOIN_QUEUE_DEPLOYMENT => [
                ModalArgument::DEPLOYMENT_DESCRIPTION,
                ModalArgument::DEPLOYMENT_LINK,
                ModalArgument::DEPLOYMENT_REPOSITORY,
            ],
            self::EDIT_QUEUE_DEPLOYMENT => [
                ModalArgument::QUEUE_REPOSITORIES,
                ModalArgument::QUEUE_BEHAVIOUR,
            ],
            self::LEAVE_QUEUE, self::POP_QUEUE => [
                ModalArgument::QUEUED_USER_ID,
            ],
            self::CONFIGURATION => [ModalArgument::CONFIGURATION_NOTIFICATION_MODE],
            default => [],
        };
    }

    /** @return string[] */
    public function getRequiredFieldNames(): array
    {
        return array_map(fn (ModalArgument $argument) => $argument->value, $this->getRequiredFields());
    }

    /** @return ModalArgument[] */
    public function getRequiredArguments(): array
    {
        return match ($this) {
            self::EDIT_QUEUE, self::EDIT_QUEUE_DEPLOYMENT, self::LEAVE_QUEUE, self::POP_QUEUE => [ModalArgument::QUEUE],
            self::EDIT_REPOSITORY => [ModalArgument::REPOSITORY_ID],
            self::JOIN_QUEUE_DEPLOYMENT => [ModalArgument::JOIN_QUEUE_NAME],
            default => [],
        };
    }

    /** @return string[] */
    public function getRequiredArgumentNames(): array
    {
        return array_map(function (ModalArgument $argument) {
            return $argument->value;
        }, $this->getRequiredArguments());
    }

    /** @return ModalArgument[] */
    public function getOptionalFields(): array
    {
        return match ($this) {
            self::EDIT_QUEUE, self::ADD_QUEUE_SIMPLE, self::ADD_QUEUE_DEPLOYMENT, self::EDIT_QUEUE_DEPLOYMENT => [
                ModalArgument::QUEUE_EXPIRY_MINUTES,
                ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER,
            ],
            self::ADD_REPOSITORY, self::EDIT_REPOSITORY => [
                ModalArgument::REPOSITORY_URL,
                ModalArgument::REPOSITORY_BLOCKS,
            ],
            self::JOIN_QUEUE_DEPLOYMENT => [
                ModalArgument::DEPLOYMENT_NOTIFY_USERS,
                ModalArgument::JOIN_QUEUE_REQUIRED_MINUTES,
            ],
            self::CONFIGURATION => [
                ModalArgument::CONFIGURATION_USER_NAME,
                ModalArgument::CONFIGURATION_DEPLOYMENT_NOTIFICATIONS,
                ModalArgument::CONFIGURATION_THIRD_PARTY_DEPLOYMENT_NOTIFICATIONS,
            ],
            default => [],
        };
    }

    /** @return string[] */
    public function getOptionalFieldNames(): array
    {
        return array_map(fn (ModalArgument $argument) => $argument->value, $this->getOptionalFields());
    }

    /** @return ModalArgument[] */
    public function getOptionalArguments(): array
    {
        return [];
    }

    /** @return string[] */
    public function getOptionalArgumentNames(): array
    {
        return array_map(function (ModalArgument $argument) {
            return $argument->value;
        }, $this->getOptionalArguments());
    }

    public function getArgumentLocation(string $argumentName): InteractionArgumentLocation
    {
        if (!in_array($argumentName, $this->getArgumentNames())) {
            return InteractionArgumentLocation::STATE;
        }

        $argument = ModalArgument::from($argumentName);

        return $argument->getLocation();
    }

    /** @return ModalArgument[] */
    public function getFields(): array
    {
        return array_merge($this->getRequiredFields(), $this->getOptionalFields());
    }

    /** @return ModalArgument[] */
    public function getArguments(): array
    {
        return array_merge($this->getRequiredArguments(), $this->getOptionalArguments());
    }

    /** @return string[] */
    public function getArgumentNames(): array
    {
        return array_map(fn (ModalArgument $argument) => $argument->value, $this->getArguments());
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::ADD_REPOSITORY => 'Add repository',
            self::EDIT_REPOSITORY => 'Edit repository',
            self::EDIT_QUEUE => 'Edit queue',
            self::ADD_QUEUE => 'Add queue',
            self::ADD_QUEUE_DEPLOYMENT => 'Add deployment queue',
            self::ADD_QUEUE_SIMPLE => 'Add simple queue',
            self::JOIN_QUEUE_DEPLOYMENT => 'Join deployment queue',
            self::EDIT_QUEUE_DEPLOYMENT => 'Edit deployment queue',
            self::LEAVE_QUEUE => 'Leave queue',
            self::POP_QUEUE => 'Remove queued user',
            self::CONFIGURATION => 'My preferences',
        };
    }
}
