<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Resolver;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidCommandException;
use App\Slack\Command\Resolver\CommandResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CommandResolver::class)]
class CommandResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowInvalidCommandExceptionIfInvalidCommandProvided(): void
    {
        $request = new Request();
        $request->request->set('command', '/invalid-command');

        $this->expectException(InvalidCommandException::class);

        $resolver = new CommandResolver();

        try {
            $resolver->resolve($request);
        } catch (InvalidCommandException $exception) {
            $this->assertEquals('invalid-command', $exception->getCommandText());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldResolveCommand(): void
    {
        $request = new Request();
        $request->request->set('command', '/bbq');

        $resolver = new CommandResolver();

        $result = $resolver->resolve($request);

        $this->assertEquals(Command::BBQ, $result);
    }
}
