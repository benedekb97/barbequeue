<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Slack;

use App\Controller\Slack\MockApiController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(MockApiController::class)]
class MockApiControllerTest extends KernelTestCase
{
    #[Test]
    public function itShouldLogTwiceAndReturnNewResponse(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('debug')
            ->withAnyParameters();

        $controller = new MockApiController($logger);

        $response = $controller(new Request());

        $this->assertEmpty($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
