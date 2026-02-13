<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Exception;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\SubCommand;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InvalidArgumentCountException::class)]
class InvalidArgumentCountExceptionTest extends KernelTestCase
{
    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    #[Test]
    public function itShouldReturnPassedProperties(): void
    {
        $command = Command::BBQ;
        $subCommand = $this->faker->boolean() ? SubCommand::LEAVE : null;

        $exception = new InvalidArgumentCountException($command, $subCommand);

        $this->assertEquals(
            'Invalid number of arguments provided for command '.$command->value.' '.$subCommand?->value,
            $exception->getMessage()
        );

        $this->assertSame($command, $exception->getCommand());
        $this->assertSame($subCommand, $exception->getSubCommand());
    }
}
