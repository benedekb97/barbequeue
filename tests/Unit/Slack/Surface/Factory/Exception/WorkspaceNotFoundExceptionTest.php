<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Exception;

use App\Slack\Surface\Factory\Exception\WorkspaceNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(WorkspaceNotFoundException::class)]
class WorkspaceNotFoundExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameter(): void
    {
        $exception = new WorkspaceNotFoundException($workspaceId = 'workspaceId');

        $this->assertSame($workspaceId, $exception->getWorkspaceId());
    }
}
