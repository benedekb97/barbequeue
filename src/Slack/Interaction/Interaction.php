<?php

declare(strict_types=1);

namespace App\Slack\Interaction;

use App\Slack\Common\Component\AuthorisableInterface;
use App\Slack\Surface\Component\Modal;

enum Interaction: string implements AuthorisableInterface
{
    private const string REGEX_PATTERN = '/([A-Za-z\-]+)-[0-9]+-?[A-Z-a-z]*/';

    case JOIN_QUEUE = 'join-queue';
    case JOIN_QUEUE_DEPLOYMENT = 'join-queue-deployment';
    case LEAVE_QUEUE = 'leave-queue';
    case SAVE_CONFIGURATION = 'save-configuration';

    case ADD_SIMPLE_QUEUE = 'add-simple-queue';
    case ADD_DEPLOYMENT_QUEUE = 'add-deployment-queue';
    case QUEUE_TYPE = 'queue_type'; // because of how fields work in slack this needs to be separated by an underscore

    case EDIT_QUEUE = 'edit-queue';
    case EDIT_QUEUE_DEPLOYMENT = 'edit-queue-deployment';
    case EDIT_QUEUE_ACTION = 'edit-queue-action';

    case POP_QUEUE_ACTION = 'pop-queue-action';

    case ADD_REPOSITORY = 'add-repository';
    case EDIT_REPOSITORY = 'edit-repository';
    case REMOVE_REPOSITORY = 'remove-repository-action';

    /** @throws \ValueError */
    public static function fromActionId(string $actionId): self
    {
        $interaction = self::tryFrom($actionId);

        if ($interaction instanceof self) {
            return $interaction;
        }

        $matches = [];

        preg_match(self::REGEX_PATTERN, $actionId, $matches);

        return self::from($matches[1] ?? '');
    }

    public function getModal(): ?Modal
    {
        return match ($this) {
            self::EDIT_QUEUE => Modal::EDIT_QUEUE,
            self::ADD_REPOSITORY => Modal::ADD_REPOSITORY,
            self::EDIT_REPOSITORY => Modal::EDIT_REPOSITORY,
            self::ADD_SIMPLE_QUEUE => Modal::ADD_QUEUE_SIMPLE,
            self::ADD_DEPLOYMENT_QUEUE => Modal::ADD_QUEUE_DEPLOYMENT,
            self::JOIN_QUEUE_DEPLOYMENT => Modal::JOIN_QUEUE_DEPLOYMENT,
            self::EDIT_QUEUE_DEPLOYMENT => Modal::EDIT_QUEUE_DEPLOYMENT,
            self::LEAVE_QUEUE => Modal::LEAVE_QUEUE,
            self::POP_QUEUE_ACTION => Modal::POP_QUEUE,
            self::SAVE_CONFIGURATION => Modal::CONFIGURATION,
            default => null,
        };
    }

    /** @return string[] */
    public function getRequiredArguments(): array
    {
        return array_merge(
            $this->getModal()?->getRequiredArgumentNames() ?? [],
            $this->getModal()?->getRequiredFieldNames() ?? []
        );
    }

    /** @return string[] */
    public function getOptionalArguments(): array
    {
        return array_merge(
            $this->getModal()?->getOptionalArgumentNames() ?? [],
            $this->getModal()?->getOptionalFieldNames() ?? []
        );
    }

    /** @return string[] */
    public function getArguments(): array
    {
        return array_merge(
            $this->getRequiredArguments(),
            $this->getOptionalArguments()
        );
    }

    public function getArgumentLocation(string $argument): InteractionArgumentLocation
    {
        return $this->getModal()?->getArgumentLocation($argument)
            ?? InteractionArgumentLocation::STATE;
    }

    public function isAuthorisationRequired(): bool
    {
        return match ($this) {
            self::EDIT_QUEUE,
            self::EDIT_QUEUE_DEPLOYMENT,
            self::EDIT_QUEUE_ACTION,
            self::ADD_REPOSITORY,
            self::EDIT_REPOSITORY,
            self::REMOVE_REPOSITORY,
            self::ADD_DEPLOYMENT_QUEUE,
            self::ADD_SIMPLE_QUEUE,
            self::QUEUE_TYPE, => true,

            default => false,
        };
    }

    public function getAction(): string
    {
        return match ($this) {
            self::EDIT_QUEUE_ACTION, self::EDIT_QUEUE => 'edit queues',
            self::LEAVE_QUEUE => 'leave queues',
            self::JOIN_QUEUE => 'join queues',
            self::ADD_REPOSITORY => 'add repositories',
            self::EDIT_REPOSITORY => 'edit repositories',
            self::REMOVE_REPOSITORY => 'remove repositories',
            self::POP_QUEUE_ACTION => 'pop queues',
            self::ADD_SIMPLE_QUEUE => 'add queues',
            self::ADD_DEPLOYMENT_QUEUE => 'add deployment queues',
            self::QUEUE_TYPE => 'select queue types',
            self::JOIN_QUEUE_DEPLOYMENT => 'join deployment queues',
            self::EDIT_QUEUE_DEPLOYMENT => 'edit deployment queues',
            self::SAVE_CONFIGURATION => 'save configurations',
        };
    }
}
