<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Auth;

use App\Controller\Auth\SecurityController;
use App\Entity\Administrator;
use App\Entity\Workspace;
use App\Service\OAuth\OAuthAccessResponse;
use App\Service\OAuth\OAuthService;
use App\Service\OAuth\Resolver\AdministratorResolver;
use App\Service\OAuth\Resolver\WorkspaceResolver;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SecurityController::class)]
class SecurityControllerTest extends KernelTestCase
{
    #[Test]
    public function itShouldRedirectToUrlProvidedByOAuthService(): void
    {
        $service = $this->createMock(OAuthService::class);
        $service->expects($this->once())
            ->method('getRedirectUrl')
            ->willReturn($redirectUrl = 'redirectUrl');

        $controller = new SecurityController(
            $service,
            $this->createStub(WorkspaceResolver::class),
            $this->createStub(AdministratorResolver::class),
            $this->createStub(EntityManagerInterface::class),
        );

        $response = $controller->redirect();

        $this->assertEquals($response->getTargetUrl(), $redirectUrl);
    }

    #[Test]
    public function itShouldPersistGivenWorkspaceAndAdministratorsAndReturnRedirectResponse(): void
    {
        $request = new Request();
        $request->query->set('code', $authorisationCode = 'code');

        $serviceResponse = $this->createMock(OAuthAccessResponse::class);
        $serviceResponse->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $serviceResponse->expects($this->once())
            ->method('getBotChannelId')
            ->willReturn($botChannelId = 'botChannelId');

        $workspace = $this->createStub(Workspace::class);

        $workspaceResolver = $this->createMock(WorkspaceResolver::class);
        $workspaceResolver->expects($this->once())
            ->method('resolve')
            ->with($serviceResponse)
            ->willReturn($workspace);

        $administrator = $this->createStub(Administrator::class);

        $administratorResolver = $this->createMock(AdministratorResolver::class);
        $administratorResolver->expects($this->once())
            ->method('resolve')
            ->with($serviceResponse, $workspace)
            ->willReturn($administrator);

        $service = $this->createMock(OAuthService::class);
        $service->expects($this->once())
            ->method('authorise')
            ->with($authorisationCode)
            ->willReturn($serviceResponse);

        $callCount = 0;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$callCount, $workspace, $administrator) {
                if (1 === ++$callCount) {
                    $this->assertInstanceOf(Workspace::class, $entity);
                    $this->assertSame($workspace, $entity);

                    return;
                }

                $this->assertInstanceOf(Administrator::class, $entity);
                $this->assertSame($administrator, $entity);
            });

        $entityManager->expects($this->exactly(2))
            ->method('flush')
            ->with();

        $controller = new SecurityController(
            $service,
            $workspaceResolver,
            $administratorResolver,
            $entityManager,
        );

        $response = $controller->callback($request);

        $this->assertEquals(
            'https://app.slack.com/client/'.$teamId.'/'.$botChannelId,
            $response->getTargetUrl()
        );
    }
}
