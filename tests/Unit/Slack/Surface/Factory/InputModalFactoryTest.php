<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory;

use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;
use App\Slack\Surface\Factory\PrivateMetadata\PrivateMetadataFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InputModalFactory::class)]
class InputModalFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldLogErrorIfInputsFactoryNotSet(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->withAnyParameters();

        $logger->expects($this->once())
            ->method('error')
            ->with('Failed to create add-repository modal. Inputs factory not set.');

        $factory = new InputModalFactory($logger);

        $result = $factory->create(
            $this->createStub(UserTriggeredInteractionInterface::class),
            Modal::ADD_REPOSITORY,
            'externalId',
        );

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldLogErrorIfJsonEncodingExceptionThrown(): void
    {
        $inputsFactory = $this->createMock(ModalInputsFactory::class);
        $inputsFactory->expects($this->once())
            ->method('create')
            ->with($modal = Modal::ADD_REPOSITORY)
            ->willReturn([]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('debug')
            ->withAnyParameters();

        $metadataFactory = $this->createMock(PrivateMetadataFactoryInterface::class);
        $metadataFactory->expects($this->once())
            ->method('create')
            ->willThrowException(new JsonEncodingException($message = 'message'));

        $logger->expects($this->once())
            ->method('error')
            ->with($message);

        $factory = new InputModalFactory($logger);

        $interaction = $this->createMock(UserTriggeredInteractionInterface::class);
        $interaction->expects($this->once())
            ->method('getTriggerId')
            ->willReturn($triggerId = 'triggerId');

        $result = $factory->setInputsFactory($inputsFactory)
            ->setPrivateMetadataFactory($metadataFactory)
            ->create(
                $interaction,
                $modal,
                'externalId',
            );

        $this->assertInstanceOf(ModalSurface::class, $result);

        $result = $result->toArray();

        $this->assertArrayHasKey('trigger_id', $result);
        $this->assertEquals($triggerId, $result['trigger_id']);

        $this->assertArrayHasKey('view', $result);
        $this->assertIsString($result['view']);

        $view = json_decode($result['view'], true);

        $this->assertIsArray($view);
    }
}
