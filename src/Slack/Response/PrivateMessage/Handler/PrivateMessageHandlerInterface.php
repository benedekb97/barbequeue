<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Handler;

use App\Slack\Client\Exception\UnauthorisedClientException;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface PrivateMessageHandlerInterface
{
    public function supports(SlackPrivateMessage $message): bool;

    /** @throws SlackErrorResponse|UnauthorisedClientException|\Throwable */
    public function handle(SlackPrivateMessage $message): void;
}
