<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Modal;

use App\Entity\Queue;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\Modal\PopQueueModalFactory;
use App\Slack\Surface\Factory\PrivateMetadata\PopQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\PopQueue\PopQueueOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PopQueueModalFactory::class)]
class PopQueueModalFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateInputModal(): void
    {
        $metadataFactory = $this->createMock(PopQueuePrivateMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('setQueue')
            ->with($queue = $this->createStub(Queue::class))
            ->willReturnSelf();

        $metadataFactory->expects($this->once())
            ->method('setResponseUrl')
            ->with($responseUrl = 'responseUrl')
            ->willReturnSelf();

        $optionsResolver = $this->createMock(PopQueueOptionsResolver::class);
        $optionsResolver->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $modalInputsFactory = $this->createMock(ModalInputsFactory::class);
        $modalInputsFactory->expects($this->once())
            ->method('setOptionsResolvers')
            ->with([$optionsResolver])
            ->willReturnSelf();

        $modalInputsFactory->expects($this->once())
            ->method('setDefaultValueResolvers')
            ->with([])
            ->willReturnSelf();

        $inputModalFactory = $this->createMock(InputModalFactory::class);
        $inputModalFactory->expects($this->once())
            ->method('setInputsFactory')
            ->with($modalInputsFactory)
            ->willReturnSelf();

        $inputModalFactory->expects($this->once())
            ->method('setPrivateMetadataFactory')
            ->with($metadataFactory)
            ->willReturnSelf();

        $inputModalFactory->expects($this->once())
            ->method('create')
            ->with(
                $interaction = $this->createMock(UserTriggeredInteractionInterface::class),
                Modal::POP_QUEUE,
                'Cancel',
                'Remove',
            )
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $interaction->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn($responseUrl);

        $factory = new PopQueueModalFactory(
            $inputModalFactory,
            $modalInputsFactory,
            $metadataFactory,
            $optionsResolver,
        );

        $result = $factory->create($queue, $interaction);

        $this->assertSame($modal, $result);
    }
}
