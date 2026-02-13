<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Modal;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\Workspace;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\Modal\EditQueueModalFactory;
use App\Slack\Surface\Factory\PrivateMetadata\EditQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueBehaviourOptionsResolver;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueRepositoryOptionsResolver;
use App\Slack\Surface\Factory\Resolver\Queue\AbstractQueueDefaultValueResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(EditQueueModalFactory::class)]
class EditQueueModalFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCallInputModalFactoryCreate(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $defaultValueResolver = $this->createMock(AbstractQueueDefaultValueResolver::class);
        $defaultValueResolver->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $interaction = $this->createMock(UserTriggeredInteractionInterface::class);
        $interaction->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn($responseUrl = 'responseUrl');

        $inputsFactory = $this->createMock(ModalInputsFactory::class);
        $inputsFactory->expects($this->once())
            ->method('setDefaultValueResolvers')
            ->with([$defaultValueResolver])
            ->willReturnSelf();

        $privateMetadataFactory = $this->createMock(EditQueuePrivateMetadataFactory::class);
        $privateMetadataFactory->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $privateMetadataFactory->expects($this->once())
            ->method('setResponseUrl')
            ->with($responseUrl)
            ->willReturnSelf();

        $modalFactory = $this->createMock(InputModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('setInputsFactory')
            ->with($inputsFactory)
            ->willReturnSelf();

        $modalFactory->expects($this->once())
            ->method('setPrivateMetadataFactory')
            ->with($privateMetadataFactory)
            ->willReturnSelf();

        $modalFactory->expects($this->once())
            ->method('create')
            ->with(
                $interaction,
                Modal::EDIT_QUEUE,
                'Cancel',
                'Save',
            )
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $addQueueRepositoryOptionsResolver = $this->createMock(AddQueueRepositoryOptionsResolver::class);
        $addQueueRepositoryOptionsResolver->expects($this->once())
            ->method('setWorkspace')
            ->with($workspace)
            ->willReturnSelf();

        $addQueueBehaviourOptionsResolver = $this->createStub(AddQueueBehaviourOptionsResolver::class);

        $inputsFactory->expects($this->once())
            ->method('setOptionsResolvers')
            ->with([
                $addQueueRepositoryOptionsResolver,
                $addQueueBehaviourOptionsResolver,
            ])
            ->willReturnSelf();

        $factory = new EditQueueModalFactory(
            $modalFactory,
            $inputsFactory,
            $privateMetadataFactory,
            [$defaultValueResolver],
            $addQueueRepositoryOptionsResolver,
            $addQueueBehaviourOptionsResolver,
        );

        $result = $factory->create($queue, $interaction);

        $this->assertSame($modal, $result);
    }

    #[Test]
    public function itShouldCreateInputModalFactoryCreateWithDeploymentQueue(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $defaultValueResolver = $this->createMock(AbstractQueueDefaultValueResolver::class);
        $defaultValueResolver->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $interaction = $this->createMock(UserTriggeredInteractionInterface::class);
        $interaction->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn($responseUrl = 'responseUrl');

        $inputsFactory = $this->createMock(ModalInputsFactory::class);
        $inputsFactory->expects($this->once())
            ->method('setDefaultValueResolvers')
            ->with([$defaultValueResolver])
            ->willReturnSelf();

        $privateMetadataFactory = $this->createMock(EditQueuePrivateMetadataFactory::class);
        $privateMetadataFactory->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $privateMetadataFactory->expects($this->once())
            ->method('setResponseUrl')
            ->with($responseUrl)
            ->willReturnSelf();

        $modalFactory = $this->createMock(InputModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('setInputsFactory')
            ->with($inputsFactory)
            ->willReturnSelf();

        $modalFactory->expects($this->once())
            ->method('setPrivateMetadataFactory')
            ->with($privateMetadataFactory)
            ->willReturnSelf();

        $modalFactory->expects($this->once())
            ->method('create')
            ->with(
                $interaction,
                Modal::EDIT_QUEUE_DEPLOYMENT,
                'Cancel',
                'Save',
            )
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $addQueueRepositoryOptionsResolver = $this->createMock(AddQueueRepositoryOptionsResolver::class);
        $addQueueRepositoryOptionsResolver->expects($this->once())
            ->method('setWorkspace')
            ->with($workspace)
            ->willReturnSelf();

        $addQueueBehaviourOptionsResolver = $this->createStub(AddQueueBehaviourOptionsResolver::class);

        $inputsFactory->expects($this->once())
            ->method('setOptionsResolvers')
            ->with([
                $addQueueRepositoryOptionsResolver,
                $addQueueBehaviourOptionsResolver,
            ])
            ->willReturnSelf();

        $factory = new EditQueueModalFactory(
            $modalFactory,
            $inputsFactory,
            $privateMetadataFactory,
            [$defaultValueResolver],
            $addQueueRepositoryOptionsResolver,
            $addQueueBehaviourOptionsResolver,
        );

        $result = $factory->create($queue, $interaction);

        $this->assertSame($modal, $result);
    }
}
