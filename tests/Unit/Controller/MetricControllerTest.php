<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\MetricController;
use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\QueuedUserRepositoryInterface;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(MetricController::class)]
class MetricControllerTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowUnauthorizedExceptionIfUserNotInTokenStorage(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);

        $container->expects($this->once())
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage = $this->createMock(TokenStorageInterface::class));

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $controller = new MetricController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ValidatorInterface::class),
            $this->createStub(QueuedUserRepositoryInterface::class),
        );

        $controller->setContainer($container);

        $this->expectException(UnauthorizedHttpException::class);

        $controller->queuedUser();
    }

    #[Test]
    public function itShouldFetchFourDifferentMetricsForQueuedUsersFromRepository(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')
            ->willReturnCallback(function ($argument) {
                $this->assertIsString($argument);

                if ('security.token_storage' === $argument) {
                    return true;
                }

                return false;
            });

        $container->expects($this->once())
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage = $this->createMock(TokenStorageInterface::class));

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $user->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $callCount = 0;
        $repository = $this->createMock(QueuedUserRepositoryInterface::class);
        $repository->expects($this->exactly(4))
            ->method('countForWorkspace')
            ->willReturnCallback(function ($workspaceArgument, $yesterday, $now, $active, $uniqueUsers) use ($workspace, &$callCount) {
                $this->assertSame($workspace, $workspaceArgument);
                $this->assertInstanceOf(CarbonImmutable::class, $yesterday);
                $this->assertInstanceOf(CarbonImmutable::class, $now);

                $this->assertTrue($yesterday->isYesterday());
                $this->assertTrue($now->isToday());

                $this->assertIsBool($active);
                $this->assertIsBool($uniqueUsers);

                if ($callCount & 1) {
                    $this->assertTrue($uniqueUsers);
                } else {
                    $this->assertFalse($uniqueUsers);
                }

                if ($callCount & 2) {
                    $this->assertTrue($active);
                } else {
                    $this->assertFalse($active);
                }

                ++$callCount;

                return $callCount;
            });

        $controller = new MetricController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ValidatorInterface::class),
            $repository,
        );

        $controller->setContainer($container);

        $result = $controller->queuedUser();

        $this->assertIsString($response = $result->getContent());

        $response = json_decode($response, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('total', $response);
        $this->assertEquals(1, $response['total']);
        $this->assertArrayHasKey('unique', $response);
        $this->assertEquals(2, $response['unique']);
        $this->assertArrayHasKey('active', $response);
        $this->assertEquals(3, $response['active']);
        $this->assertArrayHasKey('activeUnique', $response);
        $this->assertEquals(4, $response['activeUnique']);
    }
}
