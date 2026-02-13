<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Factory;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\Exception\InvalidCommandException;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\Factory\SlackCommandFactory;
use App\Slack\Command\Resolver\ArgumentsResolver;
use App\Slack\Command\Resolver\CommandResolver;
use App\Slack\Command\Resolver\SubCommandResolver;
use App\Slack\Command\SubCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SlackCommandFactory::class)]
class SlackCommandFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowInvalidCommandExceptionIfCommandCannotBeResolved(): void
    {
        $request = $this->createStub(Request::class);

        $exception = $this->createStub(InvalidCommandException::class);

        $commandResolver = $this->createMock(CommandResolver::class);
        $commandResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $factory = new SlackCommandFactory(
            $commandResolver,
            $this->createStub(SubCommandResolver::class),
            $this->createStub(ArgumentsResolver::class),
        );

        $factory->createFromRequest($request);
    }

    /** @param class-string<InvalidSubCommandException|SubCommandMissingException> $exceptionType */
    #[Test, DataProvider('provideSubCommandResolverExceptions')]
    public function itShouldThrowExceptionsIfSubCommandCannotBeResolved(string $exceptionType): void
    {
        $request = $this->createStub(Request::class);

        $commandResolver = $this->createMock(CommandResolver::class);
        $commandResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($command = Command::BBQ);

        $exception = $this->createStub($exceptionType);

        $subCommandResolver = $this->createMock(SubCommandResolver::class);
        $subCommandResolver->expects($this->once())
            ->method('resolve')
            ->with($command, $request)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $factory = new SlackCommandFactory(
            $commandResolver,
            $subCommandResolver,
            $this->createStub(ArgumentsResolver::class),
        );

        $factory->createFromRequest($request);
    }

    public static function provideSubCommandResolverExceptions(): array
    {
        return [
            [InvalidSubCommandException::class],
            [SubCommandMissingException::class],
        ];
    }

    #[Test]
    public function itShouldThrowInvalidArgumentCountExceptionIfArgumentResolutionFails(): void
    {
        $request = $this->createStub(Request::class);

        $exception = $this->createStub(InvalidArgumentCountException::class);

        $commandResolver = $this->createMock(CommandResolver::class);
        $commandResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($command = Command::BBQ_ADMIN);

        $subCommandResolver = $this->createMock(SubCommandResolver::class);
        $subCommandResolver->expects($this->once())
            ->method('resolve')
            ->with($command, $request)
            ->willReturn($subCommand = SubCommand::EDIT_QUEUE);

        $argumentsResolver = $this->createMock(ArgumentsResolver::class);
        $argumentsResolver->expects($this->once())
            ->method('resolve')
            ->with($command, $subCommand, $request)
            ->willThrowException($exception);

        $factory = new SlackCommandFactory(
            $commandResolver,
            $subCommandResolver,
            $argumentsResolver,
        );

        $this->expectExceptionObject($exception);

        $factory->createFromRequest($request);
    }

    #[Test]
    public function itShouldCreateSlackCommand(): void
    {
        $request = new Request();
        $request->request->set('team_id', $teamId = 'teamId');
        $request->request->set('user_id', $userId = 'userId');
        $request->request->set('user_name', $userName = 'userName');
        $request->request->set('trigger_id', $triggerId = 'triggerId');
        $request->request->set('response_url', $responseUrl = 'responseUrl');

        $commandResolver = $this->createMock(CommandResolver::class);
        $commandResolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($command = Command::BBQ_ADMIN);

        $subCommandResolver = $this->createMock(SubCommandResolver::class);
        $subCommandResolver->expects($this->once())
            ->method('resolve')
            ->with($command, $request)
            ->willReturn($subCommand = SubCommand::EDIT_QUEUE);

        $argumentsResolver = $this->createMock(ArgumentsResolver::class);
        $argumentsResolver->expects($this->once())
            ->method('resolve')
            ->with($command, $subCommand, $request)
            ->willReturn($arguments = ['arguments']);

        $factory = new SlackCommandFactory(
            $commandResolver,
            $subCommandResolver,
            $argumentsResolver,
        );

        $slackCommand = $factory->createFromRequest($request);

        $this->assertEquals($command, $slackCommand->getCommand());
        $this->assertEquals($subCommand, $slackCommand->getSubCommand());
        $this->assertEquals($responseUrl, $slackCommand->getResponseUrl());
        $this->assertEquals($teamId, $slackCommand->getTeamId());
        $this->assertEquals($userId, $slackCommand->getUserId());
        $this->assertEquals($userName, $slackCommand->getUserName());
        $this->assertEquals($triggerId, $slackCommand->getTriggerId());
        $this->assertEquals($arguments, $slackCommand->getArguments());
    }
}
