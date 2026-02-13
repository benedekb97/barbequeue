<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Modal;

use App\Entity\Repository;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\Modal\EditRepositoryModalFactory;
use App\Slack\Surface\Factory\PrivateMetadata\EditRepositoryPrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\Repository\AbstractRepositoryDefaultValueResolver;
use App\Slack\Surface\Factory\Resolver\Repository\AbstractRepositoryOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(EditRepositoryModalFactory::class)]
class EditRepositoryModalFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateModalSurface(): void
    {
        $repository = $this->createStub(Repository::class);

        $interaction = $this->createMock(UserTriggeredInteractionInterface::class);
        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $defaultValueResolver = $this->createMock(AbstractRepositoryDefaultValueResolver::class);
        $defaultValueResolver->expects($this->once())
            ->method('setRepository')
            ->with($repository)
            ->willReturnSelf();

        $optionsResolver = $this->createMock(AbstractRepositoryOptionsResolver::class);
        $optionsResolver->expects($this->once())
            ->method('setTeamId')
            ->with($teamId)
            ->willReturnSelf();

        $privateMetadataFactory = $this->createMock(EditRepositoryPrivateMetadataFactory::class);
        $privateMetadataFactory->expects($this->once())
            ->method('setRepository')
            ->with($repository)
            ->willReturnSelf();

        $inputsFactory = $this->createMock(ModalInputsFactory::class);
        $inputsFactory->expects($this->once())
            ->method('setDefaultValueResolvers')
            ->with([$defaultValueResolver])
            ->willReturnSelf();

        $inputsFactory->expects($this->once())
            ->method('setOptionsResolvers')
            ->with([$optionsResolver])
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
                Modal::EDIT_REPOSITORY,
                'Cancel',
                'Save',
            )
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $factory = new EditRepositoryModalFactory(
            $modalFactory,
            $inputsFactory,
            $privateMetadataFactory,
            [$defaultValueResolver],
            [$optionsResolver],
        );

        $result = $factory->create($repository, $interaction);

        $this->assertSame($modal, $result);
    }
}
