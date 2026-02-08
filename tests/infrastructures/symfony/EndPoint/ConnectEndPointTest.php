<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\EndPoint;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Session\SessionInterface;
use Teknoo\East\CommonBundle\Command\CreateUserCommand;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\CommonBundle\EndPoint\ConnectEndPoint;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ConnectEndPoint::class)]
class ConnectEndPointTest extends TestCase
{
    private ?ClientRegistry $clientRegistry = null;

    public function getClientRegistry(bool $stub = false): (ClientRegistry&Stub)|(ClientRegistry&MockObject)
    {
        if (!$this->clientRegistry instanceof ClientRegistry) {
            if ($stub) {
                $this->clientRegistry = $this->createStub(ClientRegistry::class);
            } else {
                $this->clientRegistry = $this->createMock(ClientRegistry::class);
            }
        }

        return $this->clientRegistry;
    }

    public function buildEndPoint(): ConnectEndPoint
    {
        $endpoint = new ConnectEndPoint(
            $this->getClientRegistry(true),
            'foo',
            ['bar']
        );

        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('withHeader')
            ->willReturnSelf();

        $factory = $this->createStub(ResponseFactoryInterface::class);
        $factory
            ->method('createResponse')
            ->willReturn($response);

        $endpoint->setResponseFactory($factory);
        $endpoint->setRouter($this->createStub(UrlGeneratorInterface::class));

        return $endpoint;
    }

    public function testInvokeWithSession(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $client = $this->createStub(ClientInterface::class);
        $session = $this->createStub(SessionInterface::class);

        $oauthClient = $this->createStub(OAuth2ClientInterface::class);
        $this->getClientRegistry(true)
            ->method('getClient')
            ->willReturn($oauthClient);

        $provider = $this->createStub(AbstractProvider::class);
        $oauthClient
            ->method('getOAuth2Provider')
            ->willReturn($provider);

        $provider
            ->method('getAuthorizationUrl')
            ->willReturn('/foo/bar');

        $provider
            ->method('getState')
            ->willReturn('/foo/bar');

        $this->assertInstanceOf(
            ConnectEndPoint::class,
            $this->buildEndPoint()($request, $client, $session)
        );
    }

    public function testInvokeWithoutSession(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $client = $this->createStub(ClientInterface::class);

        $oauthClient = $this->createStub(OAuth2ClientInterface::class);
        $this->getClientRegistry(true)
            ->method('getClient')
            ->willReturn($oauthClient);

        $provider = $this->createStub(AbstractProvider::class);
        $oauthClient
            ->method('getOAuth2Provider')
            ->willReturn($provider);

        $provider
            ->method('getAuthorizationUrl')
            ->willReturn('/foo/bar');

        $provider
            ->method('getState')
            ->willReturn('/foo/bar');

        $this->assertInstanceOf(
            ConnectEndPoint::class,
            $this->buildEndPoint()($request, $client)
        );
    }
}
