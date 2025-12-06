<?php

declare(strict_types=1);

namespace App\Controller\Slack;

use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Command\Component\Exception\InvalidArgumentCountException;
use App\Slack\Command\Component\Exception\InvalidSubCommandException;
use App\Slack\Command\Component\Exception\SubCommandMissingException;
use App\Slack\Command\Component\SlackCommandFactory;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Command\SlackCommandResponse;
use App\Slack\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

readonly class CommandController
{
    /** @param SlackCommandHandlerInterface[] $commandHandlers */
    public function __construct(
        private SlackCommandFactory $commandFactory,
        #[AutowireIterator(SlackCommandHandlerInterface::TAG)]
        /** @var SlackCommandHandlerInterface[] $commandHandlers */
        private iterable $commandHandlers,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/slack/command', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): JsonResponse
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
        } catch (\ValueError $error) {
            $this->logger->debug($error->getMessage());

            return $this->getUnrecognisedCommandResponse();
        }

        /** @var SlackCommandHandlerInterface $handler */
        foreach ($this->commandHandlers as $handler) {
            if ($handler->supports($command) && $command->isPending()) {
                $handler->handle($command);
            }
        }

        return new JsonResponse();
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
        $subCommand = $exception->getSubCommand();

        $response = new SlackCommandResponse(
            Response::EPHEMERAL,
            sprintf(
                'Unrecognised sub-command: /%s %s. Usage: /%s %s',
                $command->value,
                $subCommand->value,
                $command->value,
                implode('|', array_map(
                    fn (SubCommand $subCommand) => $subCommand->value,
                    $command->getSubCommands()
                ))
            ),
            [
                new HeaderBlock('Unrecognised sub-command /'.$command->value.' '.$subCommand->value),
                new DividerBlock(),
                ...array_map(function (?SubCommand $subCommand) use ($command) {
                    return new SectionBlock('Usage: '.$command->getUsage($subCommand));
                }, $command->getSubCommands()),
            ]
        );

        return new JsonResponse($response->toArray());
    }

    private function getUnrecognisedCommandResponse(): JsonResponse
    {
        return new JsonResponse();
    }
}
