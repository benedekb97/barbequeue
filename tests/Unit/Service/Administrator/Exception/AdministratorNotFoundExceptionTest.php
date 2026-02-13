<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Administrator\Exception;

use App\Service\Administrator\Exception\AdministratorNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorNotFoundException::class)]
class AdministratorNotFoundExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameter(): void
    {
        $exception = new AdministratorNotFoundException($userId = 'userId');

        $this->assertSame($userId, $exception->getUserId());
    }
}
