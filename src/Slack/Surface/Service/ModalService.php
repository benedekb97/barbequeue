<?php

declare(strict_types=1);

namespace App\Slack\Surface\Service;

use App\Entity\Queue;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\InputBlock;
use App\Slack\BlockElement\Component\EmailInputElement;
use App\Slack\BlockElement\Component\NumberInputElement;
use App\Slack\BlockElement\Component\PlainTextInputElement;
use App\Slack\BlockElement\Component\SlackBlockElement;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Interaction\Handler\EditQueueInteractionHandler;
use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use App\Slack\Surface\Component\ModalSurface;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ServerException;

readonly class ModalService
{
    private Client $client;

    public function __construct(
        private LoggerInterface $logger,
        string $slackAccessToken,
    ) {
        $this->client = ClientFactory::create($slackAccessToken);
    }

    public function createQueueModal(Queue $queue, UserTriggeredInteractionInterface $interaction): void
    {
        $modal = new ModalSurface(
            $interaction->getTriggerId(),
            sprintf('Edit the %s queue', $queue->getName()),
            $this->getEditQueueiNputFields($queue),
            'edit-queue-'.$queue->getName().'-'.$interaction->getUserId(),
            'Cancel',
            'Save',
            privateMetadata: json_encode(['queue' => $queue->getId(), 'action' => Interaction::EDIT_QUEUE->value]),
        );

        try {
            $this->client->viewsOpen($modal->toArray());
        } catch (SlackErrorResponse $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error(json_encode($exception->getResponseMetadata()));
        } catch (ServerException $exception) {
            $response = $exception->getResponse();

            $this->logger->error($exception->getMessage());
            $this->logger->error($response->getContent(false));
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception::class);
            $this->logger->error($exception->getTraceAsString());

            return;
        }
    }

    private function getEditQueueInputFields(Queue $queue): array
    {
        $requiredFields = EditQueueInteractionHandler::REQUIRED_FIELDS;
        $optionalFields = EditQueueInteractionHandler::OPTIONAL_ARGUMENTS;

        $blocks = [];

        foreach ($requiredFields as $fieldKey => $fieldType) {
            if ($fieldType === null) {
                continue;
            }

            if (!empty($blocks)) {
                $blocks[] = new DividerBlock();
            }

            $blocks[] = $this->getBlockForKey($queue, $fieldKey, $fieldType, true);
        }

        foreach ($optionalFields as $fieldKey => $fieldType) {
            if ($fieldType === null) {
                continue;
            }

            if (!empty($blocks)) {
                $blocks[] = new DividerBlock();
            }

            $blocks[] = $this->getBlockForKey($queue, $fieldKey, $fieldType, false);
        }

        return $blocks;
    }

    private function getBlockForKey(Queue $queue, string $fieldKey, string $fieldType, bool $required): InputBlock
    {
        $getter = EditQueueInteractionHandler::FIELD_ENTITY_GETTER_MAP[$fieldKey];
        $placeholder = EditQueueInteractionHandler::FIELD_PLACEHOLDER_MAP[$fieldKey];
        $hint = EditQueueInteractionHandler::FIELD_HINT_MAP[$fieldKey];

        return new InputBlock(
            EditQueueInteractionHandler::FIELD_LABEL_MAP[$fieldKey],
            $this->createBlockElement($fieldType, $fieldKey, $queue->$getter(), $placeholder),
            dispatchAction: false,
            hint: $hint,
            optional: !$required,
        );
    }

    private function createBlockElement(
        string $fieldType,
        string $fieldKey,
        string|int|null $defaultValue,
        string $placeholder
    ): SlackBlockElement {
        return match ($fieldType) {
            EmailInputElement::class => new EmailInputElement(
                actionId: $fieldKey,
                initialValue: $defaultValue !== null ? "$defaultValue" : null,
                placeholder: $placeholder
            ),
            NumberInputElement::class => new NumberInputElement(
                isDecimalAllowed: false,
                actionId: $fieldKey,
                initialValue: $defaultValue !== null ? "$defaultValue" : null,
                placeholder: $placeholder
            ),
            PlainTextInputElement::class => new PlainTextInputElement(
                actionId: $fieldKey,
                initialValue: $defaultValue !== null ? "$defaultValue" : null,
                placeholder: $placeholder
            ),
            default => throw new UnrecognisedInputElementException($fieldType),
        };
    }
}
