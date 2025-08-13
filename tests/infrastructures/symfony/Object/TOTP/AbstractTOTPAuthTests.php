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

namespace Teknoo\Tests\East\CommonBundle\Object\TOTP;

use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractTOTPAuthTests extends TestCase
{
    abstract protected function buildUser(): UserWithTOTPAuthInterface;

    public function testSetTOTPAuth(): void
    {
        $this->assertInstanceOf(
            UserWithTOTPAuthInterface::class,
            $this->buildUser()->setTOTPAuth(
                $this->createMock(TOTPAuth::class),
            )
        );
    }

    public function testGetTOTPAuth(): void
    {
        $user = $this->buildUser();
        $this->assertNotInstanceOf(
            \Teknoo\East\Common\Object\TOTPAuth::class,
            $user->getTOTPAuth()
        );
        $user->setTOTPAuth(
            $this->createMock(TOTPAuth::class),
        );
        $this->assertInstanceOf(
            TOTPAuth::class,
            $user->getTOTPAuth()
        );
    }

    public function testIsGoogleAuthenticatorEnabled(): void
    {
        $user = $this->buildUser();
        $this->assertFalse($user->isGoogleAuthenticatorEnabled());
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        $this->assertFalse($user->isGoogleAuthenticatorEnabled());
        $totpAuth->setProvider(TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR);
        $this->assertFalse($user->isGoogleAuthenticatorEnabled());
        $totpAuth->setTopSecret('foo');
        $this->assertFalse($user->isGoogleAuthenticatorEnabled());
        $totpAuth->setEnabled(true);
        $this->assertTrue($user->isGoogleAuthenticatorEnabled());
    }

    public function testGetGoogleAuthenticatorSecret(): void
    {
        $user = $this->buildUser();
        $this->assertEmpty($user->getGoogleAuthenticatorSecret());
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        $this->assertEmpty($user->getGoogleAuthenticatorSecret());
        $totpAuth->setTopSecret('foo');
        $this->assertEquals('foo', $user->getGoogleAuthenticatorSecret());
    }

    public function testGetGoogleAuthenticatorUsername(): void
    {
        $user = $this->buildUser();
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        $this->assertIsString($user->getGoogleAuthenticatorUsername());
    }

    public function testIsTotpAuthenticationEnabled(): void
    {
        $user = $this->buildUser();
        $this->assertFalse($user->isTotpAuthenticationEnabled());
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        $this->assertFalse($user->isTotpAuthenticationEnabled());
        $totpAuth->setProvider('foo');
        $this->assertFalse($user->isTotpAuthenticationEnabled());
        $totpAuth->setTopSecret('foo');
        $this->assertFalse($user->isTotpAuthenticationEnabled());
        $totpAuth->setEnabled(true);
        $this->assertTrue($user->isTotpAuthenticationEnabled());
    }

    public function testGetTotpAuthenticationUsername(): void
    {
        $user = $this->buildUser();
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        $this->assertIsString($user->getTotpAuthenticationUsername());
    }

    public function testGetTotpAuthenticationConfiguration(): void
    {
        $user = $this->buildUser();
        $this->assertEmpty($user->getTotpAuthenticationConfiguration());
        $totpAuth = new TOTPAuth(
            provider: 'foo',
            topSecret: '',
            enabled: false,
        );
        $user->setTOTPAuth($totpAuth);
        $this->assertEmpty($user->getTotpAuthenticationConfiguration());
        $totpAuth->setTopSecret('foo');
        $this->assertInstanceOf(
            TotpConfiguration::class,
            $user->getTotpAuthenticationConfiguration()
        );
    }
}
