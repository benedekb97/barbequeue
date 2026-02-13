<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Factory;

use App\Slack\BlockElement\Component\NumberInputElement;
use App\Slack\BlockElement\Component\PlainTextInputElement;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\InputElementFactory;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InputElementFactory::class)]
class InputElementFactoryTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldCreateNumberInputElement(): void
    {
        $factory = new InputElementFactory();

        $defaultValueResolver = $this->createMock(DefaultValueResolverInterface::class);
        $defaultValueResolver->expects($this->once())
            ->method('resolveString')
            ->willReturn($defaultValue = '1');

        $factory->setDefaultValueResolver($defaultValueResolver);

        $result = $factory->create($argument = ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER);

        $this->assertInstanceOf(NumberInputElement::class, $result);

        $this->assertNumberBlockElementCorrectlyFormatted(
            false,
            $result->toArray(),
            expectedActionId: $argument->value,
            expectedInitialValue: $defaultValue,
            expectedPlaceholder: $argument->getPlaceholder(),
        );
    }

    #[Test]
    public function itShouldCreatePlainTextInputElement(): void
    {
        $factory = new InputElementFactory();

        $defaultValueResolver = $this->createMock(DefaultValueResolverInterface::class);
        $defaultValueResolver->expects($this->once())
            ->method('resolveString')
            ->willReturn($defaultValue = 'repositoryName');

        $factory->setDefaultValueResolver($defaultValueResolver);

        $result = $factory->create(
            $argument = ModalArgument::REPOSITORY_NAME,
        );

        $this->assertInstanceOf(PlainTextInputElement::class, $result);

        $this->assertPlainTextBlockElementCorrectlyFormatted(
            $result->toArray(),
            expectedActionId: $argument->value,
            expectedInitialValue: $defaultValue,
            expectedPlaceholder: $argument->getPlaceholder(),
        );
    }

    #[Test]
    public function itShouldCreateMultiStaticSelectElement(): void
    {
        $factory = new InputElementFactory();

        $defaultValueResolver = $this->createMock(DefaultValueResolverInterface::class);
        $defaultValueResolver->expects($this->once())
            ->method('resolveArray')
            ->willReturn($defaultValue = ['defaultValue']);

        $optionsResolver = $this->createMock(OptionsResolverInterface::class);
        $optionsResolver->expects($this->once())
            ->method('resolve')
            ->willReturn($options = ['options']);

        $factory->setDefaultValueResolver($defaultValueResolver)
            ->setOptionsResolver($optionsResolver);

        $result = $factory->create($argument = ModalArgument::REPOSITORY_BLOCKS);

        $this->assertMultiStaticSelectElementCorrectlyFormatted(
            $result->toArray(),
            $argument->value,
            $argument->getPlaceholder(),
            expectedOptions: $options,
            expectedInitialOptions: $defaultValue,
        );
    }

    #[Test]
    public function itShouldCreateStaticSelectElement(): void
    {
        $factory = new InputElementFactory();

        $defaultValueResolver = $this->createMock(DefaultValueResolverInterface::class);
        $defaultValueResolver->expects($this->once())
            ->method('resolveArray')
            ->willReturn($defaultValue = ['defaultValue']);

        $optionsResolver = $this->createMock(OptionsResolverInterface::class);
        $optionsResolver->expects($this->once())
            ->method('resolve')
            ->willReturn($options = ['options']);

        $factory->setDefaultValueResolver($defaultValueResolver)
            ->setOptionsResolver($optionsResolver);

        $result = $factory->create($argument = ModalArgument::DEPLOYMENT_REPOSITORY);

        $this->assertStaticSelectElementCorrectlyFormatted(
            $result->toArray(),
            $argument->value,
            $argument->getPlaceholder(),
            expectedOptions: $options,
            expectedInitialOption: $defaultValue,
        );
    }

    #[Test]
    public function itShouldCreateMultiUsersSelectElement(): void
    {
        $factory = new InputElementFactory();

        $result = $factory->create($argument = ModalArgument::DEPLOYMENT_NOTIFY_USERS);

        $this->assertMultiUsersSelectElementCorrectlyFormatted(
            $result->toArray(),
            $argument->value,
            $argument->getPlaceholder(),
        );
    }

    #[Test]
    public function itShouldCreateUrlInputElement(): void
    {
        $factory = new InputElementFactory();

        $result = $factory->create($argument = ModalArgument::DEPLOYMENT_LINK);

        $this->assertUrlElementCorrectlyFormatted(
            $result->toArray(),
            $argument->value,
            expectedPlaceholder: $argument->getPlaceholder(),
        );
    }
}
