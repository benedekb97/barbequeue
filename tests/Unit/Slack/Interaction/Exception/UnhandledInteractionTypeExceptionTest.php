<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Exception;

use App\Slack\Interaction\Exception\UnhandledInteractionTypeException;
use App\Slack\Interaction\InteractionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnhandledInteractionTypeException::class)]
class UnhandledInteractionTypeExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnProvidedProperty(): void
    {
        $exception = new UnhandledInteractionTypeException(
            $interactionType = InteractionType::BLOCK_ACTIONS
        );

        $this->assertSame($interactionType, $exception->getInteractionType());
    }
}
