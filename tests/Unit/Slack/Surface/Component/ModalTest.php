<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Component;

use App\Slack\Interaction\InteractionArgumentLocation;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalArgument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Modal::class)]
class ModalTest extends KernelTestCase
{
    #[Test, DataProvider('provideRequiredFields')]
    public function itShouldReturnCorrectRequiredFields(Modal $modal, array $expectedFields): void
    {
        $this->assertEquals($expectedFields, $modal->getRequiredFields());
    }

    public static function provideRequiredFields(): array
    {
        return [
            [Modal::EDIT_QUEUE, []],
            [Modal::ADD_REPOSITORY, [ModalArgument::REPOSITORY_NAME]],
            [Modal::EDIT_REPOSITORY, [ModalArgument::REPOSITORY_NAME]],
        ];
    }

    #[Test, DataProvider('provideRequiredFieldNames')]
    public function itShouldReturnCorrectRequiredFieldNames(Modal $modal, array $expectedFields): void
    {
        $this->assertEquals($expectedFields, $modal->getRequiredFieldNames());
    }

    public static function provideRequiredFieldNames(): array
    {
        return [
            [Modal::EDIT_QUEUE, []],
            [Modal::ADD_REPOSITORY, ['repository_name']],
            [Modal::EDIT_REPOSITORY, ['repository_name']],
        ];
    }

    #[Test, DataProvider('provideRequiredArguments')]
    public function itShouldReturnCorrectRequiredArguments(Modal $modal, array $expectedFields): void
    {
        $this->assertEquals($expectedFields, $modal->getRequiredArguments());
    }

    public static function provideRequiredArguments(): array
    {
        return [
            [Modal::EDIT_QUEUE, [ModalArgument::QUEUE]],
            [Modal::ADD_REPOSITORY, []],
            [Modal::EDIT_REPOSITORY, [ModalArgument::REPOSITORY_ID]],
        ];
    }

    #[Test, DataProvider('provideRequiredArgumentNames')]
    public function itShouldReturnCorrectRequiredArgumentNames(Modal $modal, array $expectedFields): void
    {
        $this->assertEquals($expectedFields, $modal->getRequiredArgumentNames());
    }

    public static function provideRequiredArgumentNames(): array
    {
        return [
            [Modal::EDIT_QUEUE, ['queue']],
            [Modal::ADD_REPOSITORY, []],
            [Modal::EDIT_REPOSITORY, ['repository_id']],
        ];
    }

    #[Test, DataProvider('provideOptionalFields')]
    public function itShouldReturnCorrectOptionalFields(Modal $modal, array $expectedFields): void
    {
        $this->assertEquals($expectedFields, $modal->getOptionalFields());
    }

    public static function provideOptionalFields(): array
    {
        return [
            [Modal::EDIT_QUEUE, [ModalArgument::QUEUE_EXPIRY_MINUTES, ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER]],
            [Modal::ADD_QUEUE_SIMPLE, [ModalArgument::QUEUE_EXPIRY_MINUTES, ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER]],
            [Modal::ADD_QUEUE_DEPLOYMENT, [ModalArgument::QUEUE_EXPIRY_MINUTES, ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER]],
            [Modal::EDIT_QUEUE_DEPLOYMENT, [ModalArgument::QUEUE_EXPIRY_MINUTES, ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER]],
            [Modal::ADD_REPOSITORY, [ModalArgument::REPOSITORY_URL, ModalArgument::REPOSITORY_BLOCKS]],
            [Modal::EDIT_REPOSITORY, [ModalArgument::REPOSITORY_URL, ModalArgument::REPOSITORY_BLOCKS]],
            [Modal::JOIN_QUEUE_DEPLOYMENT, [ModalArgument::DEPLOYMENT_NOTIFY_USERS, ModalArgument::JOIN_QUEUE_REQUIRED_MINUTES]],
        ];
    }

    #[Test, DataProvider('provideOptionalFieldNames')]
    public function itShouldReturnCorrectOptionalFieldNames(Modal $modal, array $expectedFields): void
    {
        $this->assertEquals($expectedFields, $modal->getOptionalFieldNames());
    }

    public static function provideOptionalFieldNames(): array
    {
        return [
            [Modal::EDIT_QUEUE, ['expiry_minutes', 'maximum_entries_per_user']],
            [Modal::ADD_REPOSITORY, ['repository_url', 'repository_blockers']],
            [Modal::EDIT_REPOSITORY, ['repository_url', 'repository_blockers']],
        ];
    }

    #[Test, DataProvider('provideOptionalArguments')]
    public function itShouldReturnCorrectOptionalArguments(Modal $modal, array $expectedFields): void
    {
        $this->assertEquals($expectedFields, $modal->getOptionalArguments());
    }

    #[Test, DataProvider('provideOptionalArguments')]
    public function itShouldReturnCorrectOptionalArgumentNames(Modal $modal, array $expectedFields): void
    {
        $this->assertEquals($expectedFields, $modal->getOptionalArgumentNames());
    }

    public static function provideOptionalArguments(): array
    {
        return [
            [Modal::EDIT_QUEUE, []],
            [Modal::ADD_REPOSITORY, []],
            [Modal::EDIT_REPOSITORY, []],
        ];
    }

    #[Test, DataProvider('provideArgumentLocations')]
    public function itShouldGetCorrectArgumentLocation(Modal $modal, string $argument, InteractionArgumentLocation $location): void
    {
        $this->assertEquals($location, $modal->getArgumentLocation($argument));
    }

    public static function provideArgumentLocations(): array
    {
        return [
            [Modal::EDIT_QUEUE, 'queue', InteractionArgumentLocation::PRIVATE_METADATA],
            [Modal::EDIT_QUEUE, 'expiry_minutes', InteractionArgumentLocation::STATE],
            [Modal::EDIT_QUEUE, 'maximum_entries_per_user', InteractionArgumentLocation::STATE],
            [Modal::ADD_REPOSITORY, 'repository_name', InteractionArgumentLocation::STATE],
            [Modal::ADD_REPOSITORY, 'repository_url', InteractionArgumentLocation::STATE],
            [Modal::EDIT_REPOSITORY, 'repository_id', InteractionArgumentLocation::PRIVATE_METADATA],
            [Modal::EDIT_REPOSITORY, 'repository_name', InteractionArgumentLocation::STATE],
            [Modal::EDIT_REPOSITORY, 'repository_url', InteractionArgumentLocation::STATE],
        ];
    }

    #[Test, DataProvider('provideFields')]
    public function itShouldGetCorrectFields(Modal $modal, array $fields): void
    {
        $this->assertEquals($fields, $modal->getFields());
    }

    public static function provideFields(): array
    {
        return [
            [Modal::EDIT_QUEUE, [ModalArgument::QUEUE_EXPIRY_MINUTES, ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER]],
            [Modal::ADD_REPOSITORY, [ModalArgument::REPOSITORY_NAME, ModalArgument::REPOSITORY_URL, ModalArgument::REPOSITORY_BLOCKS]],
            [Modal::EDIT_REPOSITORY, [ModalArgument::REPOSITORY_NAME, ModalArgument::REPOSITORY_URL, ModalArgument::REPOSITORY_BLOCKS]],
        ];
    }

    #[Test, DataProvider('provideArguments')]
    public function itShouldGetCorrectArguments(Modal $modal, array $arguments): void
    {
        $this->assertEquals($arguments, $modal->getArguments());
    }

    public static function provideArguments(): array
    {
        return [
            [Modal::EDIT_QUEUE, [ModalArgument::QUEUE]],
            [Modal::ADD_REPOSITORY, []],
            [Modal::EDIT_REPOSITORY, [ModalArgument::REPOSITORY_ID]],
        ];
    }

    #[Test, DataProvider('provideArgumentNames')]
    public function itShouldReturnCorrectArgumentNames(Modal $modal, array $argumentNames): void
    {
        $this->assertEquals($argumentNames, $modal->getArgumentNames());
    }

    public static function provideArgumentNames(): array
    {
        return [
            [Modal::EDIT_QUEUE, ['queue']],
            [Modal::ADD_REPOSITORY, []],
            [Modal::EDIT_REPOSITORY, ['repository_id']],
        ];
    }

    #[Test, DataProvider('provideTitles')]
    public function itShouldReturnCorrectTitle(Modal $modal, string $title): void
    {
        $this->assertEquals($title, $modal->getTitle());
    }

    public static function provideTitles(): array
    {
        return [
            [Modal::EDIT_QUEUE, 'Edit queue'],
            [Modal::EDIT_REPOSITORY, 'Edit repository'],
            [Modal::ADD_REPOSITORY, 'Add repository'],
            [Modal::ADD_QUEUE, 'Add queue'],
            [Modal::ADD_QUEUE_DEPLOYMENT, 'Add deployment queue'],
            [Modal::ADD_QUEUE_SIMPLE, 'Add simple queue'],
            [Modal::JOIN_QUEUE_DEPLOYMENT, 'Join deployment queue'],
            [Modal::EDIT_QUEUE_DEPLOYMENT, 'Edit deployment queue'],
            [Modal::LEAVE_QUEUE, 'Leave queue'],
            [Modal::POP_QUEUE, 'Remove queued user'],
        ];
    }
}
