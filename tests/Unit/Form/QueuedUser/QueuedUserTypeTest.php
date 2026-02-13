<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\QueuedUser;

use App\Entity\Queue;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\Queue as QueueEnum;
use App\Form\QueuedUser\QueuedUserType;
use App\Validator\Constraint\CanJoinQueue;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

#[CoversClass(QueuedUserType::class)]
class QueuedUserTypeTest extends KernelTestCase
{
    #[Test]
    public function itShouldAddQueuedUserFields(): void
    {
        $type = new QueuedUserType($tokenStorage = $this->createMock(TokenStorageInterface::class));

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $user->expects($this->once())
            ->method('isAdministrator')
            ->willReturn(true);

        $user->expects($this->exactly(1))
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createMock(Workspace::class));

        $workspace->expects($this->exactly(1))
            ->method('getUsers')
            ->willReturn($users = $this->createMock(Collection::class));

        $users->expects($this->exactly(1))
            ->method('toArray')
            ->willReturn([]);

        $queue = $this->createStub(Queue::class);

        $callCount = 0;
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->exactly(3))
            ->method('add')
            ->willReturnCallback(function ($field, $type, array $options) use (&$callCount, $builder, $user, $queue) {
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
                    $this->assertEquals([QueueEnum::SIMPLE], $options['choices']);

                    $this->assertArrayHasKey('class', $options);
                    $this->assertEquals(QueueEnum::class, $options['class']);

                    $this->assertArrayHasKey('mapped', $options);
                    $this->assertFalse($options['mapped']);

                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertCount(1, $options['constraints']);
                    $this->assertContainsOnlyInstancesOf(NotNull::class, $options['constraints']);
                }

                return $builder;
            });

        $type->buildForm($builder, ['queue' => $queue]);
    }
}
