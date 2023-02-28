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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use RuntimeException;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;
use Teknoo\East\CommonBundle\Form\Model\TOTP;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;

/**
 * Step to validate from code input by user from its TOTP App and enable 2FA at login for this user
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ValidateTOTP
{
    use UserTrait;

    public function __construct(
        private readonly SymfonyUserWriter $userWriter,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(
        GoogleAuthenticatorInterface|TotpAuthenticatorInterface $authenticator,
        TOTP $object,
    ): self {
        $user = $this->getUser();

        if (
            !$user instanceof UserWithTOTPAuthInterface
            || (
                !$user instanceof GoogleTwoFactorInterface
                && !$user instanceof TotpTwoFactorInterface
            )
        ) {
            throw new RuntimeException('User instance is not a East Common bundle user instance');
        }

        $validated = $authenticator->checkCode($user, $object->code);

        if (true === $validated) {
            $user->getTOTPAuth()?->setEnabled(true);
            $this->userWriter->save($user->getWrappedUser());
        }

        return $this;
    }
}
