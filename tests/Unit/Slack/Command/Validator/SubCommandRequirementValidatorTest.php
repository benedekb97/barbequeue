<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Validator;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\Validator\SubCommandRequirementValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SubCommandRequirementValidator::class)]
class SubCommandRequirementValidatorTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowSubCommandMissingExceptionIfCommandRequiresSubCommandButNoneProvided(): void
    {
        $validator = new SubCommandRequirementValidator();

        $this->expectException(SubCommandMissingException::class);

        $command = Command::BBQ;

        try {
            $validator->validate($command, '');
        } catch (SubCommandMissingException $exception) {
            $this->assertSame($command, $exception->getCommand());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldNotThrowExceptionIfCommandDoesNotRequireSubCommand(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new SubCommandRequirementValidator();

        $validator->validate(Command::TEST, '');
    }

    #[Test]
    public function itShouldNotThrowExceptionIfProvidedSubCommandStringIsNotEmpty(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new SubCommandRequirementValidator();

        $validator->validate(Command::BBQ, 'invalid-sub-command');
    }
}
