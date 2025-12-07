<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Component;

use App\Slack\Interaction\Component\Exception\UnhandledInteractionTypeException;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionArgumentLocation;
use App\Slack\Interaction\InteractionType;
use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use ValueError;

class SlackInteractionFactory
{
    public function __construct(private LoggerInterface $logger) {}

    public function create(Request $request): SlackInteraction
    {
        $payload = json_decode((string) $request->request->get('payload'), true);

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Could not decode interaction payload');
        }

        $request = new Request(request: $payload);

        $this->logger->debug(json_encode($request->request->all()));

        return match ($type = $this->getInteractionType($request)) {
            InteractionType::BLOCK_ACTIONS, InteractionType::MESSAGE_ACTIONS => new SlackInteraction(
                $type,
                $this->getInteraction($request, $type),
                $this->getDomain($request),
                $this->getUserId($request),
                $this->getResponseUrl($request),
                $this->getValue($request)
            ),
            InteractionType::VIEW_CLOSED, InteractionType::VIEW_SUBMISSION => new SlackViewSubmission(
                $interaction = $this->getInteraction($request, $type),
                $this->getDomain($request),
                $this->getUserId($request),
                $this->getArguments($request, $interaction)
            ),
            default => throw new UnhandledInteractionTypeException($type),
        };
    }

    private function getInteractionType(Request $request): InteractionType
    {
        $type = (string) $request->request->get('type');

        return InteractionType::from($type);
    }

    private function getInteraction(Request $request, InteractionType $type): Interaction
    {
        $resolver = match ($type) {
            InteractionType::BLOCK_ACTIONS, InteractionType::MESSAGE_ACTIONS => function (Request $request): Interaction
            {
                /** @var array{action_id: string} $action */
                $action = $request->request->all('actions')[0];

                return Interaction::fromActionId($action['action_id']);
            },
            InteractionType::VIEW_CLOSED, InteractionType::VIEW_SUBMISSION => function (Request $request): Interaction
            {
                /** @var array{private_metadata: string} $view */
                $view = $request->request->all('view');

                /** @var array{action: string} $metadata */
                $metadata = json_decode($view['private_metadata'], true);

                return Interaction::from($metadata['action']);
            },
            default => fn (): Interaction => throw new ValueError(),
        };

        return $resolver($request);
    }

    private function getDomain(Request $request): string
    {
        /** @var array|string[] $team */
        $team = $request->request->all('team');

        return $team['domain'];
    }

    private function getUserId(Request $request): string
    {
        /** @var array|string[] $user */
        $user = $request->request->all('user');

        return $user['id'];
    }

    private function getResponseUrl(Request $request): string
    {
        return (string) $request->request->get('response_url');
    }

    private function getValue(Request $request): string
    {
        /** @var array{value: string} $action */
        $action = $request->request->all('actions')[0];

        return $action['value'];
    }

    private function getArguments(Request $request, Interaction $interaction): array
    {
        $argumentKeys = $interaction->getArguments();

        $arguments = [];

        foreach ($argumentKeys as $argumentKey) {
            $arguments[$argumentKey] = match ($interaction->getArgumentLocation($argumentKey)) {
                InteractionArgumentLocation::STATE => $this->getArgumentFromState($request, $argumentKey),
                InteractionArgumentLocation::PRIVATE_METADATA => $this->getArgumentFromPrivateMetadata($request, $argumentKey),
            };
        }

        return $arguments;
    }

    private function getArgumentFromState(Request $request, string $argumentKey): string|int|null
    {
        /** @var array{state: array} $view */
        $view = $request->request->all('view');

        /** @var array{values: array[]} $state */
        $state = $view['state'];

        foreach ($state['values'] as $value) {
            if (array_key_exists($argumentKey, $value)) {
                /** @var array{type: string, value: string|null} $argumentValue */
                $argumentValue = $value[$argumentKey];

                $value = $argumentValue['value'];

                if ($value === null) {
                    return null;
                }

                return match ($argumentValue['type']) {
                    'number_input' => intval($argumentValue['value']),
                    'plain_text_input', 'email_input' => $argumentValue['value'],
                    default => throw new UnrecognisedInputElementException($argumentValue['type']),
                };
            }
        }

        return null;
    }

    private function getArgumentFromPrivateMetadata(Request $request, string $argumentKey): string|int|null
    {
        /** @var array{private_metadata: string} $view */
        $view = $request->request->all('view');

        /** @var (string|int|null)[] $metadata */
        $metadata = json_decode($view['private_metadata'], true);

        if (array_key_exists($argumentKey, $metadata)) {
            return $metadata[$argumentKey];
        }

        return null;
    }
}
