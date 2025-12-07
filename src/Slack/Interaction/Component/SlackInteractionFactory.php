<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Component;

use App\Slack\Interaction\Interaction;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

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

        return new SlackInteraction(
            $this->getInteraction($request),
            $this->getDomain($request),
            $this->getUserId($request),
            $this->getResponseUrl($request),
            $this->getValue($request),
        );
    }

    private function getInteraction(Request $request): Interaction
    {
        /** @var array{action_id: string} $action */
        $action = $request->request->all('actions')[0];

        return Interaction::fromActionId($action['action_id']);
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
}
