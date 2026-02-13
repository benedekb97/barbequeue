<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Resolver;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\Resolver\SubCommandResolver;
use App\Slack\Command\SubCommand;
use App\Slack\Command\Validator\SubCommandRequirementValidator;
use App\Slack\Command\Validator\SubCommandValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SubCommandResolver::class)]
class SubCommandResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnIfCommandDoesNotHaveSubCommand(): void
    {
        $requirementValidator = $this->createMock(SubCommandRequirementValidator::class);
        $requirementValidator->expects($this->never())
            ->method('validate')
            ->withAnyParameters();

        $validator = $this->createMock(SubCommandValidator::class);
        $validator->expects($this->never())
            ->method('validate')
            ->withAnyParameters();

        $resolver = new SubCommandResolver($requirementValidator, $validator);

        $result = $resolver->resolve(Command::TEST, new Request());

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldThrowSubCommandMissingExceptionIfSubCommandNotProvided(): void
    {
        $request = new Request();
        $exception = $this->createStub(SubCommandMissingException::class);

        $requirementValidator = $this->createMock(SubCommandRequirementValidator::class);
        $requirementValidator->expects($this->once())
            ->method('validate')
            ->with($command = Command::BBQ, '')
            ->willThrowException($exception);

        $validator = $this->createMock(SubCommandValidator::class);
        $validator->expects($this->never())
            ->method('validate')
            ->withAnyParameters();

        $this->expectExceptionObject($exception);

        $resolver = new SubCommandResolver($requirementValidator, $validator);

        $resolver->resolve($command, $request);
    }

    #[Test]
    public function itShouldReturnNullIfSubCommandNotRequiredAndSubCommandTextEmpty(): void
    {
        $request = new Request();

        $requirementValidator = $this->createMock(SubCommandRequirementValidator::class);
        $requirementValidator->expects($this->once())
            ->method('validate')
            ->with($command = Command::BBQ, '');

        $validator = $this->createMock(SubCommandValidator::class);
        $validator->expects($this->never())
            ->method('validate')
            ->withAnyParameters();

        $resolver = new SubCommandResolver($requirementValidator, $validator);

        $result = $resolver->resolve($command, $request);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldThrowInvalidSubCommandExceptionIfSubCommandCouldNotBeResolved(): void
    {
        $request = new Request();
        $request->request->set('text', $subCommandText = 'invalid-subcommand');

        $exception = $this->createStub(InvalidSubCommandException::class);

        $requirementValidator = $this->createMock(SubCommandRequirementValidator::class);
        $requirementValidator->expects($this->once())
            ->method('validate')
            ->with($command = Command::BBQ, $subCommandText);

        $validator = $this->createMock(SubCommandValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($command, null, $subCommandText)
            ->willThrowException($exception);

        $resolver = new SubCommandResolver($requirementValidator, $validator);

        $this->expectExceptionObject($exception);

        $resolver->resolve($command, $request);
    }

    #[Test]
    public function itShouldResolveSubCommand(): void
    {
        $request = new Request();
        $request->request->set('text', $subCommandText = ($subCommand = SubCommand::JOIN)->value);

        $requirementValidator = $this->createMock(SubCommandRequirementValidator::class);
        $requirementValidator->expects($this->once())
            ->method('validate')
            ->with($command = Command::BBQ, $subCommandText);

        $validator = $this->createMock(SubCommandValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($command, $subCommand, $subCommandText);

        $resolver = new SubCommandResolver($requirementValidator, $validator);

        $result = $resolver->resolve($command, $request);

        $this->assertEquals($subCommand, $result);
    }
}
