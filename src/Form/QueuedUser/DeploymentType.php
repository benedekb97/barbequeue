<?php

declare(strict_types=1);

namespace App\Form\QueuedUser;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\User;
use App\Enum\Queue as QueueEnum;
use App\Validator\Constraint\CanJoinQueue;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Url;

#[OA\Schema(
    required: ['user', 'type', 'repository', 'description', 'link'],
    properties: [
        new OA\Property(property: 'expiryMinutes', type: 'integer'),
        new OA\Property(property: 'user', description: 'User ID', type: 'integer'),
        new OA\Property(property: 'type', type: 'enum', enum: ['deployment']),
        new OA\Property(property: 'repository', description: 'Repository ID', type: 'integer'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'link', type: 'string'),
        new OA\Property(property: 'notifyUsers', type: 'array', items: new OA\Items(description: 'User IDs', type: 'integer')),
    ]
)]
class DeploymentType extends AbstractQueuedUserType
{
    protected function addFields(FormBuilderInterface $builder, Queue $queue, User $user): void
    {
        if (!$queue instanceof DeploymentQueue) {
            throw new \InvalidArgumentException('Queue not set on form builder. Aborting');
        }

        $builder->add('expiryMinutes', NumberType::class, ['constraints' => [new Positive()], 'required' => false])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choices' => ($user->isAdministrator() ? $user->getWorkspace()?->getUsers()->toArray() : [$user]) ?? [],
                'empty_data' => $user,
                'constraints' => [new CanJoinQueue($queue, $user), new NotNull()],
            ])
            ->add('type', EnumType::class, [
                'class' => QueueEnum::class,
                'choices' => [QueueEnum::DEPLOYMENT],
                'mapped' => false,
                'constraints' => [new NotNull()],
            ])
            ->add('repository', EntityType::class, [
                'class' => Repository::class,
                'choices' => $queue->getRepositories(),
                'constraints' => [new NotNull()],
            ])
            ->add('description', TextType::class, ['constraints' => [new NotBlank(allowNull: false)]])
            ->add('link', UrlType::class, ['constraints' => [new Url(requireTld: true), new NotBlank(allowNull: false)]])
            ->add('notifyUsers', EntityType::class, [
                'multiple' => true,
                'class' => User::class,
                'choices' => $user->getWorkspace()?->getUsers()->toArray() ?? [],
                'required' => false,
            ]);
    }
}
