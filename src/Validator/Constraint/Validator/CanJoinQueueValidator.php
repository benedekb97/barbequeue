<?php

declare(strict_types=1);

namespace App\Validator\Constraint\Validator;

use App\Entity\User;
use App\Validator\Constraint\CanJoinQueue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CanJoinQueueValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof User) {
            throw new UnexpectedValueException($constraint, User::class);
        }

        if (!$constraint instanceof CanJoinQueue) {
            throw new UnexpectedTypeException($constraint, CanJoinQueue::class);
        }

        if ($constraint->queue->canJoin((string) $value->getSlackId())) {
            return;
        }

        if ($constraint->queue->getMaximumEntriesPerUser() > 1) {
            $this->context->buildViolation('{{ name }} can only join the {{ queue }} queue a maximum of {{ maximumEntriesPerUser }} times.')
                ->setParameter('{{ name }}', $this->getName($value, $constraint->currentUser))
                ->setParameter('{{ queue }}', (string) $constraint->queue->getName())
                ->setParameter('{{ maximumEntriesPerUser }}', (string) $constraint->queue->getMaximumEntriesPerUser())
                ->addViolation();

            return;
        }

        $this->context->buildViolation('{{ name }} {{ verb }} already in the {{ queue }} queue.')
            ->setParameter('{{ name }}', $name = $this->getName($value, $constraint->currentUser))
            ->setParameter('{{ verb }}', 'You' === $name ? 'are' : 'is')
            ->setParameter('{{ queue }}', (string) $constraint->queue->getName())
            ->addViolation();
    }

    private function getName(User $user, User $currentUser): string
    {
        if ($user === $currentUser) {
            return 'You';
        }

        return (string) $user->getName();
    }
}
