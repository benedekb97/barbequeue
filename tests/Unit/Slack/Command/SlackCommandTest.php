<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command;

use App\Entity\Administrator;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackCommand::class)]
class SlackCommandTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedProperties(): void
    {
        $administrator = $this->createStub(Administrator::class);

        $slackCommand = new SlackCommand(
            $command = Command::BBQ,
            $arguments = [
                ($argument = CommandArgument::QUEUE)->value => $value = 'value',
                ($timeArgument = CommandArgument::TIME)->value => $time = 30,
            ],
            $teamId = 'teamId',
            $userId = 'userId',
            $userName = 'userName',
            $responseUrl = 'responseUrl',
            $triggerId = 'triggerId',
            $subCommand = SubCommand::JOIN,
        );

        $slackCommand->setAdministrator($administrator);

        $response = $this->createStub(SlackInteractionResponse::class);

        $slackCommand->setResponse($response);

        $this->assertSame($command, $slackCommand->getCommand());
        $this->assertSame($arguments, $slackCommand->getArguments());
        $this->assertSame($teamId, $slackCommand->getTeamId());
        $this->assertSame($userId, $slackCommand->getUserId());
        $this->assertSame($userName, $slackCommand->getUserName());
        $this->assertSame($responseUrl, $slackCommand->getResponseUrl());
        $this->assertSame($triggerId, $slackCommand->getTriggerId());
        $this->assertSame($subCommand, $slackCommand->getSubCommand());
        $this->assertSame($value, $slackCommand->getArgumentString($argument));
        $this->assertSame($time, $slackCommand->getOptionalArgumentInteger($timeArgument));
        $this->assertNull($slackCommand->getOptionalArgumentString(CommandArgument::REPOSITORY));
        $this->assertsame($value, $slackCommand->getOptionalArgumentString($argument));
        $this->assertSame($response, $slackCommand->getResponse());
        $this->assertSame($administrator, $slackCommand->getAdministrator());
    }
}
