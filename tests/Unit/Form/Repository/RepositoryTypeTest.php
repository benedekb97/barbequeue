<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Repository;

use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Form\Repository\RepositoryType;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

#[CoversClass(RepositoryType::class)]
class RepositoryTypeTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowUnauthorizedHttpExceptionIfUserNotInTokenStorage(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())->method('getToken')->willReturn(null);

        $type = new RepositoryType($tokenStorage);

        $this->expectException(UnauthorizedHttpException::class);

        $type->buildForm($this->createStub(FormBuilderInterface::class), []);
    }

    #[Test]
    public function itShouldAddRepositoryFields(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $user->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createMock(Workspace::class));

        $workspace->expects($this->once())
            ->method('getRepositories')
            ->willReturn($collection = $this->createStub(Collection::class));

        $callCount = 0;
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->exactly(3))
            ->method('add')
            ->willReturnCallback(function (string $field, string $type, array $options) use ($builder, &$callCount, $collection) {
                if (1 === ++$callCount) {
                    $this->assertEquals('name', $field);
                    $this->assertEquals(TextType::class, $type);

                    $this->assertCount(1, $options);
                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertContainsOnlyInstancesOf(NotBlank::class, $options['constraints']);
                }

                if (2 === $callCount) {
                    $this->assertEquals('url', $field);
                    $this->assertEquals(UrlType::class, $type);
                    $this->assertCount(1, $options);
                    $this->assertArrayHasKey('constraints', $options);
                    $this->assertIsArray($options['constraints']);
                    $this->assertContainsOnlyInstancesOf(Url::class, $options['constraints']);
                    $this->assertCount(1, $options['constraints']);

                    /** @var Url $constraint */
                    $constraint = $options['constraints'][0];

                    $this->assertTrue($constraint->requireTld);
                }

                if (3 === $callCount) {
                    $this->assertEquals('deploymentBlocksRepositories', $field);
                    $this->assertEquals(EntityType::class, $type);

                    $this->assertCount(3, $options);

                    $this->assertArrayHasKey('class', $options);
                    $this->assertEquals(Repository::class, $options['class']);

                    $this->assertArrayHasKey('choices', $options);
                    $this->assertEquals($collection, $options['choices']);

                    $this->assertArrayHasKey('multiple', $options);
                    $this->assertTrue($options['multiple']);
                }

                return $builder;
            });

        $type = new RepositoryType($tokenStorage);

        $type->buildForm($builder, []);
    }
}
