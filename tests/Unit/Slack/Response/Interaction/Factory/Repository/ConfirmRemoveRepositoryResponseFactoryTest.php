<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Slack\Common\Style;
use App\Slack\Response\Interaction\Factory\Repository\ConfirmRemoveRepositoryResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ConfirmRemoveRepositoryResponseFactory::class)]
class ConfirmRemoveRepositoryResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('name');

        $repository->expects($this->exactly(3))
            ->method('getId')
            ->willReturn(1);

        $factory = new ConfirmRemoveRepositoryResponseFactory();

        $result = $factory->create($repository)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'Are you sure you want to remove the `name` repository?',
            $blocks[0],
        );

        $this->assertActionsBlockCorrectlyFormatted(
            [],
            $blocks[1],
            ignoreElements: true,
        );

        $this->assertIsArray($blocks[1]);

        $this->assertArrayHasKey('elements', $blocks[1]);
        $this->assertIsArray($elements = $blocks[1]['elements']);

        $this->assertButtonBlockElementCorrectlyFormatted(
            'Cancel',
            $elements[0],
            'remove-repository-action-1-cancel',
            expectedValue: 'no',
        );
        $this->assertButtonBlockElementCorrectlyFormatted(
            'Yes, remove it.',
            $elements[1],
            'remove-repository-action-1-confirm',
            expectedValue: '1',
            expectedStyle: Style::DANGER,
            expectedConfirm: [
                'title' => [
                    'type' => 'plain_text',
                    'text' => 'Are you sure?',
                ],
                'text' => [
                    'type' => 'plain_text',
                    'text' => 'Just double-checking...',
                ],
                'confirm' => [
                    'type' => 'plain_text',
                    'text' => 'Yes, delete it already',
                ],
                'deny' => [
                    'type' => 'plain_text',
                    'text' => 'On second thoughts...',
                ],
                'style' => 'danger',
            ],
        );
    }
}
