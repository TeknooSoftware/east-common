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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Object;

use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Common\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\CommonBundle\Object\AbstractUser
 * @covers      \Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser
 */
class ThirdPartyAuthenticatedUserTest extends AbstractUserTests
{
    private ?BaseUser $user = null;

    private ?ThirdPartyAuth $thirdPartyAuth = null;

    /**
     * @return BaseUser|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getUser(): BaseUser
    {
        if (!$this->user instanceof BaseUser) {
            $this->user = $this->createMock(BaseUser::class);

            $this->user->expects(self::any())->method('getAuthData')->willReturn([$this->getThirdPartyAuth()]);
        }

        return $this->user;
    }

    /**
     * @return ThirdPartyAuth|\PHPUnit\Framework\MockObject\MockObject
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

    public function testExceptionWithBadUser()
    {
        $this->expectException(\TypeError::class);
        new PasswordAuthenticatedUser(new \stdClass(), $this->getThirdPartyAuth());
    }

    public function testExceptionWithBadThirdPartyAuth()
    {
        $this->expectException(\TypeError::class);
        new PasswordAuthenticatedUser($this->getUser(), new \stdClass());
    }

    public function testGetWrappedThirdAuth()
    {
        self::assertInstanceOf(
            ThirdPartyAuth::class,
            $this->buildObject()->getWrappedThirdAuth()
        );
    }

    public function testGetPassword()
    {
        $this->getThirdPartyAuth()
            ->expects(self::once())
            ->method('getToken')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            $this->buildObject()->getPassword()
        );
    }

    public function testEraseCredentials()
    {
        self::assertInstanceOf(
            ThirdPartyAuthenticatedUser::class,
            $this->buildObject()->eraseCredentials()
        );
    }

}
