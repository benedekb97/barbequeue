<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction;

use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionArgumentLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Interaction::class)]
class InteractionTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveFourteenCases(): void
    {
        $this->assertCount(14, Interaction::cases());
    }

    #[Test]
    public function itShouldThrowValueErrorIfCannotResolveFromActionIdString(): void
    {
        $this->expectException(\ValueError::class);

        Interaction::fromActionId('non-existent-action-1');
    }

    #[Test]
    public function itShouldReturnInteractionIfActionIdResolvesCorrectly(): void
    {
        $result = Interaction::fromActionId('edit-queue');

        $this->assertEquals(Interaction::EDIT_QUEUE, $result);
    }

    #[Test, DataProvider('provideInteractionRequiredArguments')]
    public function itShouldReturnRequiredArgumentsCorrectly(Interaction $interaction, array $expectedArguments): void
    {
        $this->assertEquals($expectedArguments, $interaction->getRequiredArguments());
    }

    public static function provideInteractionRequiredArguments(): array
    {
        return [
            [Interaction::EDIT_QUEUE_ACTION, []],
            [Interaction::LEAVE_QUEUE, ['queue', 'queued_user_id']],
            [Interaction::JOIN_QUEUE, []],
            [Interaction::EDIT_QUEUE, ['queue']],
            [Interaction::ADD_REPOSITORY, ['repository_name']],
            [Interaction::EDIT_REPOSITORY, ['repository_id', 'repository_name']],
            [Interaction::REMOVE_REPOSITORY, []],
        ];
    }

    #[Test, DataProvider('provideInteractionOptionalArguments')]
    public function itShouldReturnOptionalArgumentsCorrectly(Interaction $interaction, array $expectedArguments): void
    {
        $this->assertEquals($expectedArguments, $interaction->getOptionalArguments());
    }

    public static function provideInteractionOptionalArguments(): array
    {
        return [
            [Interaction::EDIT_QUEUE_ACTION, []],
            [Interaction::LEAVE_QUEUE, []],
            [Interaction::JOIN_QUEUE, []],
            [Interaction::EDIT_QUEUE, ['expiry_minutes', 'maximum_entries_per_user']],
            [Interaction::ADD_REPOSITORY, ['repository_url', 'repository_blockers']],
            [Interaction::EDIT_REPOSITORY, ['repository_url', 'repository_blockers']],
            [Interaction::REMOVE_REPOSITORY, []],
        ];
    }

    #[Test, DataProvider('provideInteractionArguments')]
    public function itShouldReturnArgumentsCorrectly(Interaction $interaction, array $expectedArguments): void
    {
        $this->assertEquals($expectedArguments, $interaction->getArguments());
    }

    public static function provideInteractionArguments(): array
    {
        return [
            [Interaction::EDIT_QUEUE_ACTION, []],
            [Interaction::LEAVE_QUEUE, ['queue', 'queued_user_id']],
            [Interaction::JOIN_QUEUE, []],
            [Interaction::EDIT_QUEUE, ['queue', 'expiry_minutes', 'maximum_entries_per_user']],
            [Interaction::ADD_REPOSITORY, ['repository_name', 'repository_url', 'repository_blockers']],
            [Interaction::EDIT_REPOSITORY, ['repository_id', 'repository_name', 'repository_url', 'repository_blockers']],
            [Interaction::REMOVE_REPOSITORY, []],
        ];
    }

    #[Test, DataProvider('provideInteractionArgumentLocations')]
    public function itShouldReturnArgumentLocationCorrectly(
        Interaction $interaction,
        string $argument,
        InteractionArgumentLocation $expectedLocation,
    ): void {
        $this->assertEquals($expectedLocation, $interaction->getArgumentLocation($argument));
    }

    public static function provideInteractionArgumentLocations(): array
    {
        return [
            [Interaction::EDIT_QUEUE, 'queue', InteractionArgumentLocation::PRIVATE_METADATA],
            [Interaction::EDIT_QUEUE, 'expiry_minutes', InteractionArgumentLocation::STATE],
            [Interaction::EDIT_QUEUE, 'maximum_entries_per_user', InteractionArgumentLocation::STATE],
            [Interaction::ADD_REPOSITORY, 'repository_name', InteractionArgumentLocation::STATE],
            [Interaction::ADD_REPOSITORY, 'repository_url', InteractionArgumentLocation::STATE],
            [Interaction::EDIT_REPOSITORY, 'repository_id', InteractionArgumentLocation::PRIVATE_METADATA],
            [Interaction::EDIT_REPOSITORY, 'repository_name', InteractionArgumentLocation::STATE],
            [Interaction::EDIT_REPOSITORY, 'repository_url', InteractionArgumentLocation::STATE],
        ];
    }

    #[Test, DataProvider('provideInteractionAuthorisationRequirements')]
    public function itShouldRequireAuthorisationCorrectly(Interaction $interaction, bool $requiresAuthorisation): void
    {
        $this->assertEquals($requiresAuthorisation, $interaction->isAuthorisationRequired());
    }

    public static function provideInteractionAuthorisationRequirements(): array
    {
        return [
            [Interaction::EDIT_QUEUE_ACTION, true],
            [Interaction::LEAVE_QUEUE, false],
            [Interaction::JOIN_QUEUE, false],
            [Interaction::EDIT_QUEUE, true],
            [Interaction::ADD_REPOSITORY, true],
            [Interaction::EDIT_REPOSITORY, true],
            [Interaction::REMOVE_REPOSITORY, true],
        ];
    }

    #[Test, DataProvider('provideInteractionActions')]
    public function itShouldGetCorrectAction(Interaction $interaction, string $expectedAction): void
    {
        $this->assertEquals($expectedAction, $interaction->getAction());
    }

    public static function provideInteractionActions(): array
    {
        return [
            [Interaction::EDIT_QUEUE, 'edit queues'],
            [Interaction::EDIT_QUEUE_ACTION, 'edit queues'],
            [Interaction::JOIN_QUEUE, 'join queues'],
            [Interaction::LEAVE_QUEUE, 'leave queues'],
            [Interaction::EDIT_REPOSITORY, 'edit repositories'],
            [Interaction::ADD_REPOSITORY, 'add repositories'],
            [Interaction::REMOVE_REPOSITORY, 'remove repositories'],
            [Interaction::POP_QUEUE_ACTION, 'pop queues'],
            [Interaction::ADD_SIMPLE_QUEUE, 'add queues'],
            [Interaction::ADD_DEPLOYMENT_QUEUE, 'add deployment queues'],
            [Interaction::QUEUE_TYPE, 'select queue types'],
            [Interaction::JOIN_QUEUE_DEPLOYMENT, 'join deployment queues'],
            [Interaction::EDIT_QUEUE_DEPLOYMENT, 'edit deployment queues'],
        ];
    }
}
