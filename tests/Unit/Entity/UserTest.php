<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Administrator;
use App\Entity\User;
use App\Entity\Workspace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(User::class)]
class UserTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $user = new User()
            ->setSlackId($slackId = 'slackId')
            ->setWorkspace($workspace = $this->createStub(Workspace::class))
            ->setAdministrator($administrator = $this->createStub(Administrator::class))
            ->setName($name = 'name');

        $this->assertSame($slackId, $user->getSlackId());
        $this->assertSame($workspace, $user->getWorkspace());
        $this->assertSame($administrator, $user->getAdministrator());
        $this->assertSame($name, $user->getName());
        $this->assertContains(User::ROLE_USER, $user->getRoles());
        $this->assertContains(User::ROLE_ADMINISTRATOR, $user->getRoles());
        $user->eraseCredentials();
        $this->assertEquals($slackId, $user->getUserIdentifier());
        $this->assertTrue($user->isAdministrator());

        $user->setAdministrator(null);

        $this->assertFalse($user->isAdministrator());
        $this->assertContains(User::ROLE_USER, $user->getRoles());
        $this->assertNotContains(User::ROLE_ADMINISTRATOR, $user->getRoles());
    }
}
