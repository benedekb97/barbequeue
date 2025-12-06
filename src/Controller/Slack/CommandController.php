<?php

declare(strict_types=1);

namespace App\Controller\Slack;

use App\Slack\Command\Component\Exception\InvalidArgumentCountException;
use App\Slack\Command\Component\Exception\InvalidSubCommandException;
use App\Slack\Command\Component\Exception\SubCommandMissingException;
use App\Slack\Command\Component\SlackCommandFactory;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use ValueError;

class CommandController
{
    public function __construct(
        private SlackCommandFactory $commandFactory,
        #[AutowireIterator(SlackCommandHandlerInterface::TAG)]
        private iterable $commandHandlers,
    ) {}

    #[Route('/slack/command', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $command = $this->commandFactory->createFromRequest($request);
        } catch (SubCommandMissingException $exception) {

        } catch (InvalidArgumentCountException $exception) {

        } catch (InvalidSubCommandException $exception) {

        } catch (ValueError $error) {

        }

        foreach ($this->commandHandlers as $handler) {
            if ($handler->supports($command) && $command->isPending()) {
                $handler->handle($command);
            }
        }
    }
}
