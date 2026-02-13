<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Inputs;

use App\Slack\Block\Component\InputBlock;
use App\Slack\BlockElement\Component\SlackBlockElement;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Exception\NoOptionsAvailableException;
use App\Slack\Surface\Factory\InputElementFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ModalInputsFactory::class)]
class ModalInputsFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateAnArrayOfInputAndDividerBlocks(): void
    {
        $callCount = 0;

        $firstElement = $this->createMock(SlackBlockElement::class);
        $firstElement->expects($this->once())
            ->method('toArray')
            ->willReturn(['firstElement']);

        $secondElement = $this->createMock(SlackBlockElement::class);
        $secondElement->expects($this->once())
            ->method('toArray')
            ->willReturn(['secondElement']);

        $inputElementFactory = $this->createMock(InputElementFactory::class);
        $inputElementFactory->expects($this->exactly(3))
            ->method('create')
            ->willReturnCallback(function ($argument) use (&$callCount, $firstElement, $secondElement) {
                $this->assertInstanceOf(ModalArgument::class, $argument);

                if (1 === ++$callCount) {
                    $this->assertEquals(ModalArgument::REPOSITORY_NAME, $argument);

                    return $firstElement;
                }

                if (2 === $callCount) {
                    $this->assertEquals(ModalArgument::REPOSITORY_URL, $argument);

                    return $secondElement;
                }

                $this->assertEquals(ModalArgument::REPOSITORY_BLOCKS, $argument);

                throw new NoOptionsAvailableException();
            });

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(3))
            ->method('debug')
            ->withAnyParameters();

        $optionsResolver = $this->createMock(OptionsResolverInterface::class);
        $optionsResolver->expects($this->exactly(3))
            ->method('getSupportedArgument')
            ->willReturn(ModalArgument::REPOSITORY_BLOCKS);

        $defaultValueResolver = $this->createMock(DefaultValueResolverInterface::class);
        $defaultValueResolver->expects($this->exactly(3))
            ->method('getSupportedArgument')
            ->willReturn(ModalArgument::REPOSITORY_BLOCKS);

        $factory = new ModalInputsFactory($inputElementFactory, $logger)
            ->setOptionsResolvers([$optionsResolver])
            ->setDefaultValueResolvers([$defaultValueResolver]);

        $result = $factory->create(Modal::ADD_REPOSITORY);

        $this->assertCount(4, $result);

        $this->assertInstanceOf(InputBlock::class, $result[0]);

        $this->assertInputBlockCorrectlyFormatted(
            $result[0]->toArray(),
            'What is the repository called?',
            ['firstElement']
        );

        $this->assertDividerBlockCorrectlyFormatted($result[1]->toArray());

        $this->assertInputBlockCorrectlyFormatted(
            $result[2]->toArray(),
            'Where can the repository be found?',
            ['secondElement'],
            expectedHint: 'This will be displayed on development or environment queue entries',
            expectedOptional: true,
        );

        $this->assertDividerBlockCorrectlyFormatted($result[3]->toArray());
    }
}
