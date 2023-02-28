<?php

/*
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

namespace Teknoo\East\CommonBundle\Object\TOTP;

use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Teknoo\East\Common\Object\TOTPAuth;

/**
 * Trait to manage TOTP interfaces implementation for Scheb\TwoFactorBundle\*\TwoFactorInterface
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait TOTPAuthTrait
{
    private ?TOTPAuth $totpAuth = null;

    public function setTOTPAuth(?TOTPAuth $TOTPAuth): self
    {
        $this->totpAuth = $TOTPAuth;

        return $this;
    }

    public function getTOTPAuth(): ?TOTPAuth
    {
        return $this->totpAuth;
    }

    /**
     * Return true if the user should do two-factor authentication.
     */
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR === $this->totpAuth?->getProvider()
            && !empty($this->totpAuth->getTopSecret())
            && true === $this->totpAuth->isEnabled();
    }

    /**
     * Return the user name.
     */
    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * Return the Google Authenticator secret
     * When an empty string is returned, the Google authentication is disabled.
     */
    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->totpAuth?->getTopSecret();
    }

    /**
     * Return true if the user should do TOTP authentication.
     */
    public function isTotpAuthenticationEnabled(): bool
    {
        return null !== $this->totpAuth
            && TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR !== $this->totpAuth->getProvider()
            && !empty($this->totpAuth->getTopSecret())
            && true === $this->totpAuth->isEnabled();
    }

    /**
     * Return the user name.
     */
    public function getTotpAuthenticationUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * Return the configuration for TOTP authentication.
     */
    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        if (
            null === $this->totpAuth
            || empty($secret = $this->totpAuth->getTopSecret())
        ) {
            return null;
        }

        return new TotpConfiguration(
            secret: $secret,
            algorithm: $this->totpAuth->getAlgorithm(),
            period: $this->totpAuth->getPeriod(),
            digits: $this->totpAuth->getDigits(),
        );
    }
}
