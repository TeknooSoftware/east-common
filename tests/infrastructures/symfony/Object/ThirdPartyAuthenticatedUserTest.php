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

namespace Teknoo\Tests\East\CommonBundle\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\Common\Object\User as BaseUser;
use TypeError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ThirdPartyAuthenticatedUser::class)]
#[CoversClass(AbstractUser::class)]
class ThirdPartyAuthenticatedUserTest extends AbstractUserTests
{
    private ?BaseUser $user = null;

    private ?ThirdPartyAuth $thirdPartyAuth = null;

    /**
     * @return BaseUser|MockObject
     */
    #[\Override]
    public function getUser(): BaseUser
    {
        if (!$this->user instanceof BaseUser) {
            $this->user = $this->createMock(BaseUser::class);

            $this->user->method('getAuthData')->willReturn([$this->getThirdPartyAuth()]);
            $this->user->method('getOneAuthData')->willReturn($this->getStoredPassword());
        }

        return $this->user;
    }

    /**
     * @return ThirdPartyAuth|MockObject
     */
    public function getThirdPartyAuth(): ThirdPartyAuth
    {
        if (!$this->thirdPartyAuth instanceof ThirdPartyAuth) {
            $this->thirdPartyAuth = $this->createMock(ThirdPartyAuth::class);
        }

        return $this->thirdPartyAuth;
    }

    public function buildObject(): ThirdPartyAuthenticatedUser
    {
        return new ThirdPartyAuthenticatedUser($this->getUser(), $this->getThirdPartyAuth());
    }

    public function testExceptionWithBadUser(): void
    {
        $this->expectException(TypeError::class);
        new ThirdPartyAuthenticatedUser(new stdClass(), $this->getThirdPartyAuth());
    }

    public function testExceptionWithBadThirdPartyAuth(): void
    {
        $this->expectException(TypeError::class);
        new ThirdPartyAuthenticatedUser($this->getUser(), new stdClass());
    }

    public function testGetWrappedThirdAuth(): void
    {
        $this->assertInstanceOf(
            ThirdPartyAuth::class,
            $this->buildObject()->getWrappedThirdAuth()
        );
    }

    public function testGetPassword(): void
    {
        $this->getThirdPartyAuth()
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            $this->buildObject()->getPassword()
        );
    }

    #[\Override]
    public function testEraseCredentials(): void
    {
        $this->buildObject()->eraseCredentials();
        $this->assertTrue(true);
    }
}
