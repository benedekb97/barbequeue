<?php

declare(strict_types=1);

namespace App\Form\Queue;

use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Enum\Queue as QueueEnum;
use App\Enum\QueueBehaviour;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

#[OA\Schema(
    oneOf: [
        new OA\Schema(
            required: ['type', 'name'],
            properties: [
                new OA\Property(property: 'type', type: 'string', example: 'simple'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'expiryMinutes', type: 'integer'),
                new OA\Property(property: 'maximumEntriesPerUser', type: 'integer'),
            ]
        ),
        new OA\Schema(
            required: ['type', 'name', 'repositories'],
            properties: [
                new OA\Property(property: 'type', type: 'string', example: 'deployment'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'expiryMinutes', type: 'integer'),
                new OA\Property(property: 'maximumEntriesPerUser', type: 'integer'),
                new OA\Property(property: 'repositories', type: 'array', items: new OA\Items(description: 'Repository IDs', type: 'integer')),
                new OA\Property(property: 'behaviour', ref: '#/components/schemas/QueueBehaviour'),
            ]
        ),
    ],
)]
class QueueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', EnumType::class, [
            'class' => QueueEnum::class,
            'constraints' => [new NotNull()],
            'mapped' => false,
        ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (PreSubmitEvent $event): void {
                $form = $event->getForm();

                /** @var array<string, string> $data */
                $data = $event->getData();

                if (!isset($data['type'])) {
                    $form->addError(new FormError('type is required'));

                    return;
                }

                /** @var Queue $queue */
                $queue = $form->getData();

                if ($queue->getType()->value !== $type = $data['type']) {
                    $form->addError(new FormError('Changing queue types is not yet supported.'));

                    return;
                }

                /** @var Workspace $workspace */
                $workspace = $queue->getWorkspace();

                if (QueueEnum::DEPLOYMENT->value === $type) {
                    $this->addDeploymentQueueFields($form, $workspace);
                }

                if ($type === QueueEnum::SIMPLE->value) {
                    $this->addSimpleQueueFields($form);
                }
            });
    }

    private function addDeploymentQueueFields(FormInterface $form, Workspace $workspace): void
    {
        $this->addSimpleQueueFields($form);

        $form->add('behaviour', EnumType::class, [
            'class' => QueueBehaviour::class,
            'constraints' => [new NotNull()],
        ])
            ->add('repositories', EntityType::class, [
                'class' => Repository::class,
                'choices' => $workspace->getRepositories(),
                'multiple' => true,
            ]);
    }

    private function addSimpleQueueFields(FormInterface $form): void
    {
        $form->add('name', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('expiryMinutes', IntegerType::class, ['constraints' => [new Positive()]])
            ->add('maximumEntriesPerUser', IntegerType::class, ['constraints' => [new Positive()]]);
    }
}
