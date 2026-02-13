<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Administrator\Exception;

use App\Entity\Administrator;
use App\Service\Administrator\Exception\AdministratorExistsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorExistsException::class)]
class AdministratorExistsExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedAdministrator(): void
    {
        $administrator = $this->createStub(Administrator::class);

        $exception = new AdministratorExistsException($administrator);

        $this->assertSame($administrator, $exception->getAdministrator());
    }
}
