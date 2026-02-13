<?php

declare(strict_types=1);

namespace App\Form\QueuedUser;

use App\Entity\Queue;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractQueuedUserType extends AbstractType
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!array_key_exists('queue', $options)) {
            throw new \InvalidArgumentException('Queue not set on form builder. Aborting');
        }

        $queue = $options['queue'];

        if (!$queue instanceof Queue) {
            throw new \InvalidArgumentException('Queue not set on form builder. Aborting');
        }

        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('user');
        }

        $this->addFields($builder, $queue, $user);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setREquired(['queue']);
    }

    abstract protected function addFields(FormBuilderInterface $builder, Queue $queue, User $user): void;
}
