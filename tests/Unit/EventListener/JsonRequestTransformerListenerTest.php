<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\JsonRequestTransformerListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[CoversClass(JsonRequestTransformerListener::class)]
class JsonRequestTransformerListenerTest extends KernelTestCase
{
    public static function provideUnsupportedContentTypeFormats(): array
    {
        return [
            ['text'],
            ['html'],
            ['xml'],
        ];
    }

    #[Test, DataProvider('provideUnsupportedContentTypeFormats')]
    public function itShouldReturnEarlyIfUnsupportedRequestContentTypeFormat(string $format): void
    {
        $listener = new JsonRequestTransformerListener();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContentTypeFormat')
            ->willReturn($format);

        $event = $this->createMock(RequestEvent::class);
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $listener->onKernelRequest($event);
    }

    #[Test]
    public function itShouldSetResponseOnEventIfReceivedJsonDataIsInvalid(): void
    {
        $listener = new JsonRequestTransformerListener();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContentTypeFormat')
            ->willReturn('json');

        $request->expects($this->exactly(2))
            ->method('getContent')
            ->willReturn($content = 'invalidJsonContent{{{');

        $event = $this->createMock(RequestEvent::class);
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects($this->once())
            ->method('setResponse')
            ->willReturnCallback(function ($response) {
                $this->assertInstanceOf(JsonResponse::class, $response);
                $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
                $this->assertIsString($response = $response->getContent());
                $response = json_decode($response, true);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('message', $response);
                $this->assertEquals('Syntax error', $response['message']);
            });

        $listener->onKernelRequest($event);
    }

    #[Test]
    public function itShouldReplaceRequestDataWithParsedJsonData(): void
    {
        $content = json_encode(['key' => 'value']);
        $this->assertIsString($content);

        $request = new Request(content: $content);
        $request->headers->set('Content-Type', 'application/json');

        $event = $this->createMock(RequestEvent::class);
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $listener = new JsonRequestTransformerListener();

        $listener->onKernelRequest($event);

        $this->assertEquals('value', $request->request->get('key'));
    }
}
