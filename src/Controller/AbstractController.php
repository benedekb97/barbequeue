<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractController extends SymfonyController
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ValidatorInterface $validator,
    ) {
    }

    public function getUser(): User
    {
        $user = parent::getUser();

        if ($user instanceof User) {
            return $user;
        }

        throw new UnauthorizedHttpException('user');
    }

    protected function handleUpdateOrCreate(object $entity, FormInterface $form, Request $request): JsonResponse
    {
        $form->submit($request->getPayload()->all(), $request->isMethod(Request::METHOD_PUT));

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $errors = $this->validator->validate($entity);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->json($entity, status: $request->isMethod(Request::METHOD_POST) ? Response::HTTP_CREATED : Response::HTTP_OK);
    }
}
