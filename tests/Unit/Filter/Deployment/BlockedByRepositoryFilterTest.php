<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter\Deployment;

use App\Entity\Deployment;
use App\Filter\Deployment\BlockedByRepositoryFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(BlockedByRepositoryFilter::class)]
class BlockedByRepositoryFilterTest extends KernelTestCase
{
    #[Test]
    public function itShouldFilterDeploymentsThatAreBlockedByRepository(): void
    {
        $blockedDeployment = $this->createMock(Deployment::class);
        $blockedDeployment->expects($this->once())->method('isBlockedByRepository')->willReturn(true);

        $notBlockedDeployment = $this->createMock(Deployment::class);
        $notBlockedDeployment->expects($this->once())->method('isBlockedByRepository')->willReturn(false);

        $filter = new BlockedByRepositoryFilter();

        $result = $filter->filter([$blockedDeployment, $notBlockedDeployment]);

        $this->assertCount(1, $result);
        $this->assertSame($notBlockedDeployment, $result[0]);
    }
}
