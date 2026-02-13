<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Validator\Argument;

use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidArgumentException;
use App\Slack\Command\Validator\Argument\TimeArgumentValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(TimeArgumentValidator::class)]
class TimeArgumentValidatorTest extends KernelTestCase
{
    #[Test, DataProvider('provideUnsupportedArguments')]
    public function itShouldNotSupportOtherCommandArguments(CommandArgument $argument): void
    {
        $validator = new TimeArgumentValidator();

        $this->assertFalse($validator->supports($argument));
    }

    public static function provideUnsupportedArguments(): array
    {
        return [
            [CommandArgument::USER],
            [CommandArgument::REPOSITORY],
            [CommandArgument::QUEUE],
        ];
    }

    #[Test]
    public function itShouldSupportTimeCommandArgument(): void
    {
        $validator = new TimeArgumentValidator();

        $this->assertTrue($validator->supports(CommandArgument::TIME));
    }

    #[Test]
    public function itShouldThrowInvalidArgumentExceptionIfNullValueProvided(): void
    {
        $validator = new TimeArgumentValidator();

        $this->expectException(InvalidArgumentException::class);

        $validator->validate(CommandArgument::TIME, null);
    }

    #[Test]
    public function itShouldReturnIntegerIfNumericValueProvided(): void
    {
        $validator = new TimeArgumentValidator();

        $result = $validator->validate(CommandArgument::TIME, '20');

        $this->assertEquals(20, $result);
    }

    #[Test]
    public function itShouldThrowInvalidArgumentExceptionIfNumericZeroValueProvided(): void
    {
        $validator = new TimeArgumentValidator();

        $this->expectException(InvalidArgumentException::class);

        $validator->validate(CommandArgument::TIME, '0');
    }

    #[Test, DataProvider('provideTimeStrings')]
    public function itShouldCalculateMinutesCorrectly(string $value, int $expectedResult): void
    {
        $validator = new TimeArgumentValidator();

        $result = $validator->validate(CommandArgument::TIME, $value);

        $this->assertEquals($expectedResult, $result);
    }

    public static function provideTimeStrings(): array
    {
        return [
            ['10m', 10],
            ['60m', 60],
            ['70m', 70],
            ['1h', 60],
            ['1h0m', 60],
            ['1h 0m', 60],
            ['0h 60m', 60],
            ['0h 50m', 50],
            ['1h 10m', 70],
            ['1h 70m', 130],
            ['2h', 120],
            ['120m', 120],
        ];
    }

    #[Test, DataProvider('provideInvalidTimeStrings')]
    public function itShouldThrowInvalidArgumentExceptionIfInvalidTimeStringsPassed(string $value): void
    {
        $validator = new TimeArgumentValidator();

        $this->expectException(InvalidArgumentException::class);

        $validator->validate(CommandArgument::TIME, $value);
    }

    public static function provideInvalidTimeStrings(): array
    {
        return [
            ['0h 0m'],
            ['0h'],
            ['0h0m'],
            ['0m'],
        ];
    }
}
