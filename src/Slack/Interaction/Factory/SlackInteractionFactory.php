<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Factory;

use App\Slack\Interaction\Exception\InvalidPayloadException;
use App\Slack\Interaction\Exception\UnhandledInteractionTypeException;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\Resolver\InteractionArgumentsResolver;
use App\Slack\Interaction\Resolver\InteractionPayloadResolver;
use App\Slack\Interaction\Resolver\InteractionPrivateMetadataResponseUrlResolver;
use App\Slack\Interaction\Resolver\InteractionResolver;
use App\Slack\Interaction\Resolver\InteractionTypeResolver;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use Symfony\Component\HttpFoundation\Request;

readonly class SlackInteractionFactory
{
    public function __construct(
        private InteractionPayloadResolver $payloadResolver,
        private InteractionTypeResolver $interactionTypeResolver,
        private InteractionResolver $interactionResolver,
        private InteractionArgumentsResolver $argumentsResolver,
        private InteractionPrivateMetadataResponseUrlResolver $privateMetadataResponseUrlResolver,
    ) {
    }

    /**
     * @throws InvalidPayloadException
     * @throws \ValueError
     * @throws UnhandledInteractionTypeException
     */
    public function create(Request $request): SlackInteraction
    {
        $request = $this->payloadResolver->resolve($request);
        $type = $this->interactionTypeResolver->resolve($request);
        $interaction = $this->interactionResolver->resolve($type, $request);

        $userId = $this->getUserId($request);
        $teamId = $this->getTeamId($request);

        return match ($type) {
            InteractionType::BLOCK_ACTIONS, InteractionType::MESSAGE_ACTIONS => new SlackInteraction(
                $type,
                $interaction,
                $teamId,
                $userId,
                $this->getUserName($request),
                $this->getResponseUrl($request),
                $this->getValue($request),
                $this->getTriggerId($request),
                $this->getViewId($request),
            ),
            InteractionType::VIEW_CLOSED, InteractionType::VIEW_SUBMISSION => new SlackViewSubmission(
                interaction: $interaction,
                teamId: $teamId,
                userId: $userId,
                userName: $this->getUserName($request),
                arguments: $this->argumentsResolver->resolve($interaction, $request),
                triggerId: $this->getTriggerId($request),
                responseUrl: $this->getResponseUrl($request),
            ),
            default => throw new UnhandledInteractionTypeException($type),
        };
    }

    private function getTeamId(Request $request): string
    {
        /** @var array|string[] $team */
        $team = $request->request->all('team');

        return $team['id'];
    }

    private function getUserId(Request $request): string
    {
        /** @var array|string[] $user */
        $user = $request->request->all('user');

        return $user['id'];
    }

    private function getUserName(Request $request): string
    {
        /** @var array<string, string> $user */
        $user = $request->request->all('user');

        return $user['name'] ?? $user['username'] ?? '';
    }

    private function getResponseUrl(Request $request): string
    {
        if ($request->request->has('response_url')) {
            return (string) $request->request->get('response_url');
        }

        return (string) $this->privateMetadataResponseUrlResolver->resolve($request);
    }

    private function getValue(Request $request): string
    {
        /** @var array{value?: string, selected_option?: array{value: string}} $action */
        $action = $request->request->all('actions')[0];

        if (array_key_exists('value', $action)) {
            return $action['value'];
        }

        if (array_key_exists('selected_option', $action)) {
            return $action['selected_option']['value'];
        }

        return '';
    }

    private function getTriggerId(Request $request): string
    {
        return (string) $request->request->get('trigger_id');
    }

    private function getViewId(Request $request): ?string
    {
        $view = $request->request->all('view');

        if (empty($view)) {
            return null;
        }

        if (!array_key_exists('id', $view)) {
            return null;
        }

        /** @var string $viewId */
        $viewId = $view['id'];

        return $viewId;
    }
}
