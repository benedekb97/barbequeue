<?php

declare(strict_types=1);

namespace App\DataFixtures\Queue;

enum Administrators: string
{
    case FIRST_ADMINISTRATOR = 'first-administrator';
    case SECOND_ADMINISTRATOR = 'second-administrator';

    public function getTeamId(): Workspaces
    {
        return Workspaces::FIRST;
    }
}
