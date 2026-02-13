<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraint\Validator;

use App\Entity\Queue;
use App\Entity\User;
use App\Validator\Constraint\CanJoinQueue;
use App\Validator\Constraint\Validator\CanJoinQueueValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[CoversClass(CanJoinQueueValidator::class)]
class CanJoinQueueValidatorTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowUnexpectedValueExceptionIfValueNotUser(): void
    {
        $validator = new CanJoinQueueValidator();

        $this->expectException(UnexpectedValueException::class);

        $validator->validate('', $this->createStub(CanJoinQueue::class));
    }

    #[Test]
    public function itShouldThrowUnexpectedTypeExceptionIfConstraintNotCanJoinQueue(): void
    {
        $validator = new CanJoinQueueValidator();

        $this->expectException(UnexpectedTypeException::class);

        $validator->validate($this->createStub(User::class), $this->createStub(Constraint::class));
    }

    #[Test]
    public function itShouldNotAddViolationIfUserCanJoinQueue(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($slackId = 'slackId');

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canJoin')
            ->with($slackId)
            ->willReturn(true);

        $constraint = new CanJoinQueue($queue, $this->createStub(User::class));

        $validator = new CanJoinQueueValidator();

        $validator->validate($user, $constraint);
    }

    #[Test]
    public function itShouldAddViolationIfQueueMaxEntriesGreaterThanOne(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($slackId = 'slackId');

        $user->expects($this->once())
            ->method('getName')
            ->willReturn($userName = 'name');

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canJoin')
            ->with($slackId)
            ->willReturn(false);

        $queue->expects($this->exactly(2))
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maxEntries = 2);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $constraint = new CanJoinQueue($queue, $this->createStub(User::class));

        $validator = new CanJoinQueueValidator();

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->with('{{ name }} can only join the {{ queue }} queue a maximum of {{ maximumEntriesPerUser }} times.')
            ->willReturn($constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class));

        $callCount = 0;
        $constraintViolationBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(function ($parameter, $value) use (&$callCount, $queueName, $maxEntries, $userName, $constraintViolationBuilder) {
                if (1 === ++$callCount) {
                    $this->assertEquals('{{ name }}', $parameter);
                    $this->assertEquals($userName, $value);
                }

                if (2 === $callCount) {
                    $this->assertEquals('{{ queue }}', $parameter);
                    $this->assertEquals($queueName, $value);
                }

                if (3 === $callCount) {
                    $this->assertEquals('{{ maximumEntriesPerUser }}', $parameter);
                    $this->assertEquals($maxEntries, $value);
                }

                return $constraintViolationBuilder;
            });

        $constraintViolationBuilder->expects($this->once())
            ->method('addViolation');

        $validator->initialize($context);

        $validator->validate($user, $constraint);
    }

    #[Test]
    public function itShouldAddViolationIfQueueMaxEntriesGreaterThanOneForSameUser(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($slackId = 'slackId');

        $user->expects($this->never())
            ->method('getName');

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canJoin')
            ->with($slackId)
            ->willReturn(false);

        $queue->expects($this->exactly(2))
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maxEntries = 2);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $constraint = new CanJoinQueue($queue, $user);

        $validator = new CanJoinQueueValidator();

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->with('{{ name }} can only join the {{ queue }} queue a maximum of {{ maximumEntriesPerUser }} times.')
            ->willReturn($constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class));

        $callCount = 0;
        $constraintViolationBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(function ($parameter, $value) use (&$callCount, $queueName, $maxEntries, $constraintViolationBuilder) {
                if (1 === ++$callCount) {
                    $this->assertEquals('{{ name }}', $parameter);
                    $this->assertEquals('You', $value);
                }

                if (2 === $callCount) {
                    $this->assertEquals('{{ queue }}', $parameter);
                    $this->assertEquals($queueName, $value);
                }

                if (3 === $callCount) {
                    $this->assertEquals('{{ maximumEntriesPerUser }}', $parameter);
                    $this->assertEquals($maxEntries, $value);
                }

                return $constraintViolationBuilder;
            });

        $constraintViolationBuilder->expects($this->once())
            ->method('addViolation');

        $validator->initialize($context);

        $validator->validate($user, $constraint);
    }

    #[Test]
    public function itShouldAddViolationIfQueueMaxEntriesIsOne(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($slackId = 'slackId');

        $user->expects($this->once())
            ->method('getName')
            ->willReturn($userName = 'name');

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canJoin')
            ->with($slackId)
            ->willReturn(false);

        $queue->expects($this->once())
            ->method('getMaximumEntriesPerUser')
            ->willReturn(1);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $constraint = new CanJoinQueue($queue, $this->createStub(User::class));

        $validator = new CanJoinQueueValidator();

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->with('{{ name }} {{ verb }} already in the {{ queue }} queue.')
            ->willReturn($constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class));

        $callCount = 0;
        $constraintViolationBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(function ($parameter, $value) use (&$callCount, $queueName, $userName, $constraintViolationBuilder) {
                if (1 === ++$callCount) {
                    $this->assertEquals('{{ name }}', $parameter);
                    $this->assertEquals($userName, $value);
                }

                if (2 === $callCount) {
                    $this->assertEquals('{{ verb }}', $parameter);
                    $this->assertEquals('is', $value);
                }

                if (3 === $callCount) {
                    $this->assertEquals('{{ queue }}', $parameter);
                    $this->assertEquals($queueName, $value);
                }

                return $constraintViolationBuilder;
            });

        $constraintViolationBuilder->expects($this->once())
            ->method('addViolation');

        $validator->initialize($context);

        $validator->validate($user, $constraint);
    }

    #[Test]
    public function itShouldAddViolationIfQueueMaxEntriesIsOneForSameUser(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($slackId = 'slackId');

        $user->expects($this->never())
            ->method('getName');

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canJoin')
            ->with($slackId)
            ->willReturn(false);

        $queue->expects($this->once())
            ->method('getMaximumEntriesPerUser')
            ->willReturn(1);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $constraint = new CanJoinQueue($queue, $user);

        $validator = new CanJoinQueueValidator();

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->with('{{ name }} {{ verb }} already in the {{ queue }} queue.')
            ->willReturn($constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class));

        $callCount = 0;
        $constraintViolationBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(function ($parameter, $value) use (&$callCount, $queueName, $constraintViolationBuilder) {
                if (1 === ++$callCount) {
                    $this->assertEquals('{{ name }}', $parameter);
                    $this->assertEquals('You', $value);
                }

                if (2 === $callCount) {
                    $this->assertEquals('{{ verb }}', $parameter);
                    $this->assertEquals('are', $value);
                }

                if (3 === $callCount) {
                    $this->assertEquals('{{ queue }}', $parameter);
                    $this->assertEquals($queueName, $value);
                }

                return $constraintViolationBuilder;
            });

        $constraintViolationBuilder->expects($this->once())
            ->method('addViolation');

        $validator->initialize($context);

        $validator->validate($user, $constraint);
    }
}
