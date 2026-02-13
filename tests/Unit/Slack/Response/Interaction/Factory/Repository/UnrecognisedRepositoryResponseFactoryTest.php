<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Repository\RepositoryRepositoryInterface;
use App\Slack\Response\Interaction\Factory\Repository\UnrecognisedRepositoryResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnrecognisedRepositoryResponseFactory::class)]
class UnrecognisedRepositoryResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $firstRepository = $this->createMock(Repository::class);
        $firstRepository->expects($this->once())
            ->method('getName')
            ->willReturn('firstRepositoryName');

        $secondRepository = $this->createMock(Repository::class);
        $secondRepository->expects($this->once())
            ->method('getName')
            ->willReturn('secondRepositoryName');

        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByTeamId')
            ->with($teamId = 'teamId')
            ->willReturn([$firstRepository, $secondRepository]);

        $factory = new UnrecognisedRepositoryResponseFactory($repository);

        $result = $factory->create($name = 'name', $teamId)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertHeaderBlockCorrectlyFormatted(
            'Repository `name` does not exist',
            $blocks[0],
        );

        $this->assertSectionBlockCorrectlyFormatted(
            'Available repositories: `firstRepositoryName`, `secondRepositoryName`',
            $blocks[1],
        );
    }
}
