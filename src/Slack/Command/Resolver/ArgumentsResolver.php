<?php

declare(strict_types=1);

namespace App\Slack\Command\Resolver;

use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\Exception\InvalidArgumentException;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\SubCommand;
use App\Slack\Command\Validator\Argument\ArgumentCountValidator;
use App\Slack\Command\Validator\Argument\ArgumentValidatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;

readonly class ArgumentsResolver
{
    public function __construct(
        private ArgumentCountValidator $argumentCountValidator,
        /** @var ArgumentValidatorInterface[] $argumentValidators */
        #[AutowireIterator(ArgumentValidatorInterface::TAG)]
        private iterable $argumentValidators,
    ) {
    }

    /**
     * @return (string|int)[]
     *
     * @throws InvalidArgumentCountException|SubCommandMissingException
     */
    public function resolve(Command $command, ?SubCommand $subCommand, Request $request): array
    {
        $arguments = $this->getArguments($command, $subCommand, $request);

        $this->argumentCountValidator->validate($command, $subCommand, $arguments);

        return $arguments;
    }

    /**
     * @return (string|int)[]
     *
     * @throws SubCommandMissingException
     */
    private function getArguments(Command $command, ?SubCommand $subCommand, Request $request): array
    {
        $commandParts = new ArrayCollection(explode(' ', (string) $request->request->get('text')));

        $argumentValues = array_values($commandParts->slice(null === $subCommand ? 0 : 1));

        $argumentKeys = $command->getArguments($subCommand);

        $arguments = [];

        foreach ($argumentKeys as $key => $argument) {
            $argumentValue = $argumentValues[$key] ?? null;

            if ($key === count($argumentKeys) - 1) {
                $argumentValue = implode(' ', array_slice($argumentValues, $key));
            }

            try {
                $argumentValue = $this->validateArgument($argument, $argumentValue);
            } catch (InvalidArgumentException) {
                continue;
            }

            $arguments[$argument->value] = $argumentValue;
        }

        return array_filter($arguments);
    }

    /** @throws InvalidArgumentException */
    private function validateArgument(CommandArgument $argument, ?string $value): string|int|null
    {
        foreach ($this->argumentValidators as $argumentValidator) {
            if ($argumentValidator->supports($argument)) {
                return $argumentValidator->validate($argument, $value);
            }
        }

        return $value;
    }
}
