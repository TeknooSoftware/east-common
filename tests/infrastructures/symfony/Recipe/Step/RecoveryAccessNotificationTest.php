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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Recipe\Step\Exception\InvalidClassException;
use Teknoo\East\CommonBundle\Recipe\Step\Exception\MissingConfigurationException;
use Teknoo\East\CommonBundle\Recipe\Step\Exception\MissingPackageException;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\CommonBundle\Recipe\Step\RecoveryAccessNotification;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\CommonBundle\Recipe\Step\RecoveryAccessNotification
 */
class RecoveryAccessNotificationTest extends TestCase
{
    private ?LoginLinkHandlerInterface $loginLinkHandler = null;

    private ?NotifierInterface $notifier = null;

    private ?TranslatorInterface $translator = null;

    /**
     * @return LoginLinkHandlerInterface|MockObject
     */
    private function getLoginLinkHandler(): LoginLinkHandlerInterface
    {
        if (!$this->loginLinkHandler instanceof LoginLinkHandlerInterface) {
            $this->loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
        }

        return $this->loginLinkHandler;
    }

    /**
     * @return NotifierInterface|MockObject
     */
    private function getNotifier(): NotifierInterface
    {
        if (!$this->notifier instanceof NotifierInterface) {
            $this->notifier = $this->createMock(NotifierInterface::class);
        }

        return $this->notifier;
    }

    /**
     * @return TranslatorInterface|MockObject
     */
    private function getTranslator(): TranslatorInterface
    {
        if (!$this->translator instanceof TranslatorInterface) {
            $this->translator = $this->createMock(TranslatorInterface::class);
        }

        return $this->translator;
    }

    public function buildStep(): RecoveryAccessNotification
    {
        return new RecoveryAccessNotification(
            $this->getLoginLinkHandler(),
            $this->getNotifier(),
            $this->getTranslator(),
            'teknoo.subject',
            'teknoo.template'
        );
    }

    public function testExceptionWhenMissingLoginNotification()
    {
        $step = new RecoveryAccessNotification(
            null,
            $this->getNotifier(),
            $this->getTranslator(),
            'teknoo.subject',
            'teknoo.template'
        );

        $this->expectException(MissingConfigurationException::class);

        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getEmail')
            ->willReturn('foo@bar');

        $step(
            $this->createMock(ManagerInterface::class),
            $this->createMock(ClientInterface::class),
            $user,
            $this->createMock(RecoveryAccess::class),
        );
    }

    public function testExceptionWhenMissingNotifier()
    {
        $step = new RecoveryAccessNotification(
            $this->getLoginLinkHandler(),
            null,
            $this->getTranslator(),
            'teknoo.subject',
            'teknoo.template'
        );

        $this->expectException(MissingPackageException::class);

        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getEmail')
            ->willReturn('foo@bar');

        $step(
            $this->createMock(ManagerInterface::class),
            $this->createMock(ClientInterface::class),
            $user,
            $this->createMock(RecoveryAccess::class),
        );
    }

    public function testInvoke()
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getEmail')
            ->willReturn('foo@bar');

        self::assertInstanceOf(
            RecoveryAccessNotification::class,
            $this->buildStep()(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ClientInterface::class),
                $user,
                $this->createMock(RecoveryAccess::class),
            )
        );
    }

    public function testInvokeWithInvalidNotification()
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getEmail')
            ->willReturn('foo@bar');

        $this->expectException(InvalidClassException::class);
        self::assertInstanceOf(
            RecoveryAccessNotification::class,
            $this->buildStep()(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ClientInterface::class),
                $user,
                $this->createMock(RecoveryAccess::class),
                \stdClass::class,
            )
        );
    }
}
