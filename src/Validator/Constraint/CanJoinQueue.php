<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Entity\Queue;
use App\Entity\User;
use App\Validator\Constraint\Validator\CanJoinQueueValidator;
use Symfony\Component\Validator\Constraint;

class CanJoinQueue extends Constraint
{
    public function __construct(
        public readonly Queue $queue,
        public readonly User $currentUser,
    ) {
        parent::__construct(null, null, null);
    }

    public function validatedBy(): string
    {
        return CanJoinQueueValidator::class;
    }
}
