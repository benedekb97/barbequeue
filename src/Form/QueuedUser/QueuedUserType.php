<?php

declare(strict_types=1);

namespace App\Form\QueuedUser;

use App\Entity\Queue;
use App\Entity\User;
use App\Enum\Queue as QueueEnum;
use App\Validator\Constraint\CanJoinQueue;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

#[OA\Schema(
    required: ['user', 'type'],
    properties: [
        new OA\Property(property: 'expiryMinutes', type: 'integer'),
        new OA\Property(property: 'user', description: 'User ID', type: 'integer'),
        new OA\Property(property: 'type', type: 'enum', enum: ['simple']),
    ]
)]
class QueuedUserType extends AbstractQueuedUserType
{
    protected function addFields(FormBuilderInterface $builder, Queue $queue, User $user): void
    {
        $users = ($user->isAdministrator() ? $user->getWorkspace()?->getUsers()?->toArray() : [$user]) ?? [$user];

        $builder->add('expiryMinutes', NumberType::class, ['constraints' => [new Positive()], 'required' => false])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choices' => $users,
                'empty_data' => $user,
                'constraints' => [new CanJoinQueue($queue, $user), new NotNull()],
            ])
            ->add('type', EnumType::class, [
                'class' => QueueEnum::class,
                'choices' => [QueueEnum::SIMPLE],
                'mapped' => false,
                'constraints' => [new NotNull()],
            ]);
    }
}
