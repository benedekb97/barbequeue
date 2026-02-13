<?php

declare(strict_types=1);

namespace App\Slack\Command;

enum SubCommand: string
{
    // bbq sub-commands
    case JOIN = 'join';
    case LEAVE = 'leave';
    case LIST = 'list';
    case HELP = 'help';
    case CONFIGURE = 'configure';

    // bbq-admin sub-commands
    case ADD_USER = 'add-user';
    case REMOVE_USER = 'remove-user';
    case ADD_QUEUE = 'add-queue';

    case EDIT_QUEUE = 'edit-queue';
    case POP_QUEUE = 'pop-queue';

    case ADD_REPOSITORY = 'add-repository';
    case REMOVE_REPOSITORY = 'remove-repository';
    case EDIT_REPOSITORY = 'edit-repository';
    case LIST_REPOSITORIES = 'list-repositories';

    public static function tryFromAlias(string $input): ?self
    {
        $subCommand = self::tryFrom($input);

        if (null !== $subCommand) {
            return $subCommand;
        }

        return match ($input) {
            'j','jo','joi' => self::JOIN,
            'l','lv','le','lea','leav' => self::LEAVE,
            'ls','lst','lis' => self::LIST,
            'h','he','hl','hlp' => self::HELP,
            'c', 'conf', 'config' => self::CONFIGURE,
            'au','aus','addu','adus' => self::ADD_USER,
            'ru','reu','reus','remu' => self::REMOVE_USER,
            'aq', 'adq', 'adqu' => self::ADD_QUEUE,
            'eq','edq','ediq' => self::EDIT_QUEUE,
            'pq','popq','poq', => self::POP_QUEUE,
            'ar','adr','adre' => self::ADD_REPOSITORY,
            'rr','rer','rere' => self::REMOVE_REPOSITORY,
            'er','edr','edre' => self::EDIT_REPOSITORY,
            'lr','lsrs','lsr' => self::LIST_REPOSITORIES,
            default => null,
        };
    }
}
