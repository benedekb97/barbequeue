<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Home;

use App\Entity\Workspace;
use App\Slack\Surface\Factory\Home\AdministratorWelcomeHomeViewFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithSurfaceAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorWelcomeHomeViewFactory::class)]
class AdministratorWelcomeHomeViewFactoryTest extends KernelTestCase
{
    use WithSurfaceAssertions;
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateHomeSurface(): void
    {
        $factory = new AdministratorWelcomeHomeViewFactory();

        $result = $factory->create($userId = 'userId', $workspace = $this->createStub(Workspace::class));

        $this->assertSame($workspace, $result->getWorkspace());

        $view = $this->assertHomeSurfaceCorrectlyFormed($result->toArray(), $userId);

        $this->assertArrayHasKey('blocks', $view);
        $this->assertIsArray($blocks = $view['blocks']);
        $this->assertCount(20, $blocks);

        $this->assertHeaderBlockCorrectlyFormatted('Welcome to BarbeQueue!', $blocks[0]);
        $this->assertSectionBlockCorrectlyFormatted(
            'Read more about how to administer your application below.',
            $blocks[1],
        );
        $this->assertDividerBlockCorrectlyFormatted($blocks[2]);
        $this->assertHeaderBlockCorrectlyFormatted('Admin commands', $blocks[3]);
        $this->assertSectionBlockCorrectlyFormatted(
            '*Repositories* - Link them to your queues to track releases or define blockers _(upcoming feature)_',
            $blocks[4],
        );
        $this->assertSectionBlockCorrectlyFormatted(
            '• List repositories added to workspace '.PHP_EOL.'   `/bbq-admin list-repositories`',
            $blocks[5],
        );
        $this->assertSectionBlockCorrectlyFormatted(
            '*Queues* - Take control of your queues',
            $blocks[9],
        );
        $this->assertSectionBlockCorrectlyFormatted(
            '*Administrators* - Give or revoke access to BBQ admin commands',
            $blocks[12],
        );
        $this->assertDividerBlockCorrectlyFormatted($blocks[15]);
        $this->assertHeaderBlockCorrectlyFormatted('User commands', $blocks[16]);
    }
}
