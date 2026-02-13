<?php

declare(strict_types=1);

namespace App\DataFixtures\Queue;

use App\Entity\Administrator;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QueueFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (Workspaces::cases() as $teamId) {
            $workspace = new Workspace()
                ->setName($teamId->getName())
                ->setSlackId($teamId->value)
                ->setBotToken($teamId->getBotToken());

            foreach ($teamId->getQueues() as $queueType) {
                $queue = new Queue()
                    ->setName($queueType->value)
                    ->setMaximumEntriesPerUser($queueType->getMaximumEntriesPerUser())
                    ->setExpiryMinutes($queueType->getExpiryMinutes());

                $workspace->addQueue($queue);

                $manager->persist($queue);
            }

            $firstAdministrator = null;

            foreach ($teamId->getAdministrators() as $administratorType) {
                $user = new User()
                    ->setSlackId($administratorType->value)
                    ->setWorkspace($workspace);

                $administrator = new Administrator()
                    ->setUser($user)
                    ->setWorkspace($workspace);

                if (null !== $firstAdministrator) {
                    $administrator->setAddedBy($firstAdministrator);
                } else {
                    $firstAdministrator = $administrator;
                }

                $manager->persist($administrator);
            }

            foreach ($teamId->getRepositories() as $repositoryType) {
                $repository = new Repository()
                    ->setName($repositoryType->value)
                    ->setUrl($repositoryType->getUrl());

                $workspace->addRepository($repository);

                $manager->persist($repository);
            }

            $manager->persist($workspace);
        }

        $manager->flush();
    }
}
