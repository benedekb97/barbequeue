<?php

declare(strict_types=1);

namespace App\Slack\Common\Component;

interface UserTriggeredInteractionInterface
{
    public function getUserId(): string;

    public function getTriggerId(): string;

    public function getTeamId(): string;

    public function getResponseUrl(): string;
}
