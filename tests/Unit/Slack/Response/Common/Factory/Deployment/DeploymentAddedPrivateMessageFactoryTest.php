<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common\Factory\Deployment;

use App\Entity\Deployment;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentAddedPrivateMessageFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentAddedPrivateMessageFactory::class)]
class DeploymentAddedPrivateMessageFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreatePrivateMessage(): void
    {
        $user = $this->createStub(User::class);

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $deployment->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $deployment->expects($this->once())
            ->method('getPlacement')
            ->willReturn('1st');

        $blocker->expects($this->once())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->once())
            ->method('getRepository')
            ->willReturn($blockerRepository = $this->createMock(Repository::class));

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $blockerRepository->expects($this->once())
            ->method('getName')
            ->willReturn('blockerRepositoryName');

        $factory = new DeploymentAddedPrivateMessageFactory();

        $result = $factory->create($deployment, $workspace = $this->createStub(Workspace::class), $user);

        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertEquals($user, $result->getUser());

        $result = $result->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsString($result['blocks']);

        $blocks = json_decode($result['blocks'], true);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'userLink joined the `queueName` queue to deploy `description` to `repositoryName`. They are 1st in the queue and have to wait for blockerUserLink to finish deploying to `blockerRepositoryName` before they can start.',
            $blocks[0],
            expectedAccessory: [
                'text' => [
                    'type' => 'plain_text',
                    'text' => 'More information',
                ],
                'type' => 'button',
                'action_id' => 'more-info',
                'url' => 'link',
            ]
        );
    }
}
