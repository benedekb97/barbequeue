<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Response\Interaction\Factory\Repository\RepositoryNotFoundResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryNotFoundResponseFactory::class)]
class RepositoryNotFoundResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreatePrivateMessage(): void
    {
        $factory = new RepositoryNotFoundResponseFactory();

        $result = $factory->create()->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'We could not find the repository you are trying to edit. It may have been deleted by another administrator while you were editing it.',
            $blocks[0]
        );
    }
}
