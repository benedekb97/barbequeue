<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage;

use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Block\Component\SlackBlock;
use App\Slack\Message\Component\SlackMessage;

readonly class SlackPrivateMessage extends SlackMessage
{
    public function __construct(
        private ?User $user,
        private ?Workspace $workspace,
        ?string $text,
        ?array $blocks,
    ) {
        parent::__construct($text, $blocks);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function toArray(): array
    {
        return array_filter([
            'blocks' => $this->blocks
                ? json_encode(array_map(
                    function (?SlackBlock $block) {
                        return $block?->toArray();
                    },
                    $this->blocks
                ))
                : null,
            'text' => $this->text,
        ]);
    }
}
