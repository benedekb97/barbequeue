<?php

declare(strict_types=1);

namespace App\Slack\Surface\Component;

use App\Slack\Block\Component\SlackBlock;
use App\Slack\Surface\Surface;

class ModalSurface extends SlackSurface
{
    public function __construct(
        private readonly string $triggerId,
        private readonly string $title,
        /** @var SlackBlock[] $blocks */
        private readonly array $blocks,
        private readonly ?string $close = null,
        private readonly ?string $submit = null,
        private readonly ?string $privateMetadata = null,
        private readonly ?string $callbackId = null,
        private readonly bool $notifyOnClose = false,
        private readonly bool $clearOnClose = false,
    ) {
    }

    public function getType(): Surface
    {
        return Surface::MODAL;
    }

    public function toArray(): array
    {
        return array_filter([
            'trigger_id' => $this->triggerId,
            'view' => json_encode(array_filter([
                'type' => $this->getType()->value,
                'title' => [
                    'type' => 'plain_text',
                    'text' => $this->title,
                ],
                'blocks' => array_map(fn (SlackBlock $block) => $block->toArray(), $this->blocks),
                'close' => $this->close ? [
                    'type' => 'plain_text',
                    'text' => $this->close,
                ] : null,
                'submit' => $this->submit ? [
                    'type' => 'plain_text',
                    'text' => $this->submit,
                ] : null,
                'private_metadata' => $this->privateMetadata,
                'callback_id' => $this->callbackId,
                'clear_on_close' => $this->clearOnClose,
                'notify_on_close' => $this->notifyOnClose,
            ])),
        ]);
    }
}
