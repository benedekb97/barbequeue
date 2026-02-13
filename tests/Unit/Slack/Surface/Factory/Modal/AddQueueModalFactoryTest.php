<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Modal;

use App\Entity\Workspace;
use App\Enum\Queue;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\Modal\AddQueueModalFactory;
use App\Slack\Surface\Factory\PrivateMetadata\AddQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\AddQueue\AbstractAddQueueOptionsResolver;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueTypeDefaultValueResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AddQueueModalFactory::class)]
class AddQueueModalFactoryTest extends KernelTestCase
{
    #[Test, DataProvider('provideModalTypes')]
    public function itShouldCreateInputModalForQueueType(?Queue $queueType, Modal $modalType): void
    {
        $interaction = $this->createMock(UserTriggeredInteractionInterface::class);
        $interaction->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn($responseUrl = 'responseUrl');

        $privateMetadataFactory = $this->createMock(AddQueuePrivateMetadataFactory::class);
        $privateMetadataFactory->expects($this->once())
            ->method('setQueue')
            ->with($queueType)
            ->willReturnSelf();

        $privateMetadataFactory->expects($this->once())
            ->method('setResponseUrl')
            ->with($responseUrl)
            ->willReturnSelf();

        $addQueueTypeDefaultValueResolver = $this->createMock(AddQueueTypeDefaultValueResolver::class);
        $addQueueTypeDefaultValueResolver->expects($this->once())
            ->method('setQueue')
            ->with($queueType);

        $optionsResolver = $this->createMock(AbstractAddQueueOptionsResolver::class);
        $optionsResolver->expects($this->once())
            ->method('setWorkspace')
            ->with($workspace = $this->createStub(Workspace::class))
            ->willReturnSelf();

        $inputsFactory = $this->createMock(ModalInputsFactory::class);
        $inputsFactory->expects($this->once())
            ->method('setOptionsResolvers')
            ->with([$optionsResolver])
            ->willReturnSelf();

        $inputsFactory->expects($this->once())
            ->method('setDefaultValueResolvers')
            ->with([$addQueueTypeDefaultValueResolver])
            ->willReturnSelf();

        $inputModalFactory = $this->createMock(InputModalFactory::class);
        $inputModalFactory->expects($this->once())
            ->method('setInputsFactory')
            ->with($inputsFactory)
            ->willReturnSelf();

        $inputModalFactory->expects($this->once())
            ->method('setPrivateMetadataFactory')
            ->with($privateMetadataFactory)
            ->willReturnSelf();

        $inputModalFactory->expects($this->once())
            ->method('create')
            ->with($interaction, $modalType, 'Cancel', 'Save')
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $factory = new AddQueueModalFactory(
            $inputModalFactory,
            $inputsFactory,
            $privateMetadataFactory,
            [$optionsResolver],
            $addQueueTypeDefaultValueResolver,
        );

        $result = $factory->create($queueType, $interaction, $workspace);

        $this->assertSame($modal, $result);
    }

    public static function provideModalTypes(): array
    {
        return [
            [null, Modal::ADD_QUEUE],
            [Queue::SIMPLE, Modal::ADD_QUEUE_SIMPLE],
            [Queue::DEPLOYMENT, Modal::ADD_QUEUE_DEPLOYMENT],
        ];
    }
}
