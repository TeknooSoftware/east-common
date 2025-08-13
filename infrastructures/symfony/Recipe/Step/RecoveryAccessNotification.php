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

use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\User\NotifyUserAboutRecoveryAccessInterface;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess;
use Teknoo\East\CommonBundle\Recipe\Step\Exception\InvalidClassException;
use Teknoo\East\CommonBundle\Recipe\Step\Exception\MissingConfigurationException;
use Teknoo\East\CommonBundle\Recipe\Step\Exception\MissingPackageException;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function is_a;

/**
 * Symfony implementation of NotifyUserAboutRecoveryAccessInterface to create a LoginLink for the user and notify it
 * via Symfony Notify (default by email, configurable in the final symfony app).
 * The notification class, (by default `LoginLinkNotification`) can be overrided in the workplan witht
 * the key `$recoveryNotificationClass`.
 * The step require the translation key for the notification subjet and the Symfony User temporary Role to set with
 * this user (to prevent full access with an non authicated user).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RecoveryAccessNotification implements NotifyUserAboutRecoveryAccessInterface
{
    public function __construct(
        private readonly ?LoginLinkHandlerInterface $loginLinkHandler,
        private readonly ?NotifierInterface $notifier,
        private readonly ?TranslatorInterface $translator,
        private readonly string $recoveryNotificationSubject,
        private readonly string $recoveryAccessRole,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ClientInterface $client,
        User $user,
        RecoveryAccess $recoveryAccess,
        string $recoveryNotificationClass = LoginLinkNotification::class,
    ): NotifyUserAboutRecoveryAccessInterface {
        if (null === $this->loginLinkHandler) {
            throw new MissingConfigurationException(
                'No `' . LoginLinkNotification::class . '` available, maybe it is missing a '
                    . '`login_link` section in the firewall configuration',
            );
        }

        if (null === $this->notifier) {
            throw new MissingPackageException(
                'No `' . NotifierInterface::class . '`, install it with composer require symfony/notifier',
            );
        }

        if (!is_a($recoveryNotificationClass, LoginLinkNotification::class, true)) {
            throw new InvalidClassException(
                "Error, `{$recoveryNotificationClass}` is not a " . LoginLinkNotification::class . 'notification',
            );
        }

        $sfUser = new UserWithRecoveryAccess($user, $recoveryAccess, $this->recoveryAccessRole);

        $loginLinkDetails = $this->loginLinkHandler->createLoginLink(
            user: $sfUser,
        );

        $subject = $this->recoveryNotificationSubject;
        $notification = new $recoveryNotificationClass(
            loginLinkDetails: $loginLinkDetails,
            subject: $this->translator?->trans($subject) ?? $subject,
        );

        $recipient = new Recipient($user->getEmail());

        $this->notifier->send($notification, $recipient);

        return $this;
    }
}
