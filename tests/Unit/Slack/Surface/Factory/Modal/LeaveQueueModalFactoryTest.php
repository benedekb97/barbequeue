<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Modal;

use App\Entity\Queue;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\Modal\LeaveQueueModalFactory;
use App\Slack\Surface\Factory\PrivateMetadata\LeaveQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\LeaveQueue\LeaveQueueOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(LeaveQueueModalFactory::class)]
class LeaveQueueModalFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateInputModal(): void
    {
        $metadataFactory = $this->createMock(LeaveQueuePrivateMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('setQueue')
            ->with($queue = $this->createStub(Queue::class))
            ->willReturnSelf();

        $metadataFactory->expects($this->once())
            ->method('setResponseUrl')
            ->with($responseUrl = 'responseUrl')
            ->willReturnSelf();

        $optionsResolver = $this->createMock(LeaveQueueOptionsResolver::class);
        $optionsResolver->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $optionsResolver->expects($this->once())
            ->method('setUserId')
            ->with($userId = 'userId')
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
                Modal::LEAVE_QUEUE,
                'Cancel',
                'Leave',
            )
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $interaction->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn($responseUrl);

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $factory = new LeaveQueueModalFactory(
            $inputModalFactory,
            $modalInputsFactory,
            $metadataFactory,
            $optionsResolver,
        );

        $result = $factory->create($queue, $interaction);

        $this->assertSame($modal, $result);
    }
}
