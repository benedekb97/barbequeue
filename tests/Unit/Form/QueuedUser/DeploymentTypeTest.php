<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\QueuedUser;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\Queue as QueueEnum;
use App\Form\QueuedUser\DeploymentType;
use App\Validator\Constraint\CanJoinQueue;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Url;

#[CoversClass(DeploymentType::class)]
class DeploymentTypeTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowInvalidArgumentExceptionIfQueueNotInOptions(): void
    {
        $type = new DeploymentType($this->createStub(TokenStorageInterface::class));

        $this->expectException(\InvalidArgumentException::class);

        $type->buildForm(
            $this->createStub(FormBuilderInterface::class),
            []
        );
    }

    #[Test]
    public function itShouldThrowInvalidArgumentExceptionIfQueueOptionNotQueue(): void
    {
        $type = new DeploymentType($this->createStub(TokenStorageInterface::class));

        $this->expectException(\InvalidArgumentException::class);

        $type->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['queue' => 'not-a-queue']
        );
    }

    #[Test]
    public function itShouldThrowUnauthorizedHttpExceptionIfTokenStorageDoesNotReturnToken(): void
    {
        $type = new DeploymentType($tokenStorage = $this->createMock(TokenStorageInterface::class));

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);

        $type->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['queue' => $this->createStub(Queue::class)]
        );
    }

    #[Test]
    public function itShouldSetQueueAsRequired(): void
    {
        $type = new DeploymentType($this->createStub(TokenStorageInterface::class));

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setRequired')
            ->with(['queue']);

        $type->configureOptions($resolver);
    }

    #[Test]
    public function itShouldThrowInvalidArgumentExceptionIfQueueNotDeploymentQueue(): void
    {
        $type = new DeploymentType($tokenStorage = $this->createMock(TokenStorageInterface::class));

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createStub(User::class));

        $this->expectException(\InvalidArgumentException::class);

        $type->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['queue' => $this->createStub(Queue::class)]
        );
    }

    #[Test]
    public function itShouldAddDeploymentFields(): void
    {
        $type = new DeploymentType($tokenStorage = $this->createMock(TokenStorageInterface::class));

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $queue = $this->createMock(DeploymentQueue::class);
        $user->expects($this->once())
            ->method('isAdministrator')
            ->willReturn(true);

        $user->expects($this->exactly(2))
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createMock(Workspace::class));

        $workspace->expects($this->exactly(2))
            ->method('getUsers')
            ->willReturn($users = $this->createMock(Collection::class));

        $users->expects($this->exactly(2))
            ->method('toArray')
            ->willReturn([]);

        $queue->expects($this->once())
            ->method('getRepositories')
            ->willReturn($repositories = $this->createStub(Collection::class));

        $callCount = 0;
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->exactly(7))
            ->method('add')
            ->willReturnCallback(function ($field, $type, array $options) use (&$callCount, $builder, $user, $queue, $repositories) {
                if (1 === ++$callCount) {
                    $this->assertEquals('expiryMinutes', $field);
                    $this->assertEquals(NumberType::class, $type);
                    $this->assertCount(2, $options);
                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertContainsOnlyInstancesOf(Positive::class, $options['constraints']);

                    $this->assertArrayHasKey('required', $options);
                    $this->assertFalse($options['required']);
                }

                if (2 === $callCount) {
                    $this->assertEquals('user', $field);
                    $this->assertEquals(EntityType::class, $type);

                    $this->assertCount(4, $options);
                    $this->assertArrayHasKey('class', $options);
                    $this->assertEquals(User::class, $options['class']);

                    $this->assertArrayHaskey('choices', $options);
                    $this->assertEquals([], $options['choices']);

                    $this->assertArrayHaskey('empty_data', $options);
                    $this->assertEquals($user, $options['empty_data']);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(2, $options['constraints']);

                    $constraint = $options['constraints'][0];

                    $this->assertInstanceOf(CanJoinQueue::class, $constraint);
                    $this->assertEquals($user, $constraint->currentUser);
                    $this->assertEquals($queue, $constraint->queue);

                    $this->assertInstanceOf(NotNull::class, $options['constraints'][1]);
                }

                if (3 === $callCount) {
                    $this->assertEquals('type', $field);
                    $this->assertEquals(EnumType::class, $type);
                    $this->assertCount(4, $options);
                    $this->assertArrayHasKey('choices', $options);
                    $this->assertEquals([QueueEnum::DEPLOYMENT], $options['choices']);

                    $this->assertArrayHasKey('class', $options);
                    $this->assertEquals(QueueEnum::class, $options['class']);

                    $this->assertArrayHasKey('mapped', $options);
                    $this->assertFalse($options['mapped']);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertContainsOnlyInstancesOf(NotNull::class, $options['constraints']);
                }

                if (4 === $callCount) {
                    $this->assertEquals('repository', $field);
                    $this->assertEquals(EntityType::class, $type);

                    $this->assertCount(3, $options);
                    $this->assertArrayHasKey('choices', $options);
                    $this->assertEquals($repositories, $options['choices']);

                    $this->assertArrayHasKey('class', $options);
                    $this->assertEquals(Repository::class, $options['class']);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertContainsOnlyInstancesOf(NotNull::class, $options['constraints']);
                }

                if (5 === $callCount) {
                    $this->assertEquals('description', $field);
                    $this->assertEquals(TextType::class, $type);
                    $this->assertCount(1, $options);
                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertContainsOnlyInstancesOf(NotBlank::class, $options['constraints']);
                }

                if (6 === $callCount) {
                    $this->assertEquals('link', $field);
                    $this->assertEquals(UrlType::class, $type);
                    $this->assertCount(1, $options);
                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(2, $options['constraints']);
                    $this->assertContainsNotOnlyInstancesOf(NotBlank::class, $options['constraints']);
                    $this->assertContainsNotOnlyInstancesOf(Url::class, $options['constraints']);
                }

                if (7 === $callCount) {
                    $this->assertEquals('notifyUsers', $field);
                    $this->assertEquals(EntityType::class, $type);
                    $this->assertCount(4, $options);

                    $this->assertArrayHasKey('multiple', $options);
                    $this->assertTrue($options['multiple']);
                    $this->assertArrayHasKey('class', $options);
                    $this->assertEquals(User::class, $options['class']);
                    $this->assertArrayHasKey('choices', $options);
                    $this->assertEquals([], $options['choices']);
                    $this->assertArrayHasKey('required', $options);
                    $this->assertFalse($options['required']);
                }

                return $builder;
            });

        $type->buildForm($builder, ['queue' => $queue]);
    }
}
