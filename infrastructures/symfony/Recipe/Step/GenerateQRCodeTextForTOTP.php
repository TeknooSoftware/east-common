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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use UnexpectedValueException;

/**
 * Step to generate QRCode value as string, to build in a next step the QRCOde image.
 * The QRCode is generated thanks to Scheb TwoFactorBundle.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class GenerateQRCodeTextForTOTP
{
    use UserTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(
        GoogleAuthenticatorInterface|TotpAuthenticatorInterface $authenticator,
        ManagerInterface $manager,
    ): self {
        $qrCodeValue = null;
        $user = $this->getUser();

        if (
            $user instanceof GoogleTwoFactorInterface
            && $user instanceof UserWithTOTPAuthInterface
            && $authenticator instanceof GoogleAuthenticatorInterface
            && $user->getTOTPAuth()
        ) {
            $qrCodeValue = $authenticator->getQRContent($user);
        }

        if (
            $user instanceof TotpTwoFactorInterface
            && $user instanceof UserWithTOTPAuthInterface
            && $authenticator instanceof TotpAuthenticatorInterface
            && $user->getTOTPAuth()
        ) {
            $qrCodeValue = $authenticator->getQRContent($user);
        }

        if (empty($qrCodeValue)) {
            throw new UnexpectedValueException("QRCode can not be generated");
        }

        $manager->updateWorkPlan([
           'qrCodeValue' => $qrCodeValue,
        ]);

        return $this;
    }
}
