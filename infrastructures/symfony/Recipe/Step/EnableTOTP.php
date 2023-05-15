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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Security\Exception\WrongUserException;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;

/**
 * Step to enable TOTP/Google Authenticator for an user. The User must be an East Common bundle user, wrapping
 * an East Common user. The authenticator is pass as ingredient to this step and must be defined before in the
 * workplan. The TOTPAuth instance is injected into ParameterBag after.  The 2FA is only prepared, but not validated
 * until user as confirmed a code with its TOTP app.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class EnableTOTP
{
    use UserTrait;

    public function __construct(
        private readonly SymfonyUserWriter $userWriter,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(
        GoogleAuthenticatorInterface|TotpAuthenticatorInterface $authenticator,
        ParametersBag $bag,
        string $totpAlgorithm = 'sha1',
        int $totpDigits = 6,
        int $totpPeriods = 30,
        string $totpProvider = TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR,
    ): self {
        $user = $this->getUser();
        if (!$user instanceof AbstractUser) {
            throw new WrongUserException('User instance is not a East Common bundle user instance');
        }

        $wrappedUsed = $user->getWrappedUser();
        if (
            (!$user instanceof GoogleTwoFactorInterface && !$user instanceof TotpTwoFactorInterface)
            || ($user instanceof GoogleTwoFactorInterface && !$user->isGoogleAuthenticatorEnabled())
            || ($user instanceof TotpTwoFactorInterface && !$user->isTotpAuthenticationEnabled())
        ) {
            $secret = $authenticator->generateSecret();
            if ($authenticator instanceof GoogleAuthenticatorInterface) {
                $totpProvider = TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR;
            }

            $totpAuth = new TOTPAuth(
                provider: $totpProvider,
                topSecret: $secret,
                algorithm: $totpAlgorithm,
                period: $totpPeriods,
                digits: $totpDigits,
                enabled: false,
            );


            $wrappedUsed->addAuthData($totpAuth);
            $this->userWriter->save($wrappedUsed);

            $bag->set('totpAuth', $totpAuth);
        } else {
            $bag->set('totpAuth', $wrappedUsed->getOneAuthData(TOTPAuth::class));
        }

        $bag->set('user', $wrappedUsed);

        return $this;
    }
}
