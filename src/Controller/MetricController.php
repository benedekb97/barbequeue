<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\QueuedUserRepositoryInterface;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/metrics', name: 'metrics_')]
#[OA\Tag(
    name: 'metrics',
    description: 'Metrics API'
)]
class MetricController extends AbstractController
{
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        private readonly QueuedUserRepositoryInterface $queuedUserRepository,
    ) {
        parent::__construct($entityManager, $validator);
    }

    #[Route('/queued-user', name: 'queued_user', methods: [Request::METHOD_GET])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Metrics for queued users',
        content: new OA\JsonContent(
            required: ['total', 'active', 'unique', 'activeUnique'],
            properties: [
                new OA\Property(property: 'total', type: 'number', nullable: false),
                new OA\Property(property: 'active', type: 'number', nullable: false),
                new OA\Property(property: 'unique', type: 'number', nullable: false),
                new OA\Property(property: 'activeUnique', type: 'number', nullable: false),
            ],
            type: 'object',
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Unauthorized',
        content: new OA\JsonContent(
            ref: '#/components/schemas/UnauthorizedResponse',
        ),
    )]
    public function queuedUser(): JsonResponse
    {
        $yesterday = CarbonImmutable::now()->subDay();
        $now = CarbonImmutable::now();

        $totalQueued = $this->queuedUserRepository->countForWorkspace($workspace = $this->getUser()->getWorkspace(), $yesterday, $now);
        $uniqueQueued = $this->queuedUserRepository->countForWorkspace($workspace, $yesterday, $now, uniqueUsers: true);
        $activeQueued = $this->queuedUserRepository->countForWorkspace($workspace, $yesterday, $now, active: true);
        $activeUnique = $this->queuedUserRepository->countForWorkspace($workspace, $yesterday, $now, active: true, uniqueUsers: true);

        return $this->json([
            'total' => $totalQueued,
            'unique' => $uniqueQueued,
            'active' => $activeQueued,
            'activeUnique' => $activeUnique,
        ]);
    }
}
