<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\AddQueue;

use App\Enum\QueueBehaviour;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;

class AddQueueBehaviourOptionsResolver extends AbstractAddQueueOptionsResolver implements OptionsResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUE_BEHAVIOUR;
    }

    public function resolve(): array
    {
        return array_map(function (QueueBehaviour $behaviour) {
            return [
                'text' => [
                    'type' => 'plain_text',
                    'text' => $behaviour->getName(),
                ],
                'value' => $behaviour->value,
            ];
        }, QueueBehaviour::cases());
    }
}
