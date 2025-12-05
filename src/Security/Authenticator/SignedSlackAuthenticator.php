<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class SignedSlackAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly string $signingSecret,
        private readonly bool $signEnabled,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->signEnabled;
    }

    public function authenticate(Request $request): Passport
    {
        $timestamp = (int) $request->headers->get('X-Slack-Request-Timestamp');

        if (abs(time() - $timestamp) > 60 * 5) {
            throw new AuthenticationException('Cannot authenticate request made more than 5 minutes ago.');
        }

        $signatureBaseString = sprintf(
            'v0:%s:%s',
            $timestamp,
            $request->getContent()
        );

        $signature = 'v0='.hash_hmac('sha256', $signatureBaseString, $this->signingSecret);

        if ($request->headers->get('x-slack-signature') !== $signature) {
            throw new AuthenticationException('Could not validate request signature.');
        }

        return new SelfValidatingPassport(new UserBadge('slack'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            [
                'message' => $exception->getMessage(),
            ],
            Response::HTTP_FORBIDDEN,
        );
    }
}
