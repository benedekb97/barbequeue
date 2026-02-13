<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Validator\Argument;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\SubCommand;
use App\Slack\Command\Validator\Argument\ArgumentCountValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ArgumentCountValidator::class)]
class ArgumentCountValidatorTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowInvalidArgumentCountExceptionIfArgumentCountIsLessThanExpected(): void
    {
        $validator = new ArgumentCountValidator();

        $this->expectException(InvalidArgumentCountException::class);

        $command = Command::BBQ;
        $subCommand = SubCommand::JOIN;

        try {
            $validator->validate($command, $subCommand, []);
        } catch (InvalidArgumentCountException $exception) {
            $this->assertSame($command, $exception->getCommand());
            $this->assertSame($subCommand, $exception->getSubCommand());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldNotThrowExceptionIfArgumentCountIsGreaterThanOrEqualToExpected(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new ArgumentCountValidator();

        $command = Command::BBQ;
        $subCommand = SubCommand::JOIN;

        $validator->validate($command, $subCommand, ['queue' => 'queueName']);
    }
}
