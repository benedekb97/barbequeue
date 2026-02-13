<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Validator\Argument;

use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidArgumentException;
use App\Slack\Command\Validator\Argument\RegexArgumentValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RegexArgumentValidator::class)]
class RegexArgumentValidatorTest extends KernelTestCase
{
    #[Test]
    public function itShouldNotSupportArgumentsWithoutRegularExpressions(): void
    {
        $validator = new RegexArgumentValidator();

        $this->assertFalse($validator->supports(CommandArgument::QUEUE));
    }

    #[Test]
    public function itShouldSupportUserArgument(): void
    {
        $validator = new RegexArgumentValidator();

        $this->assertTrue($validator->supports(CommandArgument::USER));
    }

    #[Test]
    public function itShouldThrowInvalidArgumentExceptionIfValueIsNull(): void
    {
        $validator = new RegexArgumentValidator();

        $this->expectException(InvalidArgumentException::class);

        $validator->validate(CommandArgument::USER, null);
    }

    #[Test]
    public function itShouldThrowInvalidArgumentExceptionIfArgumentHasNoRegularExpression(): void
    {
        $validator = new RegexArgumentValidator();

        $this->expectException(InvalidArgumentException::class);

        $validator->validate(CommandArgument::QUEUE, 'value');
    }

    #[Test]
    public function itShouldThrowInvalidArgumentExceptionIfNoMatchesFound(): void
    {
        $validator = new RegexArgumentValidator();

        $this->expectException(InvalidArgumentException::class);

        $validator->validate(CommandArgument::USER, 'noMatches');
    }

    #[Test]
    public function itShouldReturnTheFirstMatchIfValueMatchesRegularExpression(): void
    {
        $validator = new RegexArgumentValidator();

        $result = $validator->validate(CommandArgument::USER, '<@U123ABC45DE>');

        $this->assertEquals('U123ABC45DE', $result);
    }
}
