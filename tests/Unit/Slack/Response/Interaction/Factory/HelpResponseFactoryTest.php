<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory;

use App\Slack\Command\Command;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\HelpResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(HelpResponseFactory::class)]
class HelpResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnSingleSectionIfSubCommandProvided(): void
    {
        $command = Command::BBQ;
        $subCommand = SubCommand::LIST;

        $factory = new HelpResponseFactory();

        $result = $factory->create($command, $subCommand)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'Show a list of people currently in a queue '.PHP_EOL.'   `/bbq list {queue}`',
            $blocks[0],
        );
    }

    #[Test]
    public function itShouldReturnAllSubCommandHelpTextIfSubCommandNotProvided(): void
    {
        $factory = new HelpResponseFactory();

        $result = $factory->create(Command::BBQ, null)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(5, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            '• Show a list of people currently in a queue '.PHP_EOL.'   `/bbq list {queue}`',
            $blocks[2],
        );
    }
}
