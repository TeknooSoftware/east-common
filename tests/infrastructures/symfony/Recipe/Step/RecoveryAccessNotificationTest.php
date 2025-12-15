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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RecoveryAccessNotification::class)]
class RecoveryAccessNotificationTest extends TestCase
{
    private (LoginLinkHandlerInterface&Stub)|(LoginLinkHandlerInterface&MockObject)|null $loginLinkHandler = null;

    private (NotifierInterface&Stub)|(NotifierInterface&MockObject)|null $notifier = null;

    private (TranslatorInterface&Stub)|(TranslatorInterface&MockObject)|null $translator = null;

    private function getLoginLinkHandler(bool $stub = false): (LoginLinkHandlerInterface&Stub)|(LoginLinkHandlerInterface&MockObject)
    {
        if (!$this->loginLinkHandler instanceof LoginLinkHandlerInterface) {
            if ($stub) {
                $this->loginLinkHandler = $this->createStub(LoginLinkHandlerInterface::class);
            } else {
                $this->loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
            }
        }

        return $this->loginLinkHandler;
    }

    private function getNotifier(bool $stub = false): (NotifierInterface&Stub)|(NotifierInterface&MockObject)
    {
        if (!$this->notifier instanceof NotifierInterface) {
            if ($stub) {
                $this->notifier = $this->createStub(NotifierInterface::class);
            } else {
                $this->notifier = $this->createMock(NotifierInterface::class);
            }
        }

        return $this->notifier;
    }

    private function getTranslator(bool $stub = false): (TranslatorInterface&Stub)|(TranslatorInterface&MockObject)
    {
        if (!$this->translator instanceof TranslatorInterface) {
            if ($stub) {
                $this->translator = $this->createStub(TranslatorInterface::class);
            } else {
                $this->translator = $this->createMock(TranslatorInterface::class);
            }
        }

        return $this->translator;
    }

    public function buildStep(): RecoveryAccessNotification
    {
        return new RecoveryAccessNotification(
            $this->getLoginLinkHandler(true),
            $this->getNotifier(true),
            $this->getTranslator(true),
            'teknoo.subject',
            'teknoo.template',
        );
    }

    public function testExceptionWhenMissingLoginNotification(): void
    {
        $step = new RecoveryAccessNotification(
            null,
            $this->getNotifier(true),
            $this->getTranslator(true),
            'teknoo.subject',
            'teknoo.template'
        );

        $this->expectException(MissingConfigurationException::class);

        $user = $this->createStub(User::class);
        $user
            ->method('getEmail')
            ->willReturn('foo@bar');

        $step(
            $this->createStub(ManagerInterface::class),
            $this->createStub(ClientInterface::class),
            $user,
            $this->createStub(RecoveryAccess::class),
        );
    }

    public function testExceptionWhenMissingNotifier(): void
    {
        $step = new RecoveryAccessNotification(
            $this->getLoginLinkHandler(true),
            null,
            $this->getTranslator(true),
            'teknoo.subject',
            'teknoo.template'
        );

        $this->expectException(MissingPackageException::class);

        $user = $this->createStub(User::class);
        $user
            ->method('getEmail')
            ->willReturn('foo@bar');

        $step(
            $this->createStub(ManagerInterface::class),
            $this->createStub(ClientInterface::class),
            $user,
            $this->createStub(RecoveryAccess::class),
        );
    }

    public function testInvoke(): void
    {
        $user = $this->createStub(User::class);
        $user
            ->method('getEmail')
            ->willReturn('foo@bar');

        $this->assertInstanceOf(
            RecoveryAccessNotification::class,
            $this->buildStep()(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ClientInterface::class),
                $user,
                $this->createStub(RecoveryAccess::class),
            )
        );
    }

    public function testInvokeWithInvalidNotification(): void
    {
        $user = $this->createStub(User::class);
        $user
            ->method('getEmail')
            ->willReturn('foo@bar');

        $this->expectException(InvalidClassException::class);
        $this->assertInstanceOf(
            RecoveryAccessNotification::class,
            $this->buildStep()(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ClientInterface::class),
                $user,
                $this->createStub(RecoveryAccess::class),
                \stdClass::class,
            )
        );
    }
}
