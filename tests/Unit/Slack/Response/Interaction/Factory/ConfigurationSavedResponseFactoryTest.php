<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory;

use App\Slack\Response\Interaction\Factory\ConfigurationSavedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ConfigurationSavedResponseFactory::class)]
class ConfigurationSavedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateInteractionResponse(): void
    {
        $factory = new ConfigurationSavedResponseFactory();

        $response = $factory->create()->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($response['blocks']);
        $this->assertCount(1, $response['blocks']);

        $this->assertSectionBlockCorrectlyFormatted(
            'Your preferences have been saved.',
            $response['blocks'][0],
        );
    }
}
