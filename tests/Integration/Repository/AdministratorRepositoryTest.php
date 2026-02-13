<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Queue\Administrators;
use App\DataFixtures\Queue\Workspaces;
use App\Entity\Administrator;
use App\Repository\AdministratorRepository;
use App\Repository\AdministratorRepositoryInterface;
use App\Repository\WorkspaceRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorRepository::class)]
class AdministratorRepositoryTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
    }

    #[Test]
    public function itShouldReturnNullIfNoAdministratorExistsWithNonExistentUserIdWithTeamId(): void
    {
        $result = $this->getRepository()->findOneByUserIdAndTeamId(
            'non-existent-user',
            Workspaces::FIRST->value
        );

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfNoAdministratorExistsWithNonExistentUserIdOnNonExistentTeamId(): void
    {
        $result = $this->getRepository()->findOneByUserIdAndTeamId(
            'non-existent-user',
            'non-existent-teamId'
        );

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfNoAdministratorExistsWithUserIdOnNonExistentTeamId(): void
    {
        $result = $this->getRepository()->findOneByUserIdAndTeamId(
            Administrators::FIRST_ADMINISTRATOR->value,
            'non-existent-teamId'
        );

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnAdministratorIfAdministratorExistsWithUserIdWithTeamId(): void
    {
        $result = $this->getRepository()->findOneByUserIdAndTeamId(
            Administrators::FIRST_ADMINISTRATOR->value,
            Workspaces::FIRST->value,
        );

        $this->assertInstanceOf(Administrator::class, $result);
        $this->assertNotNull($result->getId());
        $this->assertNotNull($result->getWorkspace());
        $this->assertNotNull($result->getWorkspace()->getSlackId());
    }

    private function getRepository(): AdministratorRepositoryInterface
    {
        /** @var AdministratorRepositoryInterface $repository */
        $repository = self::getContainer()->get(AdministratorRepositoryInterface::class);

        return $repository;
    }

    #[Test]
    public function itShouldReturnAdministratorIfAdministratorExistsOnWorkspace(): void
    {
        /** @var WorkspaceRepositoryInterface $workspaceRepository */
        $workspaceRepository = self::getContainer()->get(WorkspaceRepositoryInterface::class);

        $workspace = $workspaceRepository->findOneBy(['slackId' => Workspaces::FIRST->value]);

        $this->assertNotNull($workspace);

        /** @var AdministratorRepositoryInterface $repository */
        $repository = self::getContainer()->get(AdministratorRepositoryInterface::class);

        $result = $repository->findOneByUserIdAndWorkspace(Administrators::FIRST_ADMINISTRATOR->value, $workspace);

        $this->assertInstanceOf(Administrator::class, $result);
        $this->assertNotNull($result->getId());
    }
}
