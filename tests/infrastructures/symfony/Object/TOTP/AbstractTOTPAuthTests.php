<?php

/**
 * East Common.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Object\TOTP;

use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractTOTPAuthTests extends TestCase
{
    abstract protected function buildUser(): UserWithTOTPAuthInterface;

    public function testSetTOTPAuth()
    {
        self::assertInstanceOf(
            UserWithTOTPAuthInterface::class,
            $this->buildUser()->setTOTPAuth(
                $this->createMock(TOTPAuth::class),
            )
        );
    }

    public function testGetTOTPAuth()
    {
        $user = $this->buildUser();
        self::assertEmpty(
            $user->getTOTPAuth()
        );
        $user->setTOTPAuth(
            $this->createMock(TOTPAuth::class),
        );
        self::assertInstanceOf(
            TOTPAuth::class,
            $user->getTOTPAuth()
        );
    }

    public function testIsGoogleAuthenticatorEnabled()
    {
        $user = $this->buildUser();
        self::assertFalse($user->isGoogleAuthenticatorEnabled());
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        self::assertFalse($user->isGoogleAuthenticatorEnabled());
        $totpAuth->setProvider(TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR);
        self::assertFalse($user->isGoogleAuthenticatorEnabled());
        $totpAuth->setTopSecret('foo');
        self::assertFalse($user->isGoogleAuthenticatorEnabled());
        $totpAuth->setEnabled(true);
        self::assertTrue($user->isGoogleAuthenticatorEnabled());
    }

    public function testGetGoogleAuthenticatorSecret()
    {
        $user = $this->buildUser();
        self::assertEmpty($user->getGoogleAuthenticatorSecret());
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        self::assertEmpty($user->getGoogleAuthenticatorSecret());
        $totpAuth->setTopSecret('foo');
        self::assertEquals('foo', $user->getGoogleAuthenticatorSecret());
    }

    public function testGetGoogleAuthenticatorUsername()
    {
        $user = $this->buildUser();
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        self::assertIsString($user->getGoogleAuthenticatorUsername());
    }

    public function testIsTotpAuthenticationEnabled()
    {
        $user = $this->buildUser();
        self::assertFalse($user->isTotpAuthenticationEnabled());
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        self::assertFalse($user->isTotpAuthenticationEnabled());
        $totpAuth->setProvider('foo');
        self::assertFalse($user->isTotpAuthenticationEnabled());
        $totpAuth->setTopSecret('foo');
        self::assertFalse($user->isTotpAuthenticationEnabled());
        $totpAuth->setEnabled(true);
        self::assertTrue($user->isTotpAuthenticationEnabled());
    }

    public function testGetTotpAuthenticationUsername()
    {
        $user = $this->buildUser();
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        self::assertIsString($user->getTotpAuthenticationUsername());
    }

    public function testGetTotpAuthenticationConfiguration()
    {
        $user = $this->buildUser();
        self::assertEmpty($user->getTotpAuthenticationConfiguration());
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        self::assertEmpty($user->getTotpAuthenticationConfiguration());
        $totpAuth->setTopSecret('foo');
        self::assertInstanceOf(
            TotpConfiguration::class,
            $user->getTotpAuthenticationConfiguration()
        );
    }
}