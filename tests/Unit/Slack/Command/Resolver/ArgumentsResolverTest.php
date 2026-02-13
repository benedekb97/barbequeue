<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Resolver;

use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\Exception\InvalidArgumentException;
use App\Slack\Command\Resolver\ArgumentsResolver;
use App\Slack\Command\SubCommand;
use App\Slack\Command\Validator\Argument\ArgumentCountValidator;
use App\Slack\Command\Validator\Argument\ArgumentValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(ArgumentsResolver::class)]
class ArgumentsResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowInvalidArgumentCountExceptionIfNumberOfArgumentsRetrievedIsLessThanRequired(): void
    {
        $request = new Request();
        $request->request->set('text', 'leave');

        $command = Command::BBQ;
        $subCommand = SubCommand::LEAVE;

        $exception = $this->createStub(InvalidArgumentCountException::class);

        $argumentCountValidator = $this->createMock(ArgumentCountValidator::class);
        $argumentCountValidator->expects($this->once())
            ->method('validate')
            ->with($command, $subCommand, [])
            ->willThrowException($exception);

        $argumentValidator = $this->createMock(ArgumentValidatorInterface::class);
        $argumentValidator->expects($this->once())
            ->method('supports')
            ->withAnyParameters()
            ->willReturn(false);

        $resolver = new ArgumentsResolver(
            $argumentCountValidator,
            [$argumentValidator],
        );

        $this->expectExceptionObject($exception);

        $resolver->resolve($command, $subCommand, $request);
    }

    #[Test]
    public function itShouldThrowInvalidArgumentCountExceptionIfArgumentFailsToValidate(): void
    {
        $request = new Request();
        $request->request->set('text', 'leave queueName');

        $command = Command::BBQ;
        $subCommand = SubCommand::LEAVE;

        $exception = $this->createStub(InvalidArgumentCountException::class);

        $argumentCountValidator = $this->createMock(ArgumentCountValidator::class);
        $argumentCountValidator->expects($this->once())
            ->method('validate')
            ->with($command, $subCommand, [])
            ->willThrowException($exception);

        $argumentValidator = $this->createMock(ArgumentValidatorInterface::class);
        $argumentValidator->expects($this->once())
            ->method('supports')
            ->with(CommandArgument::QUEUE)
            ->willReturn(true);

        $argumentValidator->expects($this->once())
            ->method('validate')
            ->with(CommandArgument::QUEUE, 'queueName')
            ->willThrowException($this->createStub(InvalidArgumentException::class));

        $resolver = new ArgumentsResolver(
            $argumentCountValidator,
            [$argumentValidator],
        );

        $this->expectExceptionObject($exception);

        $resolver->resolve($command, $subCommand, $request);
    }

    #[Test]
    public function itShouldResolveArgumentsFromRequest(): void
    {
        $request = new Request();
        $request->request->set('text', 'leave queueName');

        $command = Command::BBQ;
        $subCommand = SubCommand::LEAVE;

        $argumentCountValidator = $this->createMock(ArgumentCountValidator::class);
        $argumentCountValidator->expects($this->once())
            ->method('validate')
            ->with($command, $subCommand, $arguments = ['queue' => 'queueName']);

        $argumentValidator = $this->createMock(ArgumentValidatorInterface::class);
        $argumentValidator->expects($this->once())
            ->method('supports')
            ->with(CommandArgument::QUEUE)
            ->willReturn(false);

        $resolver = new ArgumentsResolver(
            $argumentCountValidator,
            [$argumentValidator],
        );

        $result = $resolver->resolve($command, $subCommand, $request);

        $this->assertEquals($arguments, $result);
    }

    #[Test]
    public function itShouldResolveValidatedArgumentsFromRequest(): void
    {
        $request = new Request();
        $request->request->set('text', 'add-user <@UABCDEFGHIJ|email@example.com>');

        $command = Command::BBQ_ADMIN;
        $subCommand = SubCommand::ADD_USER;

        $argumentCountValidator = $this->createMock(ArgumentCountValidator::class);
        $argumentCountValidator->expects($this->once())
            ->method('validate')
            ->with($command, $subCommand, $arguments = ['user' => 'UABCDEFGHIJ']);

        $argumentValidator = $this->createMock(ArgumentValidatorInterface::class);
        $argumentValidator->expects($this->once())
            ->method('supports')
            ->with(CommandArgument::USER)
            ->willReturn(true);

        $argumentValidator->expects($this->once())
            ->method('validate')
            ->with(CommandArgument::USER, '<@UABCDEFGHIJ|email@example.com>')
            ->willReturnCallback(fn ($argument, $argumentValue) => 'UABCDEFGHIJ');

        $resolver = new ArgumentsResolver(
            $argumentCountValidator,
            [$argumentValidator],
        );

        $result = $resolver->resolve($command, $subCommand, $request);

        $this->assertEquals($arguments, $result);
    }

    #[Test]
    public function itShouldPreserveSpacesInLastArgument(): void
    {
        $request = new Request();
        $request->request->set('text', 'edit-repository multiple spaces in repository name');

        $command = Command::BBQ_ADMIN;
        $subCommand = SubCommand::EDIT_REPOSITORY;

        $argumentCountValidator = $this->createMock(ArgumentCountValidator::class);
        $argumentCountValidator->expects($this->once())
            ->method('validate')
            ->with($command, $subCommand, $arguments = ['repository' => 'multiple spaces in repository name']);

        $argumentValidator = $this->createMock(ArgumentValidatorInterface::class);
        $argumentValidator->expects($this->once())
            ->method('supports')
            ->with(CommandArgument::REPOSITORY)
            ->willReturn(false);

        $resolver = new ArgumentsResolver(
            $argumentCountValidator,
            [$argumentValidator],
        );

        $result = $resolver->resolve($command, $subCommand, $request);

        $this->assertEquals($arguments, $result);
    }
}
