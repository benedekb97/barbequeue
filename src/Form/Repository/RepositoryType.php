<?php

declare(strict_types=1);

namespace App\Form\Repository;

use App\Entity\Repository;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class RepositoryType extends AbstractType
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('user');
        }

        $builder->add('name', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('url', UrlType::class, ['constraints' => [new Url(requireTld: true)]])
            ->add('deploymentBlocksRepositories', EntityType::class, [
                'class' => Repository::class,
                'choices' => $user->getWorkspace()?->getRepositories(),
                'multiple' => true,
            ]);
    }
}
