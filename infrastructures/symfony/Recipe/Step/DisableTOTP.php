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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
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
