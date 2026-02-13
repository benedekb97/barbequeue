<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\PrivateMetadata;

use App\Entity\Repository;
use App\Slack\Surface\Factory\PrivateMetadata\EditRepositoryPrivateMetadataFactory;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(EditRepositoryPrivateMetadataFactory::class)]
class EditRepositoryPrivateMetadataFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowJsonEncodingExceptionIfRepositoryNotSet(): void
    {
        $factory = new EditRepositoryPrivateMetadataFactory();

        $this->expectException(JsonEncodingException::class);

        try {
            $factory->create();
        } catch (JsonEncodingException $exception) {
            $this->assertEquals(
                'Could not encode private metadata: missing repository',
                $exception->getMessage()
            );

            throw $exception;
        }
    }

    #[Test]
    public function itShouldEncodeRepositoryId(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $result = new EditRepositoryPrivateMetadataFactory()
            ->setRepository($repository)
            ->setResponseUrl('responseUrl')
            ->create();

        $result = json_decode($result, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('action', $result);
        $this->assertEquals('edit-repository', $result['action']);
        $this->assertArrayHasKey('repository_id', $result);
        $this->assertEquals(1, $result['repository_id']);
        $this->assertArrayHasKey('response_url', $result);
        $this->assertEquals('responseUrl', $result['response_url']);
    }
}
