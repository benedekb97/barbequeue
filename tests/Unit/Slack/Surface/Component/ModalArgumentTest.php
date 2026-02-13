<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Component;

use App\Slack\BlockElement\Component\MultiStaticSelectElement;
use App\Slack\BlockElement\Component\MultiUsersSelectElement;
use App\Slack\BlockElement\Component\NumberInputElement;
use App\Slack\BlockElement\Component\PlainTextInputElement;
use App\Slack\BlockElement\Component\StaticSelectElement;
use App\Slack\BlockElement\Component\UrlInputElement;
use App\Slack\Interaction\InteractionArgumentLocation;
use App\Slack\Surface\Component\ModalArgument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ModalArgument::class)]
class ModalArgumentTest extends KernelTestCase
{
    #[Test, DataProvider('provideRequirements')]
    public function itShouldReturnCorrectRequirements(ModalArgument $argument, bool $required): void
    {
        $this->assertEquals($required, $argument->isRequired());
    }

    public static function provideRequirements(): array
    {
        return [
            [ModalArgument::CONFIGURATION_NOTIFICATION_MODE, true],
            [ModalArgument::QUEUE, true],
            [ModalArgument::REPOSITORY_ID, true],
            [ModalArgument::REPOSITORY_NAME, true],
            [ModalArgument::QUEUE_TYPE, true],
            [ModalArgument::QUEUE_NAME, true],
            [ModalArgument::QUEUE_REPOSITORIES, true],
            [ModalArgument::JOIN_QUEUE_NAME, true],
            [ModalArgument::DEPLOYMENT_DESCRIPTION, true],
            [ModalArgument::DEPLOYMENT_LINK, true],
            [ModalArgument::DEPLOYMENT_REPOSITORY, true],
            [ModalArgument::QUEUE_BEHAVIOUR, true],
        ];
    }

    #[Test, DataProvider('provideArgumentLocations')]
    public function itShouldReturnCorrectLocation(ModalArgument $argument, InteractionArgumentLocation $location): void
    {
        $this->assertEquals($location, $argument->getLocation());
    }

    public static function provideArgumentLocations(): array
    {
        return [
            [ModalArgument::QUEUE, InteractionArgumentLocation::PRIVATE_METADATA],
            [ModalArgument::QUEUE_EXPIRY_MINUTES, InteractionArgumentLocation::STATE],
            [ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER, InteractionArgumentLocation::STATE],
            [ModalArgument::REPOSITORY_ID, InteractionArgumentLocation::PRIVATE_METADATA],
            [ModalArgument::REPOSITORY_NAME, InteractionArgumentLocation::STATE],
            [ModalArgument::REPOSITORY_URL, InteractionArgumentLocation::STATE],
        ];
    }

    #[Test, DataProvider('provideLabels')]
    public function itShouldReturnCorrectLabel(ModalArgument $argument, ?string $label): void
    {
        $this->assertEquals($label, $argument->getLabel());
    }

    public static function provideLabels(): array
    {
        return [
            [ModalArgument::QUEUE, null],
            [ModalArgument::QUEUE_EXPIRY_MINUTES, 'How long before the first person in the queue gets removed'],
            [ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER, 'How many times a person can join the queue'],
            [ModalArgument::REPOSITORY_ID, null],
            [ModalArgument::REPOSITORY_NAME, 'What is the repository called?'],
            [ModalArgument::REPOSITORY_URL, 'Where can the repository be found?'],
        ];
    }

    #[Test, DataProvider('providePlaceholders')]
    public function itShouldReturnCorrectPlaceholder(ModalArgument $argument, ?string $placeholder): void
    {
        $this->assertEquals($placeholder, $argument->getPlaceholder());
    }

    public static function providePlaceholders(): array
    {
        return [
            [ModalArgument::QUEUE, null],
            [ModalArgument::QUEUE_EXPIRY_MINUTES, 'Expiry in minutes'],
            [ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER, 'Maximum entries per user'],
            [ModalArgument::REPOSITORY_ID, null],
            [ModalArgument::REPOSITORY_NAME, 'Repository name'],
            [ModalArgument::REPOSITORY_URL, 'Repository URL'],
        ];
    }

    #[Test, DataProvider('provideHints')]
    public function itShouldReturnCorrectHint(ModalArgument $argument, ?string $hint): void
    {
        $this->assertEquals($hint, $argument->getHint());
    }

    public static function provideHints(): array
    {
        return [
            [ModalArgument::QUEUE, null],
            [ModalArgument::QUEUE_EXPIRY_MINUTES, 'Leave empty for no limit. Will be rounded up to closest 5.'],
            [ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER, 'Leave empty for no limit.'],
            [ModalArgument::REPOSITORY_ID, null],
            [ModalArgument::REPOSITORY_NAME, null],
            [ModalArgument::REPOSITORY_URL, 'This will be displayed on development or environment queue entries'],
        ];
    }

    #[Test, DataProvider('provideFieldTypes')]
    public function itShouldReturnCorrectFieldType(ModalArgument $argument, ?string $fieldType): void
    {
        $this->assertEquals($fieldType, $argument->getFieldType());
    }

    public static function provideFieldTypes(): array
    {
        return [
            [ModalArgument::QUEUE, null],
            [ModalArgument::QUEUE_EXPIRY_MINUTES, NumberInputElement::class],
            [ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER, NumberInputElement::class],
            [ModalArgument::REPOSITORY_ID, null],
            [ModalArgument::REPOSITORY_NAME, PlainTextInputElement::class],
            [ModalArgument::REPOSITORY_URL, PlainTextInputElement::class],
            [ModalArgument::REPOSITORY_BLOCKS, MultiStaticSelectElement::class],
            [ModalArgument::QUEUE_TYPE, StaticSelectElement::class],
            [ModalArgument::QUEUE_REPOSITORIES, MultiStaticSelectElement::class],
            [ModalArgument::QUEUE_BEHAVIOUR, StaticSelectElement::class],
            [ModalArgument::QUEUE_NAME, PlainTextInputElement::class],
            [ModalArgument::DEPLOYMENT_DESCRIPTION, PlainTextInputElement::class],
            [ModalArgument::DEPLOYMENT_LINK, UrlInputElement::class],
            [ModalArgument::DEPLOYMENT_REPOSITORY, StaticSelectElement::class],
            [ModalArgument::DEPLOYMENT_NOTIFY_USERS, MultiUsersSelectElement::class],
            [ModalArgument::JOIN_QUEUE_REQUIRED_MINUTES, NumberInputElement::class],
            [ModalArgument::QUEUED_USER_ID, StaticSelectElement::class],
        ];
    }

    #[Test, DataProvider('provideExplanations')]
    public function itShouldReturnCorrectExplanation(ModalArgument $argument, ?string $explanation): void
    {
        $this->assertEquals($explanation, $argument->getExplanation());
    }

    public static function provideExplanations(): array
    {
        return [
            [ModalArgument::QUEUE_BEHAVIOUR, '*Enforce queue*: FIFO.
*Allow jump*: If the first deployment in line is blocked by a deployment in another queue, the next deployment can jump the queue.
*Allow simultaneous*: Allows all deployments where the repository is free to happen simultaneously.'],
        ];
    }

    #[Test, DataProvider('provideDispatchedActions')]
    public function itShouldReturnCorrectDispatchedAction(ModalArgument $argument, bool $hasDispatchedAction): void
    {
        $this->assertEquals($hasDispatchedAction, $argument->hasDispatchedAction());
    }

    public static function provideDispatchedActions(): array
    {
        return [[ModalArgument::QUEUE_TYPE, true]];
    }
}
