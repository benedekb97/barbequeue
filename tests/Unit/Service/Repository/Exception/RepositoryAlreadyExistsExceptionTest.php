<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Repository\Exception;

use App\Service\Repository\Exception\RepositoryAlreadyExistsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryAlreadyExistsException::class)]
class RepositoryAlreadyExistsExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $exception = new RepositoryAlreadyExistsException($name = 'name');

        $this->assertSame($name, $exception->getName());
    }
}
