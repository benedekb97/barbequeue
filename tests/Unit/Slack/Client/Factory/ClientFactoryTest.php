<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Client\Factory;

use App\Slack\Client\Factory\ClientFactory;
use JoliCode\Slack\Api\Runtime\Client\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ClientFactory::class)]
class ClientFactoryTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
    }

    #[Test]
    public function itShouldCreateClient(): void
    {
        /** @var ClientFactory $clientFactory */
        $clientFactory = self::getContainer()->get(ClientFactory::class);

        $result = $clientFactory->create('');

        $this->assertInstanceOf(Client::class, $result);
    }
}
