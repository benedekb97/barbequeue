<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Calculator\ClosestFiveMinutesCalculator;
use App\Enum\DeploymentStatus;
use App\Event\Deployment\DeploymentStartedEvent;
use App\Event\Repository\RepositoryUpdatedEvent;
use App\Resolver\Repository\NextDeploymentResolver;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class RepositoryEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NextDeploymentResolver $nextDeploymentResolver,
        private ClosestFiveMinutesCalculator $closestFiveMinutesCalculator,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RepositoryUpdatedEvent::class => 'handleUpdated',
        ];
    }

    public function handleUpdated(RepositoryUpdatedEvent $event): void
    {
        $repository = $event->getRepository();

        if (null === $repository) {
            return;
        }

        $nextDeployment = $this->nextDeploymentResolver->resolve($repository);

        if (null === $nextDeployment) {
            return;
        }

        if (null !== $expiryMinutes = $nextDeployment->getExpiryMinutes()) {
            $nextDeployment->setExpiresAt(
                $this->closestFiveMinutesCalculator->calculate(CarbonImmutable::now()->addMinutes($expiryMinutes)),
            );
        }

        $workspace = $nextDeployment->getQueue()?->getWorkspace();

        if (!$nextDeployment->isActive() && $event->areNotificationsEnabled() && null !== $workspace) {
            $this->eventDispatcher->dispatch(new DeploymentStartedEvent($nextDeployment, $workspace, true));
        }

        $nextDeployment->setStatus(DeploymentStatus::ACTIVE);

        $this->entityManager->persist($nextDeployment);
    }
}
