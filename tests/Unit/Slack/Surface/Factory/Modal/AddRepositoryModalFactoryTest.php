<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Modal;

use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\Modal\AddRepositoryModalFactory;
use App\Slack\Surface\Factory\PrivateMetadata\AddRepositoryPrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\Repository\AbstractRepositoryOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AddRepositoryModalFactory::class)]
class AddRepositoryModalFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCallInputModalFactoryCreate(): void
    {
        $interaction = $this->createMock(UserTriggeredInteractionInterface::class);
        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $optionsResolver = $this->createMock(AbstractRepositoryOptionsResolver::class);
        $optionsResolver->expects($this->once())
            ->method('setTeamId')
            ->with($teamId)
            ->willReturnSelf();

        $inputsFactory = $this->createMock(ModalInputsFactory::class);
        $inputsFactory->expects($this->once())
            ->method('setOptionsResolvers')
            ->with([$optionsResolver])
            ->willReturnSelf();

        $inputsFactory->expects($this->once())
            ->method('setDefaultValueResolvers')
            ->with([])
            ->willReturnSelf();

        $privateMetadataFactory = $this->createStub(AddRepositoryPrivateMetadataFactory::class);

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
                Modal::ADD_REPOSITORY,
                'Cancel',
                'Save',
            )
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $factory = new AddRepositoryModalFactory(
            $modalFactory,
            $inputsFactory,
            $privateMetadataFactory,
            [$optionsResolver],
        );

        $result = $factory->create($interaction);

        $this->assertSame($modal, $result);
    }
}
