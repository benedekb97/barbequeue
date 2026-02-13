<?php

declare(strict_types=1);

namespace App\Controller\Queue;

use App\Controller\AbstractController;
use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Enum\Queue as QueueEnum;
use App\Form\QueuedUser\Data\DeploymentData;
use App\Form\QueuedUser\Data\QueuedUserData;
use App\Form\QueuedUser\DeploymentType;
use App\Form\QueuedUser\QueuedUserType;
use App\Repository\QueuedUserRepositoryInterface;
use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\InvalidDeploymentUrlException;
use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\Join\Factory\JoinQueueContextFactory;
use App\Service\Queue\Join\JoinQueueHandler;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Leave\LeaveQueueHandler;
use App\Service\Queue\Pop\PopQueueContext;
use App\Service\Queue\Pop\PopQueueHandler;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/queue/{queueName}/queued-user', name: 'queued_user_')]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Unauthorized',
    content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
)]
#[OA\Tag(
    name: 'queued-user',
    description: 'Queued User API',
)]
class QueuedUserController extends AbstractController
{
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        private readonly QueueRepositoryInterface $queueRepository,
        private readonly QueuedUserRepositoryInterface $queuedUserRepository,
        private readonly JoinQueueHandler $joinQueueHandler,
        private readonly LoggerInterface $logger,
        private readonly LeaveQueueHandler $leaveQueueHandler,
        private readonly PopQueueHandler $popQueueHandler,
        private readonly JoinQueueContextFactory $joinQueueContextFactory,
    ) {
        parent::__construct($entityManager, $validator);
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'List queued users for queue',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(anyOf: [
                new OA\Schema(ref: new Model(type: QueuedUser::class, groups: ['queued-user'], name: 'QueueQueuedUser')),
                new OA\Schema(ref: new Model(type: Deployment::class, groups: ['queued-user'])),
            ])
        )
    )]
    public function index(string $queueName): JsonResponse
    {
        $queue = $this->queueRepository->findOneByNameAndWorkspace($queueName, $this->getUser()->getWorkspace())
            ?? throw new NotFoundHttpException();

        return $this->json($queue->getQueuedUsers());
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Not found',
        content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Show queued user or deployment',
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(ref: new Model(type: QueuedUser::class, groups: ['queued-user'], name: 'QueueQueuedUser')),
                new OA\Schema(ref: new Model(type: Deployment::class, groups: ['queued-user'])),
            ]
        )
    )]
    public function show(string $queueName, int $id): JsonResponse
    {
        $queuedUser = $this->queuedUserRepository->findOneByIdQueueNameAndWorkspace(
            $id,
            $queueName,
            $this->getUser()->getWorkspace()
        ) ?? throw new NotFoundHttpException();

        return $this->json($queuedUser);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Not found',
        content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Queued user or deployment created',
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(ref: new Model(type: QueuedUser::class, groups: ['queued-user'])),
                new OA\Schema(ref: new Model(type: Deployment::class, groups: ['queued-user'])),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Internal error occurred, handled gracefully.',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(ref: new Model(type: QueuedUserType::class, options: ['queue' => new Queue()])),
                new OA\Schema(ref: new Model(type: DeploymentType::class, options: ['queue' => new DeploymentQueue()])),
            ]
        )
    )]
    public function create(string $queueName, Request $request): JsonResponse
    {
        $queue = $this->queueRepository->findOneByNameAndWorkspace($queueName, ($user = $this->getUser())->getWorkspace())
            ?? throw new NotFoundHttpException();

        $type = $queue->getType();

        $data = match ($type) {
            QueueEnum::DEPLOYMENT => new DeploymentData(),
            default => new QueuedUserData(),
        };

        $data->setQueueName($queueName)
            ->setUser($user)
            ->setQueue($queue);

        $form = match ($type) {
            QueueEnum::DEPLOYMENT => $this->createForm(DeploymentType::class, $data, ['queue' => $queue]),
            default => $this->createForm(QueuedUserType::class, $data, ['queue' => $queue]),
        };

        $form->submit($request->getPayload()->all());

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $context = $this->joinQueueContextFactory->createFromFormData($data);

        try {
            $this->joinQueueHandler->handle($context);
        } catch (QueueNotFoundException $exception) {
            throw new NotFoundHttpException(previous: $exception);
        } catch (UnableToJoinQueueException|DeploymentInformationRequiredException|InvalidDeploymentUrlException $exception) {
            $this->logger->critical('Join queue handler threw exception {exception} while all issues should have been caught by validation.', [
                'exception' => $exception::class,
            ]);

            return $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($context->getQueuedUser(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Not found',
        content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'No content',
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Internal error occurred, handled gracefully.',
    )]
    public function delete(string $queueName, int $id): JsonResponse
    {
        $user = $this->getUser();

        $queuedUser = $this->queuedUserRepository->findOneByIdQueueNameAndWorkspace(
            $id,
            $queueName,
            $workspace = $user->getWorkspace()
        ) ?? throw new NotFoundHttpException();

        if (!$user->isAdministrator() && $queuedUser->getUser() !== $user) {
            throw new UnauthorizedHttpException('user');
        }

        try {
            if ($user->isAdministrator()) {
                $this->popQueueHandler->handle(new PopQueueContext(
                    $queueName,
                    (string) $workspace?->getSlackId(),
                    (string) $user->getSlackId(),
                    $id,
                ));
            } else {
                $this->leaveQueueHandler->handle(new LeaveQueueContext(
                    $queueName,
                    (string) $workspace?->getSlackId(),
                    (string) $user->getSlackId(),
                    $id,
                ));
            }
        } catch (PopQueueInformationRequiredException|QueueNotFoundException|UnableToLeaveQueueException|LeaveQueueInformationRequiredException $exception) {
            $this->logger->critical('Handler threw {exception} while all issues should have been caught by validation.', [
                'exception' => $exception::class,
            ]);

            return $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function json(mixed $data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        return parent::json($data, $status, $headers, array_merge($context, ['groups' => ['queued-user']]));
    }
}
