<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common\Factory;

use App\Entity\Queue;
use App\Slack\Response\Common\Factory\QueueEditedMessageFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueEditedMessageFactory::class)]
class QueueEditedMessageFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test, DataProvider('provideForItShouldReturnPrivateMessageResponse')]
    public function itShouldReturnPrivateMessageResponse(
        ?int $maximumEntriesPerUser,
        ?int $expiryMinutes
    ): void {
        $name = 'queue';
        $id = 1;

        $factory = new QueueEditedMessageFactory();

        $queue = $this->createMock(Queue::class);
        $queue->expects(self::once())
            ->method('getName')
            ->willReturn($name);

        $queue->expects(self::exactly(2))
            ->method('getId')
            ->wilLReturn($id);

        $queue
            ->expects(
                $maximumEntriesPerUser === null
                    ? self::once()
                    : self::exactly(2)
            )
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maximumEntriesPerUser);

        $queue
            ->expects(
                $expiryMinutes === null
                    ? self::once()
                    : self::exactly(2)
            )
            ->method('getExpiryMinutes')
            ->willReturn($expiryMinutes);

        $result = $factory->create($queue, $userId = 'userId');

        $this->assertEquals($userId, $result->getUserId());

        $this->assertArrayNotHasKey('text', $result = $result->toArray());
        $this->assertArrayHasKey('blocks', $result);

        $blocks = $result['blocks'];

        $this->assertIsString($blocks);
        $this->assertJson($blocks);

        $blocks = json_decode($blocks, true);

        $this->assertIsArray($blocks);
        $this->assertCount(4, $blocks);

        $sectionBlock = $blocks[0];
        $this->assertSectionBlockCorrectlyFormatted(
            sprintf('Queue *%s* edited successfully.', $name),
            $sectionBlock
        );

        $dividerBlock = $blocks[1];
        $this->assertDividerBlockCorrectlyFormatted($dividerBlock);

        $tableBlock = $blocks[2];
        $this->assertTableBlockCorrectlyFormatted([
            [
                [
                    'type' => 'raw_text',
                    'text' => 'Parameter',
                ],[
                    'type' => 'raw_text',
                    'text' => 'Value',
                ],
            ],[
                [
                    'type' => 'raw_text',
                    'text' => 'Top of queue expiry (minutes)',
                ],[
                    'type' => 'raw_text',
                    'text' => $expiryMinutes ? $expiryMinutes . ' minutes' : 'No expiry',
                ],
            ],[
                [
                    'type' => 'raw_text',
                    'text' => 'Maximum entries per user',
                ],[
                    'type' => 'raw_text',
                    'text' => $maximumEntriesPerUser ? $maximumEntriesPerUser . ' entries' : 'No limit',
                ],
            ],
        ], $tableBlock);

        $actionsBlock = $blocks[3];
        $this->assertActionsBlockCorrectlyFormatted([
            [
                'type' => 'button',
                'text' => [
                    'type' => 'plain_text',
                    'text' => 'Edit',
                ],
                'action_id' => sprintf('edit-queue-action-%d', $id),
                'value' => sprintf('%d', $id),
            ],
        ], $actionsBlock);
    }

    public static function provideForItShouldReturnPrivateMessageResponse(): array
    {
        return [
            'No user limit, no expiry' => [null, null],
            'User limit, no expiry' => [3, null],
            'No user limit, expiry' => [null, 20],
            'User limit, expiry' => [3, 20],
        ];
    }
}
