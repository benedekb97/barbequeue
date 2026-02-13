<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'user', description: 'User API')]
class UserController extends AbstractController
{
    #[Route('/me', name: 'me', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Unauthorized',
        content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User information',
        content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['me'], name: 'UserInfo'))
    )]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json($user, context: ['groups' => ['me']]);
    }
}
