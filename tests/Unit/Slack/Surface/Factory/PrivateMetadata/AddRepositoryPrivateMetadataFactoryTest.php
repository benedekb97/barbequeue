<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\PrivateMetadata;

use App\Slack\Surface\Factory\PrivateMetadata\AddRepositoryPrivateMetadataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AddRepositoryPrivateMetadataFactory::class)]
class AddRepositoryPrivateMetadataFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldEncodeInteractionAddRepository(): void
    {
        $factory = new AddRepositoryPrivateMetadataFactory();

        $factory->setResponseUrl($responseUrl = 'responseUrl');

        $result = $factory->create();

        $result = json_decode($result, true);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('action', $result);
        $this->assertEquals('add-repository', $result['action']);
        $this->assertArrayHasKey('response_url', $result);
        $this->assertEquals($responseUrl, $result['response_url']);
    }
}
