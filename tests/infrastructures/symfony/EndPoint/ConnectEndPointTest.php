<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\EndPoint;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use PHPUnit\Framework\Attributes\CoversClass;
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ConnectEndPoint::class)]
class ConnectEndPointTest extends TestCase
{
    private ?ClientRegistry $clientRegistry = null;

    /**
     * @return ClientRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getClientRegistry(): ClientRegistry
    {
        if (!$this->clientRegistry instanceof ClientRegistry) {
            $this->clientRegistry = $this->createMock(ClientRegistry::class);
        }

        return $this->clientRegistry;
    }

    public function buildEndPoint(): ConnectEndPoint
    {
        $endpoint = new ConnectEndPoint(
            $this->getClientRegistry(),
            'foo',
            ['bar']
        );

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())
            ->method('withHeader')
            ->willReturnSelf();

        $factory = $this->createMock(ResponseFactoryInterface::class);
        $factory->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $endpoint->setResponseFactory($factory);
        $endpoint->setRouter($this->createMock(UrlGeneratorInterface::class));

        return $endpoint;
    }

    public function testInvokeWithSession()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $oauthClient = $this->createMock(OAuth2ClientInterface::class);
        $this->getClientRegistry()
            ->expects($this->any())
            ->method('getClient')
            ->with('foo')
            ->willReturn($oauthClient);

        $provider = $this->createMock(AbstractProvider::class);
        $oauthClient->expects($this->any())
            ->method('getOAuth2Provider')
            ->willReturn($provider);

        $provider->expects($this->any())
            ->method('getAuthorizationUrl')
            ->willReturn('/foo/bar');

        $provider->expects($this->any())
            ->method('getState')
            ->willReturn('/foo/bar');

        self::assertInstanceOf(
            ConnectEndPoint::class,
            $this->buildEndPoint()($request, $client, $session)
        );
    }

    public function testInvokeWithoutSession()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $oauthClient = $this->createMock(OAuth2ClientInterface::class);
        $this->getClientRegistry()
            ->expects($this->any())
            ->method('getClient')
            ->with('foo')
            ->willReturn($oauthClient);

        $provider = $this->createMock(AbstractProvider::class);
        $oauthClient->expects($this->any())
            ->method('getOAuth2Provider')
            ->willReturn($provider);

        $provider->expects($this->any())
            ->method('getAuthorizationUrl')
            ->willReturn('/foo/bar');

        $provider->expects($this->any())
            ->method('getState')
            ->willReturn('/foo/bar');

        self::assertInstanceOf(
            ConnectEndPoint::class,
            $this->buildEndPoint()($request, $client)
        );
    }
}
