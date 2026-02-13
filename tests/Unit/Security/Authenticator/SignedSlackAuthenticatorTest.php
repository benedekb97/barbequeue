<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Authenticator;

use App\Security\Authenticator\SignedSlackAuthenticator;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[CoversClass(SignedSlackAuthenticator::class)]
class SignedSlackAuthenticatorTest extends KernelTestCase
{
    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    #[Test, DataProvider('provideForItShouldSupportRequestsWhenEnabled')]
    public function itShouldSupportRequestsWhenEnabled(bool $enabled): void
    {
        $authenticator = new SignedSlackAuthenticator('secret', $enabled);

        $this->assertEquals($enabled, $authenticator->supports(new Request()));
    }

    public static function provideForItShouldSupportRequestsWhenEnabled(): array
    {
        return [
            'Enabled' => [true],
            'Disabled' => [false],
        ];
    }

    #[Test]
    public function itShouldThrowAuthenticationExceptionIfRequestTimestampMoreThanFiveMinutesAgo(): void
    {
        $authenticator = new SignedSlackAuthenticator('secret', true);

        $request = new Request();

        $request->headers->set('X-Slack-Request-Timestamp', sprintf('%d', time() - 60 * 10));

        $this->expectException(AuthenticationException::class);

        try {
            $authenticator->authenticate($request);
        } catch (AuthenticationException $e) {
            $this->assertEquals('Cannot authenticate request made more than 5 minutes ago.', $e->getMessage());

            throw $e;
        }
    }

    #[Test]
    public function itShouldThrowAuthenticationExceptionIfRequestTimestampMissing(): void
    {
        $authenticator = new SignedSlackAuthenticator('secret', true);

        $request = new Request();

        $this->expectException(AuthenticationException::class);

        try {
            $authenticator->authenticate($request);
        } catch (AuthenticationException $e) {
            $this->assertEquals('Cannot authenticate request made more than 5 minutes ago.', $e->getMessage());

            throw $e;
        }
    }

    #[Test]
    public function itShouldThrowAuthenticationExceptionIfRequestSignatureMissing(): void
    {
        $authenticator = new SignedSlackAuthenticator('secret', true);

        $request = new Request();

        $request->headers->set('X-Slack-Request-Timestamp', sprintf('%d', time()));

        $this->expectException(AuthenticationException::class);

        try {
            $authenticator->authenticate($request);
        } catch (AuthenticationException $e) {
            $this->assertEquals('Could not validate request signature.', $e->getMessage());

            throw $e;
        }
    }

    #[Test]
    public function itShouldThrowAuthenticationExceptionIfRequestSignatureIncorrect(): void
    {
        $authenticator = new SignedSlackAuthenticator(
            $this->faker->regexify('[a-f0-9]{32}'),
            true
        );

        $request = new Request();

        $request->headers->set('X-Slack-Request-Timestamp', sprintf('%d', $timestamp = time()));

        $signatureBase = sprintf(
            'v0:%s:%s',
            $timestamp,
            $request->getContent()
        );

        $signature = 'v0='.hash_hmac('sha256', $signatureBase, $this->faker->regexify('[a-f0-9]{32}'));

        $request->headers->set('x-slack-signature', $signature);

        $this->expectException(AuthenticationException::class);

        try {
            $authenticator->authenticate($request);
        } catch (AuthenticationException $e) {
            $this->assertEquals('Could not validate request signature.', $e->getMessage());

            throw $e;
        }
    }

    #[Test]
    public function itShouldReturnSelfValidatingPassportWithUserBadgeIfSignatureCorrect(): void
    {
        $authenticator = new SignedSlackAuthenticator(
            $signingSecret = $this->faker->regexify('[a-f0-9]{32}'),
            true
        );

        $request = new Request();

        $request->headers->set('X-Slack-Request-Timestamp', sprintf('%d', $timestamp = time()));

        $signatureBase = sprintf(
            'v0:%s:%s',
            $timestamp,
            $request->getContent()
        );

        $signature = 'v0='.hash_hmac('sha256', $signatureBase, $signingSecret);

        $request->headers->set('x-slack-signature', $signature);

        $result = $authenticator->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $result);

        $badge = $result->getBadge(UserBadge::class);
        $this->assertNotNull($badge);
        $this->assertInstanceof(UserBadge::class, $badge);
    }

    #[Test]
    public function itShouldReturnNullOnAuthenticationSuccess(): void
    {
        $authenticator = new SignedSlackAuthenticator('secret', true);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->never())->method('getUserIdentifier');

        $this->assertNull(
            $authenticator->onAuthenticationSuccess(
                new Request(),
                $token,
                'firewall'
            )
        );
    }

    #[Test]
    public function itShouldReturnForbiddenOnAuthenticationFailure(): void
    {
        $authenticator = new SignedSlackAuthenticator('secret', true);

        $result = $authenticator->onAuthenticationFailure(new Request(), new AuthenticationException('message'));

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $result->getStatusCode());
    }
}
