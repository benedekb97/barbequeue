<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command;

use App\Slack\Command\SubCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SubCommand::class)]
class SubCommandTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveFourteenCases(): void
    {
        $this->assertCount(14, SubCommand::cases());
    }

    #[Test, DataProvider('provideAliases')]
    public function itShouldReturnCorrectCaseFromAlias(?SubCommand $case, string $alias): void
    {
        $this->assertEquals($case, SubCommand::tryFromAlias($alias));
    }

    public static function provideAliases(): array
    {
        return [
            [SubCommand::HELP, 'h'],
            [SubCommand::HELP, 'he'],
            [SubCommand::HELP, 'hl'],
            [SubCommand::HELP, 'hlp'],
            [SubCommand::HELP, 'help'],
            [SubCommand::JOIN, 'j'],
            [SubCommand::JOIN, 'jo'],
            [SubCommand::JOIN, 'joi'],
            [SubCommand::JOIN, 'join'],
            [SubCommand::LEAVE, 'l'],
            [SubCommand::LEAVE, 'lv'],
            [SubCommand::LEAVE, 'le'],
            [SubCommand::LEAVE, 'lea'],
            [SubCommand::LEAVE, 'leav'],
            [SubCommand::LEAVE, 'leave'],
            [SubCommand::LIST, 'ls'],
            [SubCommand::LIST, 'lst'],
            [SubCommand::LIST, 'lis'],
            [SubCommand::LIST, 'list'],
            [SubCommand::ADD_USER, 'au'],
            [SubCommand::ADD_USER, 'aus'],
            [SubCommand::ADD_USER, 'addu'],
            [SubCommand::ADD_USER, 'adus'],
            [SubCommand::ADD_USER, 'add-user'],
            [SubCommand::REMOVE_USER, 'ru'],
            [SubCommand::REMOVE_USER, 'reu'],
            [SubCommand::REMOVE_USER, 'reus'],
            [SubCommand::REMOVE_USER, 'remu'],
            [SubCommand::REMOVE_USER, 'remove-user'],
            [SubCommand::EDIT_QUEUE, 'eq'],
            [SubCommand::EDIT_QUEUE, 'edq'],
            [SubCommand::EDIT_QUEUE, 'ediq'],
            [SubCommand::EDIT_QUEUE, 'edit-queue'],
            [SubCommand::POP_QUEUE, 'pq'],
            [SubCommand::POP_QUEUE, 'popq'],
            [SubCommand::POP_QUEUE, 'poq'],
            [SubCommand::POP_QUEUE, 'pop-queue'],
            [SubCommand::ADD_REPOSITORY, 'ar'],
            [SubCommand::ADD_REPOSITORY, 'adr'],
            [SubCommand::ADD_REPOSITORY, 'adre'],
            [SubCommand::ADD_REPOSITORY, 'add-repository'],
            [SubCommand::REMOVE_REPOSITORY, 'rr'],
            [SubCommand::REMOVE_REPOSITORY, 'rer'],
            [SubCommand::REMOVE_REPOSITORY, 'rere'],
            [SubCommand::REMOVE_REPOSITORY, 'remove-repository'],
            [SubCommand::EDIT_REPOSITORY, 'er'],
            [SubCommand::EDIT_REPOSITORY, 'edr'],
            [SubCommand::EDIT_REPOSITORY, 'edre'],
            [SubCommand::EDIT_REPOSITORY, 'edit-repository'],
            [SubCommand::LIST_REPOSITORIES, 'lr'],
            [SubCommand::LIST_REPOSITORIES, 'lsrs'],
            [SubCommand::LIST_REPOSITORIES, 'lsr'],
            [SubCommand::LIST_REPOSITORIES, 'list-repositories'],
            [SubCommand::ADD_QUEUE, 'aq'],
            [SubCommand::ADD_QUEUE, 'adq'],
            [SubCommand::ADD_QUEUE, 'adqu'],
            [SubCommand::CONFIGURE, 'c'],
            [SubCommand::CONFIGURE, 'conf'],
            [SubCommand::CONFIGURE, 'config'],
            [null, 'something'],
            [null, 'anything'],
        ];
    }
}
