<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraint;

use App\Entity\Queue;
use App\Entity\User;
use App\Validator\Constraint\CanJoinQueue;
use App\Validator\Constraint\Validator\CanJoinQueueValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(CanJoinQueue::class)]
class CanJoinQueueTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnCorrectValidatedBy(): void
    {
        $constraint = new CanJoinQueue(
            $this->createStub(Queue::class),
            $this->createStub(User::class),
        );

        $this->assertEquals(CanJoinQueueValidator::class, $constraint->validatedBy());
    }
}
