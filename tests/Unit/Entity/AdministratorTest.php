<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Administrator;
use App\Entity\User;
use App\Entity\Workspace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Administrator::class)]
class AdministratorTest extends KernelTestCase
{
    #[Test]
    public function itShouldSetPropertiesCorrectly(): void
    {
        $administrator = new Administrator();

        $administrator->setUser($user = $this->createMock(User::class))
            ->setWorkspace($workspace = $this->createStub(Workspace::class))
            ->setAddedBy($addedBy = $this->createStub(Administrator::class));

        $user->expects(self::exactly(2))->method('getSlackId')
            ->willReturn($userId = 'userId');

        $this->assertSame($userId, $administrator->getUserId());
        $this->assertSame($user, $administrator->getUser());
        $this->assertSame($workspace, $administrator->getWorkspace());
        $this->assertSame($addedBy, $administrator->getAddedBy());
        $this->assertEquals('<@'.$userId.'>', $administrator->getUserLink());
    }

    #[Test]
    public function itShouldReturnTrueOnIsAddedByIfNotAddedByDirectParent(): void
    {
        $root = new Administrator();

        $child = new Administrator();

        $grandChild = new Administrator();

        $grandChild->setAddedBy($child);
        $child->setAddedBy($root);

        $this->assertTrue($grandChild->isAddedBy($root));
    }

    #[Test]
    public function itShouldReturnFalseIfNotAddedByUnrelatedAdministrator(): void
    {
        $root = new Administrator();

        $child = new Administrator();

        $grandChild = new Administrator();

        $unrelated = new Administrator();

        $grandChild->setAddedBy($child);
        $child->setAddedBy($root);

        $this->assertFalse($grandChild->isAddedBy($unrelated));
    }
}
