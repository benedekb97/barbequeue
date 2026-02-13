<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\Resolver\SubCommandResolver;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\HelpResponseFactory;

readonly class AdministratorHelpCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private SubCommandResolver $subCommandResolver,
        private HelpResponseFactory $helpResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackCommand $command): void
    {
        try {
            $subCommand = $this->subCommandResolver->resolveFromString(
                $command->getCommand(),
                $command->getOptionalArgumentString(CommandArgument::COMMAND),
            );
        } catch (InvalidSubCommandException) {
            $subCommand = null;
        }

        $command->setResponse(
            $this->helpResponseFactory->create($command->getCommand(), $subCommand)
        );
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand() && SubCommand::HELP === $command->getSubCommand();
    }
}
