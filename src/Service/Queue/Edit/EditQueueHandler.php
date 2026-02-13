<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit;

use App\Service\Queue\Edit\Handler\EditQueueHandlerInterface;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class EditQueueHandler
{
    public function __construct(
        /** @var EditQueueHandlerInterface[] $handlers */
        #[AutowireIterator(EditQueueHandlerInterface::class)]
        private iterable $handlers,
    ) {
    }

    /** @throws EntityNotFoundException|RepositoryNotFoundException */
    public function handle(EditQueueContext $context): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($context)) {
                $handler->handle($context);
            }
        }
    }
}
