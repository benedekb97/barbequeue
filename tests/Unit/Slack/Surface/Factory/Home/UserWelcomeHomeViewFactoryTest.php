<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Home;

use App\Entity\Workspace;
use App\Slack\Surface\Factory\Home\UserWelcomeHomeViewFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithSurfaceAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UserWelcomeHomeViewFactory::class)]
class UserWelcomeHomeViewFactoryTest extends KernelTestCase
{
    use WithSurfaceAssertions;
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateHomeSurface(): void
    {
        $factory = new UserWelcomeHomeViewFactory();

        $result = $factory->create($userId = 'userId', $workspace = $this->createStub(Workspace::class));

        $this->assertSame($workspace, $result->getWorkspace());

        $view = $this->assertHomeSurfaceCorrectlyFormed($result->toArray(), $userId);

        $this->assertArrayHasKey('blocks', $view);
        $this->assertIsArray($blocks = $view['blocks']);
        $this->assertCount(8, $blocks);

        $this->assertHeaderBlockCorrectlyFormatted('Welcome to BarbeQueue!', $blocks[0]);
        $this->assertSectionBlockCorrectlyFormatted('Read about how to use BarbeQueue below.', $blocks[1]);
        $this->assertDividerBlockCorrectlyFormatted($blocks[2]);
        $this->assertSectionBlockCorrectlyFormatted(
            'BBQ allows users to join a queue more than once. There may also be a limit on how long you can hold the front of the queue for. It all depends on the settings for the specific queue in question.',
            $blocks[3],
        );
        $this->assertHeaderBlockCorrectlyFormatted('Commands', $blocks[4]);
        $this->assertSectionBlockCorrectlyFormatted(
            '• Remove yourself from a queue '.PHP_EOL.'   `/bbq leave {queue}`',
            $blocks[6]
        );
    }
}
