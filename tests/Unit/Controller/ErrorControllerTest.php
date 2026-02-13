<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\ErrorController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(ErrorController::class)]
class ErrorControllerTest extends KernelTestCase
{
    #[Test, DataProvider('provideStatusCodes')]
    public function itShouldReturnCorrectStatusCodeAndText(int $statusCode, string $message, \Throwable $exception): void
    {
        $controller = new ErrorController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ValidatorInterface::class),
        );

        $response = $controller->show($this->createStub(Request::class), $exception, null);

        $this->assertEquals($statusCode, $response->getStatusCode());

        $content = json_decode($response->getContent() ?: '', true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('code', $content);
        $this->assertEquals($statusCode, $content['code']);

        $this->assertArrayHasKey('message', $content);
        $this->assertEquals($message, $content['message']);
    }

    public static function provideStatusCodes(): array
    {
        return [
            [500, 'Internal Server Error', new \Exception()],
            [404, 'Not Found', new NotFoundHttpException()],
            [403, 'Forbidden', new AccessDeniedHttpException()],
            [401, 'Unauthorized', new UnauthorizedHttpException('')],
            [400, 'Bad Request', new BadRequestHttpException()],
            [422, 'Unprocessable Content', new UnprocessableEntityHttpException()],
        ];
    }
}
