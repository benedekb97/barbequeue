<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\AddQueue;

use App\Enum\Queue;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;

class AddQueueTypeOptionsResolver extends AbstractAddQueueOptionsResolver implements OptionsResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUE_TYPE;
    }

    public function resolve(): array
    {
        return array_map(function (Queue $queue): array {
            return [
                'text' => [
                    'type' => 'plain_text',
                    'text' => $queue->getName(),
                ],
                'value' => $queue->value,
            ];
        }, Queue::cases());
    }
}
