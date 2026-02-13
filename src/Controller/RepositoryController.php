<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Repository;
use App\Form\Repository\RepositoryType;
use App\Repository\RepositoryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/repository', name: 'repository_')]
#[OA\Tag(
    name: 'repository',
    description: 'Repository API',
)]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Unauthorized',
    content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
)]
class RepositoryController extends AbstractController
{
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        private readonly RepositoryRepositoryInterface $repositoryRepository,
    ) {
        parent::__construct($entityManager, $validator);
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'List repositories',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Repository::class, groups: ['repository', 'blocked-repository'])),
        )
    )]
    public function index(): JsonResponse
    {
        return $this->json($this->getUser()->getWorkspace()?->getRepositories());
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Show repository',
        content: new OA\JsonContent(
            ref: new Model(type: Repository::class, groups: ['repository', 'blocked-repository'])
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Not found',
        content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
    )]
    public function show(int $id): JsonResponse
    {
        $repository = $this->repositoryRepository->findOneBy([
            'id' => $id,
            'workspace' => $this->getUser()->getWorkspace(),
        ]) ?? throw new NotFoundHttpException();

        return $this->json($repository);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: RepositoryType::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Repository updated',
        content: new OA\JsonContent(ref: new Model(type: Repository::class, groups: ['repository', 'blocked-repository'])),
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Not found',
        content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $repository = $this->repositoryRepository->findOneBy([
            'id' => $id,
            'workspace' => $this->getUser()->getWorkspace(),
        ]) ?? throw new NotFoundHttpException();

        $form = $this->createForm(RepositoryType::class, $repository);

        return $this->handleUpdateOrCreate($repository, $form, $request);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMINISTRATOR')]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: RepositoryType::class))
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Created',
        content: new OA\JsonContent(ref: new Model(type: Repository::class, groups: ['repository', 'blocked-repository']))
    )]
    public function create(Request $request): JsonResponse
    {
        $repository = new Repository()
            ->setWorkspace($this->getUser()->getWorkspace());

        $form = $this->createForm(RepositoryType::class, $repository);

        return $this->handleUpdateOrCreate($repository, $form, $request);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMINISTRATOR')]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Not found',
        content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'No content',
    )]
    public function delete(int $id): JsonResponse
    {
        $repository = $this->repositoryRepository->findOneBy([
            'id' => $id,
            'workspace' => $this->getUser()->getWorkspace(),
        ]) ?? throw new NotFoundHttpException();

        $errors = $this->validator->validate($repository, groups: ['delete']);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->remove($repository);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function json(mixed $data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        return parent::json($data, $status, $headers, array_merge($context, ['groups' => ['repository', 'blocked-repository']]));
    }
}
