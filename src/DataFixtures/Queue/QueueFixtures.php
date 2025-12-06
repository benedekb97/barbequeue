<?php

declare(strict_types=1);

namespace App\DataFixtures\Queue;

use App\Entity\Queue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QueueFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (Queues::cases() as $queueType) {
            $queue = new Queue()
                ->setName($queueType->value)
                ->setDomain($queueType->getDomain()->value)
                ->setMaximumEntriesPerUser($queueType->getMaximumEntriesPerUser())
                ->setExpiryMinutes($queueType->getExpiryMinutes());

            $manager->persist($queue);
        }

        $manager->flush();
    }
}
