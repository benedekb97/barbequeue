<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Queue;

use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Enum\Queue as QueueEnum;
use App\Enum\QueueBehaviour;
use App\Form\Queue\QueueType;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

#[CoversClass(QueueType::class)]
class QueueTypeTest extends KernelTestCase
{
    #[Test]
    public function itShouldBuildQueueFormForSimpleQueue(): void
    {
        $type = new QueueType();

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (string $type, string $class, array $options) use ($builder) {
                $this->assertEquals('type', $type);
                $this->assertEquals(EnumType::class, $class);
                $this->assertCount(3, $options);

                $this->assertArrayHasKey('class', $options);
                $this->assertEquals(QueueEnum::class, $options['class']);
                $this->assertArrayHasKey('constraints', $options);
                $this->assertIsArray($options['constraints']);
                $this->assertCount(1, $options['constraints']);
                $this->assertInstanceOf(NotNull::class, $options['constraints'][0]);
                $this->assertArrayHasKey('mapped', $options);
                $this->assertFalse($options['mapped']);

                return $builder;
            });

        $event = new PreSubmitEvent(
            $form = $this->createMock(FormInterface::class),
            ['type' => 'simple'],
        );

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($queue = $this->createMock(Queue::class));

        $queue->expects($this->once())
            ->method('getType')
            ->willReturn(QueueEnum::SIMPLE);

        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($this->createStub(Workspace::class));

        $callCount = 0;
        $form->expects($this->exactly(3))
            ->method('add')
            ->willReturnCallback(function ($type, $class, array $options) use ($form, &$callCount) {
                if (1 === ++$callCount) {
                    $this->assertEquals('name', $type);
                    $this->assertEquals(TextType::class, $class);

                    $this->assertCount(1, $options);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertInstanceOf(NotBlank::class, $options['constraints'][0]);
                }

                if (2 === $callCount) {
                    $this->assertEquals('expiryMinutes', $type);
                    $this->assertEquals(IntegerType::class, $class);

                    $this->assertCount(1, $options);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertInstanceOf(Positive::class, $options['constraints'][0]);
                }

                if (3 === $callCount) {
                    $this->assertEquals('maximumEntriesPerUser', $type);
                    $this->assertEquals(IntegerType::class, $class);

                    $this->assertCount(1, $options);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertInstanceOf(Positive::class, $options['constraints'][0]);
                }

                return $form;
            });

        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $callback) use ($event, $builder) {
                $this->assertEquals(FormEvents::PRE_SUBMIT, $eventName);

                $callback($event);

                return $builder;
            });

        $type->buildForm($builder, []);
    }

    #[Test]
    public function itShouldAddErrorIfTypeNotSetOnData(): void
    {
        $type = new QueueType();

        $event = new PreSubmitEvent(
            $form = $this->createMock(FormInterface::class),
            [],
        );

        $form->expects($this->once())
            ->method('addError')
            ->willReturnCallback(function ($error) use ($form) {
                $this->assertInstanceOf(FormError::class, $error);
                $this->assertEquals('type is required', $error->getMessage());

                return $form;
            });

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (string $type, string $class, array $options) use ($builder) {
                $this->assertEquals('type', $type);
                $this->assertEquals(EnumType::class, $class);
                $this->assertCount(3, $options);

                $this->assertArrayHasKey('class', $options);
                $this->assertEquals(QueueEnum::class, $options['class']);
                $this->assertArrayHasKey('constraints', $options);
                $this->assertIsArray($options['constraints']);
                $this->assertCount(1, $options['constraints']);
                $this->assertInstanceOf(NotNull::class, $options['constraints'][0]);
                $this->assertArrayHasKey('mapped', $options);
                $this->assertFalse($options['mapped']);

                return $builder;
            });

        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $callback) use ($event, $builder) {
                $this->assertEquals(FormEvents::PRE_SUBMIT, $eventName);

                $callback($event);

                return $builder;
            });

        $type->buildForm($builder, []);
    }

    #[Test]
    public function itShouldAddErrorIfQueueTypeDifferentThanDataType(): void
    {
        $type = new QueueType();

        $event = new PreSubmitEvent(
            $form = $this->createMock(FormInterface::class),
            ['type' => 'deployment'],
        );

        $form->expects($this->once())
            ->method('addError')
            ->willReturnCallback(function ($error) use ($form) {
                $this->assertInstanceOf(FormError::class, $error);
                $this->assertEquals('Changing queue types is not yet supported.', $error->getMessage());

                return $form;
            });

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($queue = $this->createMock(Queue::class));

        $queue->expects($this->once())
            ->method('getType')
            ->willReturn(QueueEnum::SIMPLE);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (string $type, string $class, array $options) use ($builder) {
                $this->assertEquals('type', $type);
                $this->assertEquals(EnumType::class, $class);
                $this->assertCount(3, $options);

                $this->assertArrayHasKey('class', $options);
                $this->assertEquals(QueueEnum::class, $options['class']);
                $this->assertArrayHasKey('constraints', $options);
                $this->assertIsArray($options['constraints']);
                $this->assertCount(1, $options['constraints']);
                $this->assertInstanceOf(NotNull::class, $options['constraints'][0]);
                $this->assertArrayHasKey('mapped', $options);
                $this->assertFalse($options['mapped']);

                return $builder;
            });

        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $callback) use ($event, $builder) {
                $this->assertEquals(FormEvents::PRE_SUBMIT, $eventName);

                $callback($event);

                return $builder;
            });

        $type->buildForm($builder, []);
    }

    #[Test]
    public function itShouldBuildDeploymentQueueForm(): void
    {
        $type = new QueueType();

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (string $type, string $class, array $options) use ($builder) {
                $this->assertEquals('type', $type);
                $this->assertEquals(EnumType::class, $class);
                $this->assertCount(3, $options);

                $this->assertArrayHasKey('class', $options);
                $this->assertEquals(QueueEnum::class, $options['class']);
                $this->assertArrayHasKey('constraints', $options);
                $this->assertIsArray($options['constraints']);
                $this->assertCount(1, $options['constraints']);
                $this->assertInstanceOf(NotNull::class, $options['constraints'][0]);
                $this->assertArrayHasKey('mapped', $options);
                $this->assertFalse($options['mapped']);

                return $builder;
            });

        $event = new PreSubmitEvent(
            $form = $this->createMock(FormInterface::class),
            ['type' => 'deployment'],
        );

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($queue = $this->createMock(Queue::class));

        $queue->expects($this->once())
            ->method('getType')
            ->willReturn(QueueEnum::DEPLOYMENT);

        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createMock(Workspace::class));

        $workspace->expects($this->once())
            ->method('getRepositories')
            ->willReturn($collection = $this->createStub(Collection::class));

        $callCount = 0;
        $form->expects($this->exactly(5))
            ->method('add')
            ->willReturnCallback(function ($type, $class, array $options) use ($form, &$callCount, $collection) {
                if (1 === ++$callCount) {
                    $this->assertEquals('name', $type);
                    $this->assertEquals(TextType::class, $class);

                    $this->assertCount(1, $options);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertInstanceOf(NotBlank::class, $options['constraints'][0]);
                }

                if (2 === $callCount) {
                    $this->assertEquals('expiryMinutes', $type);
                    $this->assertEquals(IntegerType::class, $class);

                    $this->assertCount(1, $options);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertInstanceOf(Positive::class, $options['constraints'][0]);
                }

                if (3 === $callCount) {
                    $this->assertEquals('maximumEntriesPerUser', $type);
                    $this->assertEquals(IntegerType::class, $class);

                    $this->assertCount(1, $options);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertInstanceOf(Positive::class, $options['constraints'][0]);
                }

                if (4 === $callCount) {
                    $this->assertEquals('behaviour', $type);
                    $this->assertEquals(EnumType::class, $class);

                    $this->assertCount(2, $options);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertInstanceOf(NotNull::class, $options['constraints'][0]);

                    $this->assertArrayHasKey('class', $options);
                    $this->assertEquals(QueueBehaviour::class, $options['class']);
                }

                if (5 === $callCount) {
                    $this->assertEquals('repositories', $type);
                    $this->assertEquals(EntityType::class, $class);

                    $this->assertCount(3, $options);

                    $this->assertArrayHasKey('choices', $options);
                    $this->assertEquals($collection, $options['choices']);

                    $this->assertArrayHasKey('multiple', $options);
                    $this->assertTrue($options['multiple']);

                    $this->assertArrayHasKey('class', $options);
                    $this->assertEquals(Repository::class, $options['class']);
                }

                return $form;
            });

        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $callback) use ($event, $builder) {
                $this->assertEquals(FormEvents::PRE_SUBMIT, $eventName);

                $callback($event);

                return $builder;
            });

        $type->buildForm($builder, []);
    }
}
