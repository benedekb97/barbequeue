<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Component\Exception;

use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnrecognisedInputElementException::class)]
class UnrecognisedInputElementExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $exception = new UnrecognisedInputElementException($inputElementType = 'inputElementType');

        $this->assertEquals($inputElementType, $exception->getInputElementType());
    }
}
