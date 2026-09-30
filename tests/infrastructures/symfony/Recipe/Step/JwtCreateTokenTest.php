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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\DTO\JWTConfiguration;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Recipe\Step\Exception\MissingPackageException;
use Teknoo\East\CommonBundle\Recipe\Step\JwtCreateToken;
use Teknoo\East\Foundation\Time\DatesService;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(JwtCreateToken::class)]
class JwtCreateTokenTest extends TestCase
{
    private (JWTTokenManagerInterface&Stub)|(JWTTokenManagerInterface&MockObject)|null $jwtManager = null;

    private (TokenStorageInterface&Stub)|null $tokenStorage = null;

    private function getJwtManager(
        bool $stub = false,
    ): (JWTTokenManagerInterface&Stub)|(JWTTokenManagerInterface&MockObject) {
        if (!$this->jwtManager instanceof JWTTokenManagerInterface) {
            if ($stub) {
                $this->jwtManager = $this->createStub(JWTTokenManagerInterface::class);
            } else {
                $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
            }
        }

        return $this->jwtManager;
    }

    private function getTokenStorage(): TokenStorageInterface&Stub
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface) {
            $this->tokenStorage = $this->createStub(TokenStorageInterface::class);
        }

        return $this->tokenStorage;
    }

    private function buildStep(): JwtCreateToken
    {
        return new JwtCreateToken(
            $this->getJwtManager(true),
            $this->getTokenStorage(),
            new DatesService()->setCurrentDate(new DateTimeImmutable('2024-01-01 00:00:00')),
            30,
        );
    }

    private function prepareToken(): AbstractUser&Stub
    {
        $symfonyUser = $this->createStub(AbstractUser::class);
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($symfonyUser);

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        return $symfonyUser;
    }

    public function testInvokeWithoutJwtManager(): void
    {
        $step = new JwtCreateToken(
            null,
            $this->getTokenStorage(),
            new DatesService(),
        );

        $this->expectException(MissingPackageException::class);
        $step(
            $this->createStub(ParametersBag::class),
            new JWTConfiguration(),
        );
    }

    public function testInvokeWithoutToken(): void
    {
        $this->getJwtManager()
            ->expects($this->never())
            ->method('createFromPayload');

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn(null);

        $step = $this->buildStep();
        $this->assertSame(
            $step,
            $step(
                $this->createStub(ParametersBag::class),
                new JWTConfiguration(),
            )
        );
    }

    public function testInvokeWithNotAnAbstractUserInToken(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($this->createStub(UserInterface::class));

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->getJwtManager()
            ->expects($this->never())
            ->method('createFromPayload');

        $step = $this->buildStep();
        $this->assertSame(
            $step,
            $step(
                $this->createStub(ParametersBag::class),
                new JWTConfiguration(),
            )
        );
    }

    public function testInvokeWithoutExpirationDate(): void
    {
        $symfonyUser = $this->prepareToken();

        $this->getJwtManager()
            ->expects($this->once())
            ->method('createFromPayload')
            ->with(
                $symfonyUser,
                ['exp' => new DateTimeImmutable('2024-01-31 00:00:00')->getTimestamp()],
            )
            ->willReturn('jwt-token');

        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with('jwtToken', 'jwt-token')
            ->willReturnSelf();

        $step = $this->buildStep();
        $this->assertSame(
            $step,
            $step(
                $bag,
                new JWTConfiguration(expirationDate: null),
            )
        );
    }

    public function testInvokeWithExpirationDate(): void
    {
        $symfonyUser = $this->prepareToken();

        $this->getJwtManager()
            ->expects($this->once())
            ->method('createFromPayload')
            ->with(
                $symfonyUser,
                ['exp' => new DateTimeImmutable('2024-01-02 00:00:00')->getTimestamp()],
            )
            ->willReturn('jwt-token');

        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with('jwtToken', 'jwt-token')
            ->willReturnSelf();

        $step = $this->buildStep();
        $this->assertSame(
            $step,
            $step(
                $bag,
                new JWTConfiguration(expirationDate: new DateTimeImmutable('2024-01-02 00:00:00')),
            )
        );
    }

    public function testInvokeWithExpirationDateAfterTheMaximumAllowed(): void
    {
        $symfonyUser = $this->prepareToken();

        $this->getJwtManager()
            ->expects($this->once())
            ->method('createFromPayload')
            ->with(
                $symfonyUser,
                ['exp' => new DateTimeImmutable('2024-01-31 00:00:00')->getTimestamp()],
            )
            ->willReturn('jwt-token');

        $step = $this->buildStep();
        $this->assertSame(
            $step,
            $step(
                new ParametersBag(),
                new JWTConfiguration(expirationDate: new DateTimeImmutable('2025-01-01 00:00:00')),
            )
        );
    }
}
