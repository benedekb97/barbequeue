<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\UserController;
use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserController::class)]
class UserControllerTest extends ApiTestCase
{
    #[Test]
    public function itShouldReturnUserInformation(): void
    {
        $this->jsonGet('/api/me', administrator: true);
        $this->assertOk();

        $this->assertNotEmpty($response = $this->getJsonResponse());

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('slackId', $response);
        $this->assertArrayHasKey('notificationSettings', $response);
        $this->assertArrayHasKey('administrator', $response);

        $this->jsonGet('/api/me');
        $this->assertOk();

        $this->assertNotEmpty($response = $this->getJsonResponse());

        $this->assertArrayHasKey('administrator', $response);
        $this->assertNull($response['administrator']);
    }
}
