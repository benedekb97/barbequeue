<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response;

use App\Slack\Response\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Response::class)]
class ResponseTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveTwoCases(): void
    {
        $this->assertCount(2, Response::cases());
    }
}
