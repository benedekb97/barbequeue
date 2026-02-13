<?php

declare(strict_types=1);

namespace App\Controller\Slack;

use App\Message\Slack\SlackCommandMessage;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\Exception\InvalidCommandException;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\Factory\SlackCommandFactory;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Command\SlackCommandResponse;
use App\Slack\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

readonly class CommandController
{
    public function __construct(
        private SlackCommandFactory $commandFactory,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/slack/command', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): JsonResponse|SymfonyResponse
    {
        try {
            $command = $this->commandFactory->createFromRequest($request);
        } catch (SubCommandMissingException $exception) {
            $this->logger->debug($exception->getMessage());

            return $this->getSubCommandMissingResponse($exception);
        } catch (InvalidArgumentCountException $exception) {
            $this->logger->debug($exception->getMessage());

            return $this->getInvalidArgumentCountResponse($exception);
        } catch (InvalidSubCommandException $exception) {
            $this->logger->debug($exception->getMessage());

            return $this->getInvalidSubCommandResponse($exception);
        } catch (InvalidCommandException $exception) {
            $this->logger->debug($exception->getMessage());

            return $this->getUnrecognisedCommandResponse($exception);
        }

        $this->logger->info('Dispatching command to asynchronous consumer: {command} {subCommand} {arguments}', [
            'command' => $command->getCommand()->value,
            'subCommand' => $command->getSubCommand()?->value,
            'arguments' => implode(' ', $command->getArguments()),
        ]);

        $this->messageBus->dispatch(new SlackCommandMessage($command));

        return new SymfonyResponse();
    }

    private function getSubCommandMissingResponse(SubCommandMissingException $exception): JsonResponse
    {
        $command = $exception->getCommand();

        $response = new SlackCommandResponse(
            Response::EPHEMERAL,
            sprintf(
                'Unrecognised command: /%s. Usage: /%s %s',
                $command->value,
                $command->value,
                implode('|', array_map(
                    fn (?SubCommand $subCommand) => $subCommand?->value,
                    $command->getSubCommands()
                ))
            ),
            [
                new HeaderBlock('Unrecognised command /'.$command->value),
                new DividerBlock(),
                ...array_map(function (?SubCommand $subCommand) use ($command) {
                    return new SectionBlock('Usage: '.$command->getUsage($subCommand));
                }, $command->getSubCommands()),
            ]
        );

        return new JsonResponse($response->toArray());
    }

    private function getInvalidArgumentCountResponse(InvalidArgumentCountException $exception): JsonResponse
    {
        $command = $exception->getCommand();
        $subCommand = $exception->getSubCommand();

        $response = new SlackCommandResponse(
            Response::EPHEMERAL,
            'Usage: '.$command->getUsage($subCommand),
            [
                new HeaderBlock('Please provide all required arguments /'.$command->value),
                new DividerBlock(),
                new SectionBlock('Usage: *'.$command->getUsage($subCommand).'*'),
            ]
        );

        return new JsonResponse($response->toArray());
    }

    private function getInvalidSubCommandResponse(InvalidSubCommandException $exception): JsonResponse
    {
        $command = $exception->getCommand();
        $subCommandText = $exception->getSubCommandText();

        $response = new SlackCommandResponse(
            Response::EPHEMERAL,
            sprintf(
                'Unrecognised sub-command: /%s %s. Usage: /%s %s',
                $command->value,
                $subCommandText,
                $command->value,
                implode('|', array_map(
                    fn (SubCommand $subCommand) => $subCommand->value,
                    $command->getSubCommands()
                ))
            ),
            [
                new HeaderBlock('Unrecognised sub-command /'.$command->value.' '.$subCommandText),
                new DividerBlock(),
                ...array_map(function (?SubCommand $subCommand) use ($command) {
                    return new SectionBlock('Usage: '.$command->getUsage($subCommand));
                }, $command->getSubCommands()),
            ]
        );

        return new JsonResponse($response->toArray());
    }

    private function getUnrecognisedCommandResponse(InvalidCommandException $exception): JsonResponse
    {
        $response = new SlackCommandResponse(
            Response::EPHEMERAL,
            $message = sprintf('Unrecognised command: /%s', $exception->getCommandText()),
            [
                new SectionBlock($message),
            ]
        );

        return new JsonResponse($response->toArray());
    }
}
