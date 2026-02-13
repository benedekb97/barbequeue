<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Modal;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\Modal\JoinQueueModalFactory;
use App\Slack\Surface\Factory\PrivateMetadata\JoinQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\JoinQueue\JoinQueueRepositoryOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(JoinQueueModalFactory::class)]
class JoinQueueModalFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnNullIfQueueNotDeploymentQueue(): void
    {
        $queue = $this->createStub(Queue::class);

        $factory = new JoinQueueModalFactory(
            $this->createStub(InputModalFactory::class),
            $this->createStub(ModalInputsFactory::class),
            $this->createStub(JoinQueuePrivateMetadataFactory::class),
            $this->createStub(JoinQueueRepositoryOptionsResolver::class),
        );

        $result = $factory->create($queue, $this->createStub(UserTriggeredInteractionInterface::class));

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldCreateInputModal(): void
    {
        $privateMetadataFactory = $this->createMock(JoinQueuePrivateMetadataFactory::class);
        $privateMetadataFactory->expects($this->once())
            ->method('setQueue')
            ->with($queue = $this->createStub(DeploymentQueue::class))
            ->willReturnSelf();

        $privateMetadataFactory->expects($this->once())
            ->method('setResponseUrl')
            ->with($responseUrl = 'responseUrl')
            ->willReturnSelf();

        $optionsResolver = $this->createMock(JoinQueueRepositoryOptionsResolver::class);
        $optionsResolver->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $modalInputsFactory = $this->createMock(ModalInputsFactory::class);
        $modalInputsFactory->expects($this->once())
            ->method('setDefaultValueResolvers')
            ->with([])
            ->willReturnSelf();

        $modalInputsFactory->expects($this->once())
            ->method('setOptionsResolvers')
            ->with([$optionsResolver])
            ->willReturnSelf();

        $inputModalFactory = $this->createMock(InputModalFactory::class);
        $inputModalFactory->expects($this->once())
            ->method('setInputsFactory')
            ->with($modalInputsFactory)
            ->willReturnSelf();

        $inputModalFactory->expects($this->once())
            ->method('setPrivateMetadataFactory')
            ->with($privateMetadataFactory)
            ->willReturnSelf();

        $inputModalFactory->expects($this->once())
            ->method('create')
            ->with(
                $interaction = $this->createMock(UserTriggeredInteractionInterface::class),
                Modal::JOIN_QUEUE_DEPLOYMENT,
                'Cancel',
                'Join',
            )
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $interaction->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn($responseUrl);

        $factory = new JoinQueueModalFactory(
            $inputModalFactory,
            $modalInputsFactory,
            $privateMetadataFactory,
            $optionsResolver,
        );

        $result = $factory->create($queue, $interaction);

        $this->assertSame($modal, $result);
    }
}
