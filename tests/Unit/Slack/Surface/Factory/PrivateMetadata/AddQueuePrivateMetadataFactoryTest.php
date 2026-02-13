<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\PrivateMetadata;

use App\Enum\Queue;
use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Factory\PrivateMetadata\AddQueuePrivateMetadataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AddQueuePrivateMetadataFactory::class)]
class AddQueuePrivateMetadataFactoryTest extends KernelTestCase
{
    #[Test, DataProvider('provideQueueTypes')]
    public function itShouldCreatePrivateMetadata(?Queue $queueType, string $interaction): void
    {
        $metadataFactory = new AddQueuePrivateMetadataFactory();

        $result = $metadataFactory->setQueue($queueType)
            ->setResponseUrl($responseUrl = 'responseUrl')
            ->create();

        $result = json_decode($result, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('response_url', $result);
        $this->assertEquals($responseUrl, $result['response_url']);
        $this->assertArrayHasKey('action', $result);
        $this->assertEquals($interaction, $result['action']);
    }

    public static function provideQueueTypes(): array
    {
        return [
            [null, Interaction::ADD_SIMPLE_QUEUE->value],
            [Queue::SIMPLE, Interaction::ADD_SIMPLE_QUEUE->value],
            [Queue::DEPLOYMENT, Interaction::ADD_DEPLOYMENT_QUEUE->value],
        ];
    }
}
