<?php

declare(strict_types=1);

namespace App\DataFixtures\QueuedUser;

use App\DataFixtures\Queue\Queues;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QueuedUserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $queues = $manager->getRepository(Queue::class)->findAll();

        foreach ($queues as $queue) {
            if (!$queue->getWorkspace()?->hasUserWithId('userId')) {
                $user = new User()
                    ->setSlackId('userId');

                $queue->getWorkspace()?->addUser($user);

                $manager->persist($user);
            }

            if (!$queue->getWorkspace()?->hasUserWithId('expiredUserId')) {
                $user = new User()
                    ->setSlackId('expiredUserId');

                $queue->getWorkspace()?->addUser($user);

                $manager->persist($user);
            }
        }

        foreach ($queues as $queue) {
            for ($i = 0; $i < min($queue->getMaximumEntriesPerUser() ?? 3, 3); ++$i) {
                $queuedUser = new QueuedUser()
                    ->setQueue($queue)
                    ->setExpiresAt(
                        0 === $i && null !== $queue->getExpiryMinutes()
                            ? CarbonImmutable::now()->addYear()
                            : null
                    )
                    ->setUser($queue->getWorkspace()?->getUserById('userId'));

                $manager->persist($queuedUser);
            }

            if ($queue->getName() === Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT->value) {
                $expiredUser = new QueuedUser()
                    ->setQueue($queue)
                    ->setUser($queue->getWorkspace()?->getUserById('expiredUserId'))
                    ->setExpiresAt(CarbonImmutable::now()->subYear());

                $manager->persist($expiredUser);
            }
        }

        $manager->flush();
    }
}
