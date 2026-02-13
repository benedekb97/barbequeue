<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\QueuedUser\Data;

use App\Entity\Repository;
use App\Form\QueuedUser\Data\DeploymentData;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentData::class)]
class DeploymentDataTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $data = new DeploymentData()
            ->setRepository($repository = $this->createStub(Repository::class))
            ->setDescription($description = 'description')
            ->setLink($link = 'link')
            ->setNotifyUsers($collection = $this->createStub(Collection::class));

        $this->assertSame($repository, $data->getRepository());
        $this->assertSame($description, $data->getDescription());
        $this->assertSame($link, $data->getLink());
        $this->assertSame($collection, $data->getNotifyUsers());
    }
}
