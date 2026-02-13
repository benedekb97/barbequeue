<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Workspace;
use Doctrine\Persistence\ObjectRepository;

/** @extends ObjectRepository<Workspace> */
interface WorkspaceRepositoryInterface extends ObjectRepository
{
}
