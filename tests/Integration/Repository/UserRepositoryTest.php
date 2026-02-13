<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Queue\Workspaces;
use App\Repository\UserRepository;
use App\Repository\UserRepositoryInterface;
use App\Repository\WorkspaceRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UserRepository::class)]
class UserRepositoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnUserBySlackIdAndWorkspace(): void
    {
        /** @var UserRepositoryInterface $repository */
        $repository = static::getContainer()->get(UserRepositoryInterface::class);

        /** @var WorkspaceRepositoryInterface $workspaceRepository */
        $workspaceRepository = static::getContainer()->get(WorkspaceRepositoryInterface::class);

        $workspace = $workspaceRepository->findOneBy([
            'slackId' => Workspaces::FIRST->value,
        ]);

        $this->assertNotNull($workspace);

        $user = $repository->findOneBy([
            'slackId' => 'userId',
            'workspace' => $workspace,
        ]);

        $this->assertNotNull($user);
        $this->assertEquals($workspace, $user->getWorkspace());
        $this->assertEquals('userId', $user->getSlackId());
    }

    #[Test]
    public function itShouldFindOneBySlackIdAndWorkspaceSlackId(): void
    {
        /** @var UserRepositoryInterface $repository */
        $repository = static::getContainer()->get(UserRepositoryInterface::class);

        $result = $repository->findOneBySlackIdAndWorkspaceSlackId('userId', Workspaces::FIRST->value);

        $this->assertNotNull($result);
    }
}
