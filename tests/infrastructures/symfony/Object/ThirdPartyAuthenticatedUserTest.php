<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\Object;

use Teknoo\East\Website\Object\ThirdPartyAuth;
use Teknoo\East\WebsiteBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Object\AbstractUser
 * @covers      \Teknoo\East\WebsiteBundle\Object\ThirdPartyAuthenticatedUser
 */
class ThirdPartyAuthenticatedUserTest extends AbstractUserTest
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
