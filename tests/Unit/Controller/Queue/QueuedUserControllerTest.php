<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Queue;

use App\Controller\Queue\QueuedUserController;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\Queue as QueueEnum;
use App\Form\QueuedUser\Data\QueuedUserData;
use App\Form\QueuedUser\QueuedUserType;
use App\Repository\QueuedUserRepositoryInterface;
use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\InvalidDeploymentUrlException;
use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Join\Factory\JoinQueueContextFactory;
use App\Service\Queue\Join\JoinQueueContext;
use App\Service\Queue\Join\JoinQueueHandler;
use App\Service\Queue\Leave\LeaveQueueHandler;
use App\Service\Queue\Pop\PopQueueContext;
use App\Service\Queue\Pop\PopQueueHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(QueuedUserController::class)]
class QueuedUserControllerTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowNotFoundExceptionIfHandlerThrowsQueueNotFoundException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndWorkspace')
            ->with($queueName = 'queueName', $workspace = $this->createStub(Workspace::class))
            ->willReturn($queue = $this->createMock(Queue::class));

        $container->expects($this->once())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);

        $user = $this->createMock(User::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('submit')
            ->with([])
            ->willReturnSelf();

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $request = new Request();

        $context = $this->createStub(JoinQueueContext::class);
        $contextFactory = $this->createMock(JoinQueueContextFactory::class);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($context)
            ->willThrowException($this->createStub(QueueNotFoundException::class));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($type, $data, $options) use ($queue, $queueName, $user, $form, $context, $contextFactory) {
                $this->assertEquals(QueuedUserType::class, $type);
                $this->assertInstanceOf(QueuedUserData::class, $data);
                $this->assertEquals($queue, $data->getQueue());
                $this->assertEquals($queueName, $data->getQueueName());
                $this->assertEquals($user, $data->getUser());
                $this->assertIsArray($options);
                $this->assertArrayHasKey('queue', $options);
                $this->assertEquals($queue, $options['queue']);

                $contextFactory->expects($this->once())
                    ->method('createFromFormData')
                    ->with($data)
                    ->willReturn($context);

                return $form;
            });

        $callCount = 0;
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function ($argument) use (&$callCount, $tokenStorage, $formFactory) {
                if (++$callCount < 2) {
                    $this->assertEquals('security.token_storage', $argument);

                    return $tokenStorage;
                }

                $this->assertEquals('form.factory', $argument);

                return $formFactory;
            });

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $user->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $queue->expects($this->once())
            ->method('getType')
            ->willReturn(QueueEnum::SIMPLE);

        $controller = new QueuedUserController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ValidatorInterface::class),
            $repository,
            $this->createStub(QueuedUserRepositoryInterface::class),
            $handler,
            $this->createStub(LoggerInterface::class),
            $this->createStub(LeaveQueueHandler::class),
            $this->createStub(PopQueueHandler::class),
            $contextFactory,
        );

        $controller->setContainer($container);

        $this->expectException(NotFoundHttpException::class);

        $controller->create($queueName, $request);
    }

    #[Test]
    public function itShouldLogCriticalIfHandlerThrowsUnableToJoinQueueException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndWorkspace')
            ->with($queueName = 'queueName', $workspace = $this->createStub(Workspace::class))
            ->willReturn($queue = $this->createMock(Queue::class));

        $containerHasCallCount = 0;
        $container->expects($this->exactly(2))
            ->method('has')
            ->willReturnCallback(function ($argument) use (&$containerHasCallCount) {
                if (++$containerHasCallCount < 2) {
                    $this->assertEquals('security.token_storage', $argument);

                    return true;
                }

                $this->assertEquals('serializer', $argument);

                return false;
            });

        $user = $this->createMock(User::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('submit')
            ->with([])
            ->willReturnSelf();

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $request = new Request();

        $context = $this->createStub(JoinQueueContext::class);
        $contextFactory = $this->createMock(JoinQueueContextFactory::class);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($context)
            ->willThrowException($exception = $this->createStub(UnableToJoinQueueException::class));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($type, $data, $options) use ($queue, $queueName, $user, $form, $context, $contextFactory) {
                $this->assertEquals(QueuedUserType::class, $type);
                $this->assertInstanceOf(QueuedUserData::class, $data);
                $this->assertEquals($queue, $data->getQueue());
                $this->assertEquals($queueName, $data->getQueueName());
                $this->assertEquals($user, $data->getUser());
                $this->assertIsArray($options);
                $this->assertArrayHasKey('queue', $options);
                $this->assertEquals($queue, $options['queue']);

                $contextFactory->expects($this->once())
                    ->method('createFromFormData')
                    ->with($data)
                    ->willReturn($context);

                return $form;
            });

        $callCount = 0;
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function ($argument) use (&$callCount, $tokenStorage, $formFactory) {
                if (++$callCount < 2) {
                    $this->assertEquals('security.token_storage', $argument);

                    return $tokenStorage;
                }

                $this->assertEquals('form.factory', $argument);

                return $formFactory;
            });

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $user->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $queue->expects($this->once())
            ->method('getType')
            ->willReturn(QueueEnum::SIMPLE);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with('Join queue handler threw exception {exception} while all issues should have been caught by validation.', [
                'exception' => $exception::class,
            ]);

        $controller = new QueuedUserController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ValidatorInterface::class),
            $repository,
            $this->createStub(QueuedUserRepositoryInterface::class),
            $handler,
            $logger,
            $this->createStub(LeaveQueueHandler::class),
            $this->createStub(PopQueueHandler::class),
            $contextFactory,
        );

        $controller->setContainer($container);

        $controller->create($queueName, $request);
    }

    #[Test]
    public function itShouldLogCriticalIfHandlerThrowsDeploymentInformationRequiredException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndWorkspace')
            ->with($queueName = 'queueName', $workspace = $this->createStub(Workspace::class))
            ->willReturn($queue = $this->createMock(Queue::class));

        $containerHasCallCount = 0;
        $container->expects($this->exactly(2))
            ->method('has')
            ->willReturnCallback(function ($argument) use (&$containerHasCallCount) {
                if (++$containerHasCallCount < 2) {
                    $this->assertEquals('security.token_storage', $argument);

                    return true;
                }

                $this->assertEquals('serializer', $argument);

                return false;
            });

        $user = $this->createMock(User::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('submit')
            ->with([])
            ->willReturnSelf();

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $request = new Request();

        $context = $this->createStub(JoinQueueContext::class);
        $contextFactory = $this->createMock(JoinQueueContextFactory::class);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($context)
            ->willThrowException($exception = $this->createStub(DeploymentInformationRequiredException::class));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($type, $data, $options) use ($queue, $queueName, $user, $form, $context, $contextFactory) {
                $this->assertEquals(QueuedUserType::class, $type);
                $this->assertInstanceOf(QueuedUserData::class, $data);
                $this->assertEquals($queue, $data->getQueue());
                $this->assertEquals($queueName, $data->getQueueName());
                $this->assertEquals($user, $data->getUser());
                $this->assertIsArray($options);
                $this->assertArrayHasKey('queue', $options);
                $this->assertEquals($queue, $options['queue']);

                $contextFactory->expects($this->once())
                    ->method('createFromFormData')
                    ->with($data)
                    ->willReturn($context);

                return $form;
            });

        $callCount = 0;
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function ($argument) use (&$callCount, $tokenStorage, $formFactory) {
                if (++$callCount < 2) {
                    $this->assertEquals('security.token_storage', $argument);

                    return $tokenStorage;
                }

                $this->assertEquals('form.factory', $argument);

                return $formFactory;
            });

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $user->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $queue->expects($this->once())
            ->method('getType')
            ->willReturn(QueueEnum::SIMPLE);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with('Join queue handler threw exception {exception} while all issues should have been caught by validation.', [
                'exception' => $exception::class,
            ]);

        $controller = new QueuedUserController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ValidatorInterface::class),
            $repository,
            $this->createStub(QueuedUserRepositoryInterface::class),
            $handler,
            $logger,
            $this->createStub(LeaveQueueHandler::class),
            $this->createStub(PopQueueHandler::class),
            $contextFactory,
        );

        $controller->setContainer($container);

        $controller->create($queueName, $request);
    }

    #[Test]
    public function itShouldLogCriticalIfHandlerThrowsInvalidDeploymentUrlException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndWorkspace')
            ->with($queueName = 'queueName', $workspace = $this->createStub(Workspace::class))
            ->willReturn($queue = $this->createMock(Queue::class));

        $containerHasCallCount = 0;
        $container->expects($this->exactly(2))
            ->method('has')
            ->willReturnCallback(function ($argument) use (&$containerHasCallCount) {
                if (++$containerHasCallCount < 2) {
                    $this->assertEquals('security.token_storage', $argument);

                    return true;
                }

                $this->assertEquals('serializer', $argument);

                return false;
            });

        $user = $this->createMock(User::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('submit')
            ->with([])
            ->willReturnSelf();

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $request = new Request();

        $context = $this->createStub(JoinQueueContext::class);
        $contextFactory = $this->createMock(JoinQueueContextFactory::class);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($context)
            ->willThrowException($exception = $this->createStub(InvalidDeploymentUrlException::class));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($type, $data, $options) use ($queue, $queueName, $user, $form, $context, $contextFactory) {
                $this->assertEquals(QueuedUserType::class, $type);
                $this->assertInstanceOf(QueuedUserData::class, $data);
                $this->assertEquals($queue, $data->getQueue());
                $this->assertEquals($queueName, $data->getQueueName());
                $this->assertEquals($user, $data->getUser());
                $this->assertIsArray($options);
                $this->assertArrayHasKey('queue', $options);
                $this->assertEquals($queue, $options['queue']);

                $contextFactory->expects($this->once())
                    ->method('createFromFormData')
                    ->with($data)
                    ->willReturn($context);

                return $form;
            });

        $callCount = 0;
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function ($argument) use (&$callCount, $tokenStorage, $formFactory) {
                if (++$callCount < 2) {
                    $this->assertEquals('security.token_storage', $argument);

                    return $tokenStorage;
                }

                $this->assertEquals('form.factory', $argument);

                return $formFactory;
            });

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $user->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $queue->expects($this->once())
            ->method('getType')
            ->willReturn(QueueEnum::SIMPLE);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with('Join queue handler threw exception {exception} while all issues should have been caught by validation.', [
                'exception' => $exception::class,
            ]);

        $controller = new QueuedUserController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ValidatorInterface::class),
            $repository,
            $this->createStub(QueuedUserRepositoryInterface::class),
            $handler,
            $logger,
            $this->createStub(LeaveQueueHandler::class),
            $this->createStub(PopQueueHandler::class),
            $contextFactory,
        );

        $controller->setContainer($container);

        $controller->create($queueName, $request);
    }

    #[Test]
    public function itShouldLogCriticalIfPopQueueHandlerThrowsPopQueueInformationRequiredException(): void
    {
        $containerHasCallCount = 0;
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')
            ->willReturnCallback(function ($argument) use (&$containerHasCallCount) {
                if (++$containerHasCallCount < 2) {
                    $this->assertEquals('security.token_storage', $argument);

                    return true;
                }

                $this->assertEquals('serializer', $argument);

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
            ->method('getSlackId')
            ->willReturn($userSlackId = 'userSlackId');

        $user->expects($this->exactly(2))
            ->method('isAdministrator')
            ->willReturn(true);

        $user->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createMock(Workspace::class));

        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn($workspaceSlackId = 'workspaceSlackId');

        $queueName = 'queueName';
        $id = 1;

        $repository = $this->createMock(QueuedUserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByIdQueueNameAndWorkspace')
            ->with($id, $queueName, $workspace)
            ->willReturn($this->createStub(QueuedUser::class));

        $exception = $this->createStub(PopQueueInformationRequiredException::class);

        $popQueueHandler = $this->createMock(PopQueueHandler::class);
        $popQueueHandler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function ($context) use ($queueName, $workspaceSlackId, $userSlackId, $id, $exception) {
                $this->assertInstanceOf(PopQueueContext::class, $context);

                $this->assertEquals($queueName, $context->getQueueIdentifier());
                $this->assertEquals($workspaceSlackId, $context->getTeamId());
                $this->assertEquals($userSlackId, $context->getUserId());
                $this->assertEquals($id, $context->getQueuedUserId());

                throw $exception;
            });

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with('Handler threw {exception} while all issues should have been caught by validation.', [
                'exception' => $exception::class,
            ]);

        $controller = new QueuedUserController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ValidatorInterface::class),
            $this->createStub(QueueRepositoryInterface::class),
            $repository,
            $this->createStub(JoinQueueHandler::class),
            $logger,
            $this->createStub(LeaveQueueHandler::class),
            $popQueueHandler,
            $this->createStub(JoinQueueContextFactory::class),
        );

        $controller->setContainer($container);

        $result = $controller->delete($queueName, $id);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $result->getStatusCode());
    }
}
