<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common;

use App\Slack\Response\PrivateMessage\NoResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(NoResponse::class)]
class NoResponseTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnEmptyArray(): void
    {
        $response = new NoResponse();

        $this->assertEmpty($response->toArray());
    }
}
