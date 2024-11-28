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

namespace Teknoo\Tests\East\CommonBundle\Object\TOTP;

use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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