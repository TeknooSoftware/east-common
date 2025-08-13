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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Security\Exception\WrongUserException;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;

/**
 * Step to disable TOTP/Google Authenticator for an user. The User must be an East Common bundle user, wrapping
 * an East Common user
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DisableTOTP
{
    use UserTrait;

    public function __construct(
        private readonly SymfonyUserWriter $userWriter,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(): self
    {
        $user = $this->getUser();
        if (!$user instanceof AbstractUser) {
            throw new WrongUserException('User instance is not a East Common bundle user instance');
        }

        $wrappedUsed = $user->getWrappedUser();
        $totpAuth = $wrappedUsed->getOneAuthData(TOTPAuth::class);
        if ($totpAuth instanceof TOTPAuth) {
            $totpAuth->setEnabled(false);
            $this->userWriter->save($wrappedUsed);
        }

        return $this;
    }
}
